<?php
include_once('../connection/connect_database.php');
// Nên dùng Prepared Statement nếu có tham số đầu vào, ở đây là SELECT * nên chưa cần
$sl_donhang = "SELECT * FROM donhang ORDER BY idDH DESC";
$rs_donhang = mysqli_query($conn, $sl_donhang);
if (!$rs_donhang) {
    // Hiển thị lỗi một cách thân thiện hơn
    die("Lỗi: Không thể truy vấn cơ sở dữ liệu đơn hàng. Vui lòng thử lại sau.");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head> <?php // Đổi header thành head ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Danh sách đơn hàng</title>
    <?php include_once('header2.php'); ?>
    <style>
        /* CSS tùy chỉnh nếu cần, ví dụ giới hạn chiều rộng cột địa chỉ */
        .col-address {
            min-width: 200px; /* Hoặc max-width */
            white-space: normal; /* Cho phép xuống dòng */
        }
        .col-status {
             min-width: 160px;
        }
         .col-total {
            min-width: 120px;
            text-align: right; /* Căn phải cho cột tiền */
         }
         .col-date {
             min-width: 150px;
         }
         .table-sm td, .table-sm th {
             padding: 0.4rem; /* Điều chỉnh padding cho table-sm nếu cần */
             vertical-align: middle; /* Căn giữa theo chiều dọc */
         }
         .status-form select {
             margin: 0; /* Bỏ margin mặc định của form/select */
         }
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container-fluid mt-4"> <?php // Dùng container-fluid cho bảng rộng ?>
    <h3 class="text-center mb-4" style="color: #1F75FE;">DANH SÁCH ĐƠN HÀNG</h3>

    <div class="table-responsive"> <?php // Bọc table trong div này để responsive ?>
        <table class="table table-bordered table-hover table-striped table-sm"> <?php // Thêm class table-sm, table-striped ?>
            <thead class="table-light text-center"> <?php // Dùng table-light hoặc table-dark cho thead ?>
                <tr>
                    <th>MĐH</th>
                    <th>ID User</th>
                    <th>Người nhận</th>
                    <th class="col-address">Địa chỉ</th>
                    <th>SĐT</th>
                    <th>Email</th>
                    <th class="col-total">Tổng tiền</th>
                    <th class="col-date">Ngày đặt</th>
                    <th>PTTT</th>
                    <th>PTGH</th>
                    <th class="col-status">Tình trạng</th>
                </tr>
            </thead>
            <tbody> <?php // tbody phải ở ngoài vòng lặp ?>
                <?php
                // Kiểm tra xem có đơn hàng nào không
                if (mysqli_num_rows($rs_donhang) > 0) {
                    while ($r = $rs_donhang->fetch_assoc()) {
                        $idDH_current = $r['idDH']; // Lấy idDH để dùng trong các query con
                ?>
                        <tr>
                            <td class="text-center">
                                <a href="index_ds_chitietdh.php?idDH=<?php echo $idDH_current; ?>" title="Xem chi tiết đơn hàng <?php echo $idDH_current; ?>">
                                    <?php echo $idDH_current; ?>
                                </a>
                            </td>
                            <td class="text-center"><?php echo $r['idUser'] ?? 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($r['TenNguoiNhan']); ?></td>
                            <td class="col-address"><?php echo htmlspecialchars($r['DiaChi']); ?></td>
                            <td><?php echo htmlspecialchars($r['SDT']); ?></td>
                            <td><?php echo htmlspecialchars($r['Email']); ?></td>
                            <td class="col-total">
                                <?php
                                // Cảnh báo: Query này rất không hiệu quả khi chạy trong vòng lặp!
                                // Nên tính tổng tiền sẵn và lưu vào bảng donhang khi tạo đơn.
                                // Hoặc tính 1 lần duy nhất bên ngoài bằng JOIN.
                                $total_amount = 0;
                                $sql_total = "SELECT SUM(b.SoLuong * b.Gia) AS TongTien
                                              FROM donhangchitiet b
                                              WHERE b.idDH = ?";
                                $stmt_total = mysqli_prepare($conn, $sql_total);
                                if ($stmt_total) {
                                    mysqli_stmt_bind_param($stmt_total, "i", $idDH_current);
                                    mysqli_stmt_execute($stmt_total);
                                    $rs_tt = mysqli_stmt_get_result($stmt_total);
                                    $d = mysqli_fetch_assoc($rs_tt);
                                    $total_amount = $d['TongTien'] ?? 0;
                                    mysqli_stmt_close($stmt_total);
                                } else {
                                    echo "<small>Lỗi tính tổng</small>";
                                }
                                echo number_format($total_amount, 0, ',', '.') . ' VNĐ';
                                ?>
                            </td>
                            <td class="col-date text-center"><?php echo date("d/m/Y H:i", strtotime($r['ThoiDiemDatHang'])); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($r['PTThanhToan'] ?? 'N/A'); ?></td>
                            <td class="text-center">
                                <?php
                                // Cảnh báo: Query này cũng không hiệu quả khi chạy trong vòng lặp!
                                // Nên JOIN lấy TenGH ngay từ query đầu tiên.
                                $tenGH = 'N/A';
                                $sql_ptgh = "SELECT TenGH FROM phuongthucgiaohang WHERE idGH = ?";
                                $stmt_ptgh = mysqli_prepare($conn, $sql_ptgh);
                                if ($stmt_ptgh && $r['idPTGH']) { // Chỉ query nếu có idPTGH
                                    mysqli_stmt_bind_param($stmt_ptgh, "i", $r['idPTGH']);
                                    mysqli_stmt_execute($stmt_ptgh);
                                    $rs_ptgh = mysqli_stmt_get_result($stmt_ptgh);
                                    if ($d_gh = mysqli_fetch_assoc($rs_ptgh)) {
                                        $tenGH = $d_gh['TenGH'];
                                    }
                                    mysqli_stmt_close($stmt_ptgh);
                                }
                                echo htmlspecialchars($tenGH);
                                ?>
                            </td>
                            <td class="col-status">
                                <form action="update_status.php" method="POST" class="status-form">
                                    <input type="hidden" name="idDH" value="<?php echo $idDH_current; ?>">
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Cập nhật trạng thái đơn hàng">
                                        <?php
                                            // Mảng trạng thái để dễ quản lý
                                            $statuses = [
                                                0 => 'Chưa xử lý',
                                                1 => 'Đã xử lý',
                                                2 => 'Đang giao', // Sửa lại cho rõ nghĩa hơn
                                                3 => 'Yêu cầu hủy',
                                                4 => 'Đã hủy', // Sửa lại cho rõ nghĩa hơn
                                                5 => 'Đã giao thành công' // Thêm trạng thái mới nếu cần
                                            ];
                                            $current_status = $r['DaXuLy']; // Lấy trạng thái hiện tại

                                            foreach ($statuses as $value => $text) {
                                                $selected = ($current_status == $value) ? 'selected' : '';
                                                echo "<option value='{$value}' {$selected}>{$text}</option>";
                                            }
                                        ?>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php
                    } // End while loop
                } else { // Trường hợp không có đơn hàng nào
                    ?>
                    <tr>
                        <td colspan="11" class="text-center text-muted p-3">Không có đơn hàng nào.</td>
                    </tr>
                <?php
                } // End if num_rows > 0
                ?>
            </tbody>
        </table>
    </div>

</div> <?php // end container-fluid ?>

<?php include_once('footer.php'); ?>
</body>
</html>