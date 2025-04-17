<?php
session_start(); // Nên đặt ở đầu file
include_once('../connection/connect_database.php');

// --- Kiểm tra ID đơn hàng ---
if (!isset($_GET['idDH']) || !filter_var($_GET['idDH'], FILTER_VALIDATE_INT)) {
    die("ID Đơn hàng không hợp lệ.");
}
$idDH = (int)$_GET['idDH'];

// --- Xử lý cập nhật trạng thái (Dùng Prepared Statements) ---
if (isset($_POST['capnhatdonhang'])) {
    if (isset($_POST['xuly']) && $_POST['xuly'] !== '' && isset($_POST['mahang_xuly']) && $_POST['mahang_xuly'] == $idDH) {
        $new_status = (int)$_POST['xuly']; // Lấy trạng thái mới
        $order_id_to_update = (int)$_POST['mahang_xuly'];

        // Lấy trạng thái hiện tại của đơn hàng (để tránh cập nhật kho sai)
        $current_status = -1; // Giá trị không hợp lệ ban đầu
        $sql_get_status = "SELECT DaXuLy FROM donhang WHERE idDH = ?";
        $stmt_get = mysqli_prepare($conn, $sql_get_status);
        if ($stmt_get) {
            mysqli_stmt_bind_param($stmt_get, 'i', $order_id_to_update);
            mysqli_stmt_execute($stmt_get);
            $res_get = mysqli_stmt_get_result($stmt_get);
            if ($row_get = mysqli_fetch_assoc($res_get)) {
                $current_status = (int)$row_get['DaXuLy'];
            }
            mysqli_free_result($res_get);
            mysqli_stmt_close($stmt_get);
        }

        if ($current_status != -1 && $current_status != $new_status) { // Chỉ cập nhật nếu trạng thái thay đổi
            mysqli_begin_transaction($conn); // Bắt đầu transaction
            try {
                // 1. Cập nhật bảng donhang
                $sql_update_donhang = "UPDATE donhang SET DaXuLy = ? WHERE idDH = ?";
                $stmt_update_dh = mysqli_prepare($conn, $sql_update_donhang);
                if (!$stmt_update_dh) throw new Exception("Prepare failed (donhang): " . mysqli_error($conn));
                mysqli_stmt_bind_param($stmt_update_dh, 'ii', $new_status, $order_id_to_update);
                if (!mysqli_stmt_execute($stmt_update_dh)) throw new Exception("Execute failed (donhang): " . mysqli_stmt_error($stmt_update_dh));
                mysqli_stmt_close($stmt_update_dh);

                // 2. Cập nhật bảng donhangchitiet (cập nhật trạng thái cho từng dòng chi tiết nếu cần)
                // Bảng gốc của bạn có cột DaXuLy trong donhangchitiet
                $sql_update_ctdh = "UPDATE donhangchitiet SET DaXuLy = ? WHERE idDH = ?";
                $stmt_update_ctdh = mysqli_prepare($conn, $sql_update_ctdh);
                 if (!$stmt_update_ctdh) throw new Exception("Prepare failed (donhangchitiet): " . mysqli_error($conn));
                mysqli_stmt_bind_param($stmt_update_ctdh, 'ii', $new_status, $order_id_to_update);
                 if (!mysqli_stmt_execute($stmt_update_ctdh)) throw new Exception("Execute failed (donhangchitiet): " . mysqli_stmt_error($stmt_update_ctdh));
                mysqli_stmt_close($stmt_update_ctdh);


                // 3. Cập nhật kho (Logic cần xem xét kỹ)
                // Chỉ nên cập nhật kho khi chuyển trạng thái cụ thể (vd: từ Chưa xử lý -> Đã xử lý HOẶC từ Đã giao -> Hủy)
                // Logic gốc: Cập nhật khi new_status=1 (trừ kho) hoặc new_status=4 (cộng lại kho)
                // -> Logic này có thể sai nếu cập nhật trạng thái nhiều lần.
                // -> Nên có logic phức tạp hơn dựa trên trạng thái CŨ và MỚI.
                // -> Tạm thời giữ logic gốc nhưng dùng prepared statement:

                if ($new_status == 1 && $current_status == 0) { // Chỉ trừ kho khi chuyển từ "Chưa xử lý" -> "Đã xử lý"
                    $sql_chitietdh = "SELECT idSP, SoLuong FROM donhangchitiet WHERE idDH = ?";
                    $stmt_get_items = mysqli_prepare($conn, $sql_chitietdh);
                    if($stmt_get_items){
                        mysqli_stmt_bind_param($stmt_get_items, 'i', $order_id_to_update);
                        mysqli_stmt_execute($stmt_get_items);
                        $result_items = mysqli_stmt_get_result($stmt_get_items);

                        $sql_update_stock = "UPDATE sanpham SET SoLuongTonKho = SoLuongTonKho - ? WHERE idSP = ? AND SoLuongTonKho >= ?";
                        $stmt_update_stock = mysqli_prepare($conn, $sql_update_stock);
                        if(!$stmt_update_stock) throw new Exception("Prepare failed (update stock): " . mysqli_error($conn));

                        while ($row = mysqli_fetch_assoc($result_items)) {
                            $soluong = (int)$row['SoLuong'];
                            $idSP = (int)$row['idSP'];
                            mysqli_stmt_bind_param($stmt_update_stock, 'iii', $soluong, $idSP, $soluong);
                            if(!mysqli_stmt_execute($stmt_update_stock)){
                                throw new Exception("Execute failed (update stock) for SP ID {$idSP}: " . mysqli_stmt_error($stmt_update_stock));
                            }
                            if (mysqli_stmt_affected_rows($stmt_update_stock) == 0) {
                                throw new Exception("Stock update failed or insufficient stock for SP ID {$idSP}");
                            }
                        }
                        mysqli_stmt_close($stmt_update_stock);
                        mysqli_free_result($result_items);
                    }
                    mysqli_stmt_close($stmt_get_items);

                } elseif ($new_status == 4 && ($current_status == 1 || $current_status == 2)) { // Chỉ cộng kho khi chuyển từ "Đã xử lý" hoặc "Đã giao" -> "Bị hủy"
                     $sql_chitietdh = "SELECT idSP, SoLuong FROM donhangchitiet WHERE idDH = ?";
                     $stmt_get_items_refund = mysqli_prepare($conn, $sql_chitietdh);
                     if($stmt_get_items_refund){
                        mysqli_stmt_bind_param($stmt_get_items_refund, 'i', $order_id_to_update);
                        mysqli_stmt_execute($stmt_get_items_refund);
                        $result_items_refund = mysqli_stmt_get_result($stmt_get_items_refund);

                        $sql_refund_stock = "UPDATE sanpham SET SoLuongTonKho = SoLuongTonKho + ? WHERE idSP = ?";
                        $stmt_refund_stock = mysqli_prepare($conn, $sql_refund_stock);
                         if(!$stmt_refund_stock) throw new Exception("Prepare failed (refund stock): " . mysqli_error($conn));

                        while ($row = mysqli_fetch_assoc($result_items_refund)) {
                            $soluong = (int)$row['SoLuong'];
                            $idSP = (int)$row['idSP'];
                            mysqli_stmt_bind_param($stmt_refund_stock, 'ii', $soluong, $idSP);
                            if(!mysqli_stmt_execute($stmt_refund_stock)){
                                throw new Exception("Execute failed (refund stock) for SP ID {$idSP}: " . mysqli_stmt_error($stmt_refund_stock));
                            }
                        }
                        mysqli_stmt_close($stmt_refund_stock);
                        mysqli_free_result($result_items_refund);
                     }
                      mysqli_stmt_close($stmt_get_items_refund);
                }

                mysqli_commit($conn); // Hoàn tất transaction
                echo "<script>alert('Cập nhật trạng thái đơn hàng thành công!'); window.location.href = window.location.href;</script>"; // Tải lại trang
                exit;

            } catch (Exception $e) {
                mysqli_rollback($conn); // Hoàn tác nếu có lỗi
                error_log("Order Update Error: " . $e->getMessage());
                echo "<script>alert('Lỗi cập nhật đơn hàng: " . addslashes($e->getMessage()) . "');</script>";
            }
        } else if ($current_status == $new_status) {
             echo "<script>alert('Trạng thái đơn hàng không thay đổi.');</script>";
        } else {
             echo "<script>alert('Không thể lấy trạng thái hiện tại của đơn hàng.');</script>";
        }

    } else {
         echo "<script>alert('Vui lòng chọn trạng thái hợp lệ.');</script>";
    }
}

// --- Lấy dữ liệu chi tiết đơn hàng (Dùng Prepared Statement) ---
$sql_ctdonhang = "SELECT ct.idDH, ct.idSP, ct.TenSP, ct.SoLuong, ct.DaXuLy, ct.Gia, ct.GiaKhuyenMai
                  FROM donhangchitiet ct
                  WHERE ct.idDH = ?";
$stmt_ct = mysqli_prepare($conn, $sql_ctdonhang);
$order_details = []; // Mảng lưu chi tiết
$current_status_display = 0; // Trạng thái chung để hiển thị
$tong_thanh_tien = 0; // Tổng tiền tính từ chi tiết

if($stmt_ct){
    mysqli_stmt_bind_param($stmt_ct, 'i', $idDH);
    mysqli_stmt_execute($stmt_ct);
    $rs_ctdonhang = mysqli_stmt_get_result($stmt_ct);
    if (!$rs_ctdonhang) {
        echo "Không thể truy vấn CSDL";
    } else {
        while ($r = mysqli_fetch_assoc($rs_ctdonhang)) {
            $order_details[] = $r;
            $current_status_display = $r['DaXuLy']; // Lấy trạng thái từ dòng chi tiết đầu tiên (giả sử đồng bộ)
            // Tính thành tiền cho từng dòng (dùng giá đã lưu trong CTHD)
            $thanh_tien_dong = (int)$r['SoLuong'] * (float)$r['Gia'];
            $tong_thanh_tien += $thanh_tien_dong;
        }
        mysqli_free_result($rs_ctdonhang);
    }
    mysqli_stmt_close($stmt_ct);
} else {
    echo "Lỗi chuẩn bị truy vấn chi tiết đơn hàng: " . mysqli_error($conn);
}

// Hàm lấy text trạng thái
function getStatusText($status_code) {
    switch ($status_code) {
        case 0: return "Chưa xử lý";
        case 1: return "Đã xử lý";
        case 2: return "Đã giao";
        case 3: return "Yêu cầu hủy";
        case 4: return "Đã hủy";
        default: return "Không xác định";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head> <?php /* Đổi thành head */ ?>
    <?php include_once("header1.php"); // Giả sử chứa meta, CSS cơ bản ?>
    <title>Chi tiết đơn hàng #<?php echo $idDH; ?></title>
    <?php include_once('header2.php'); // Giả sử chứa CSS/JS thêm ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* Thêm CSS tùy chỉnh nếu cần */
        .table th, .table td { vertical-align: middle; text-align: center; }
        .table thead th { background-color: #f2f2f2; } /* Màu nền header bảng */
        .total-amount { font-size: 1.2em; font-weight: bold; }
        .action-buttons a, .action-buttons input, .action-buttons select { margin-right: 10px; }
    </style>
</head> <?php /* Đổi thành /head */ ?>
<body>
<?php include_once('header3.php'); // Giả sử là menu admin ?>

<div class="container mt-4 mb-5">
    <h3 class="text-center mb-4">CHI TIẾT ĐƠN HÀNG #<?php echo $idDH; ?></h3>

    <?php if (!empty($order_details)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID SP</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Số Lượng</th>
                        <th>Đơn Giá Lưu</th>
                        <th>Thành Tiền</th>
                        <th>Trạng Thái Hiện Tại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_details as $r): ?>
                        <?php
                           // Thành tiền dùng giá đã lưu trong CTHD
                            $thanh_tien_dong = (int)$r['SoLuong'] * (float)$r['Gia'];
                        ?>
                        <tr>
                            <td><?php echo $r['idSP']; ?></td>
                            <td class="text-start"><?php echo htmlspecialchars($r['TenSP']); ?></td>
                            <td><?php echo $r['SoLuong']; ?></td>
                            <td><?php echo number_format((float)$r['Gia'], 0); ?> VNĐ</td>
                            <td><?php echo number_format($thanh_tien_dong, 0); ?> VNĐ</td>
                             <td><?php echo getStatusText($r['DaXuLy']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                 <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Tổng cộng tiền hàng:</td>
                        <td colspan="2" class="fw-bold"><?php echo number_format($tong_thanh_tien, 0); ?> VNĐ</td>
                    </tr>
                    <?php
                    // Lấy thêm thông tin đơn hàng tổng thể để hiển thị phí ship và tổng cuối
                    $order_total = 0;
                    $shipping_fee_display = 0;
                    $sql_order_info = "SELECT TongTien, idPTGH FROM donhang WHERE idDH = ?";
                    $stmt_order = mysqli_prepare($conn, $sql_order_info);
                     if($stmt_order){
                        mysqli_stmt_bind_param($stmt_order, 'i', $idDH);
                        mysqli_stmt_execute($stmt_order);
                        $res_order = mysqli_stmt_get_result($stmt_order);
                        if($row_order = mysqli_fetch_assoc($res_order)){
                             $order_total = (float)$row_order['TongTien'];
                             // Lấy phí ship từ $available_ptgh (cần query lại nếu chưa có)
                             // Giả sử phí ship được tính = TongTien (đơn hàng) - tong_thanh_tien (hàng hóa)
                             $shipping_fee_display = $order_total - $tong_thanh_tien;
                        }
                         mysqli_free_result($res_order);
                        mysqli_stmt_close($stmt_order);
                     }
                    ?>
                     <tr>
                        <td colspan="4" class="text-end fw-bold">Phí vận chuyển:</td>
                        <td colspan="2" class="fw-bold"><?php echo ($shipping_fee_display > 0) ? number_format($shipping_fee_display, 0) . ' VNĐ' : 'Miễn phí'; ?></td>
                    </tr>
                     <tr>
                        <td colspan="4" class="text-end fw-bold total-amount">Tổng Thanh Toán:</td>
                        <td colspan="2" class="fw-bold total-amount text-danger"><?php echo number_format($order_total, 0); ?> VNĐ</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <hr>

        <div class="mt-4 action-buttons">
             <form method="post" action="" name="updateStatusForm" class="d-flex align-items-center">
                 <input type="hidden" name="mahang_xuly" value="<?php echo $idDH; ?>">
                 <label for="xuly" class="form-label me-2">Cập nhật trạng thái:</label>
                 <select class="form-select me-2" name="xuly" id="xuly" style="width: 200px;">
                     <option value="">-- Chọn trạng thái --</option>
                     <option value="0" <?php if($current_status_display == 0) echo 'disabled'; ?>>Chưa xử lý</option>
                     <option value="1" <?php if($current_status_display == 1) echo 'disabled'; ?>>Đã xử lý</option>
                     <option value="2" <?php if($current_status_display == 2) echo 'disabled'; ?>>Đã giao</option>
                     <option value="3" <?php if($current_status_display == 3) echo 'disabled'; ?>>Yêu cầu hủy</option>
                     <option value="4" <?php if($current_status_display == 4) echo 'disabled'; ?>>Đã hủy</option>
                 </select>
                 <button type="submit" name="capnhatdonhang" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Xác nhận
                 </button>
                 <a href="in_don_hang.php?idDH=<?php echo $idDH; ?>" target="_blank" class="btn btn-success">
                     <i class="bi bi-printer-fill"></i> In Đơn Hàng
                 </a>
                 <a href="index_donhang.php" class="btn btn-secondary">
                     <i class="bi bi-arrow-left-circle"></i> Trở về DS Đơn hàng
                 </a>
            </form>
        </div>

    <?php else: ?>
        <div class="alert alert-warning text-center">Không tìm thấy chi tiết cho đơn hàng này hoặc đơn hàng không tồn tại.</div>
         <div class="text-center mt-3">
             <a href="index_donhang.php" class="btn btn-secondary">
                 <i class="bi bi-arrow-left-circle"></i> Trở về DS Đơn hàng
             </a>
         </div>
    <?php endif; ?>
</div>

<?php include_once('footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>