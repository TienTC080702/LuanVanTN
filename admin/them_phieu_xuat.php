<?php
// Bắt đầu session nếu cần (ví dụ: để lấy id người lập phiếu sau này)
// if (!isset($_SESSION)) { session_start(); ob_start(); }

// Bao gồm file kết nối trước tiên
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

// Kiểm tra đăng nhập nếu cần
// $idNguoiLap = isset($_SESSION['IDUser']) ? intval($_SESSION['IDUser']) : null;
// if ($idNguoiLap === null) { /* Chuyển hướng đăng nhập */ }

$error_message = null; // Biến lưu thông báo lỗi

// Phần xử lý PHP khi submit form (LOGIC GIỮ NGUYÊN)
if (isset($_POST['submit'])) {
    $TenKH = isset($_POST['TenKH']) ? trim(mysqli_real_escape_string($conn, $_POST['TenKH'])) : '';
    $idSP = isset($_POST['idSP']) ? intval($_POST['idSP']) : 0;
    $SoLuong = isset($_POST['SoLuong']) ? intval($_POST['SoLuong']) : 0;
    $NgayXuat = isset($_POST['NgayXuat']) ? $_POST['NgayXuat'] : date('Y-m-d');

    // --- Kiểm tra dữ liệu đầu vào cơ bản ---
    if (empty($TenKH)) {
        $error_message = "Vui lòng nhập tên khách hàng.";
    } elseif ($idSP <= 0) {
        $error_message = "Vui lòng chọn sản phẩm.";
    } elseif ($SoLuong <= 0) {
        $error_message = "Vui lòng nhập số lượng xuất hợp lệ (lớn hơn 0).";
    } elseif (empty($NgayXuat) || !DateTime::createFromFormat('Y-m-d', $NgayXuat)) {
        $error_message = "Vui lòng chọn ngày xuất hợp lệ.";
    } else {
        // --- Lấy giá bán và kiểm tra tồn kho ---
        $query_sp = "SELECT GiaBan, SoLuongTonKho FROM sanpham WHERE idSP = $idSP";
        $result_sp = mysqli_query($conn, $query_sp);
        $row_sp = mysqli_fetch_assoc($result_sp);

        if (!$row_sp) {
            $error_message = "Lỗi: Không tìm thấy sản phẩm đã chọn!";
        } else {
            $GiaBan = floatval($row_sp['GiaBan']);
            $SoLuongTonKho = intval($row_sp['SoLuongTonKho']);

            // --- Kiểm tra số lượng tồn kho ---
            if ($SoLuong > $SoLuongTonKho) {
                $error_message = "Lỗi: Số lượng tồn kho không đủ! (Chỉ còn {$SoLuongTonKho})";
            } else {
                // --- Dữ liệu hợp lệ, tiến hành xử lý ---
                $TongTien = $GiaBan * $SoLuong;

                mysqli_begin_transaction($conn);

                try {
                    // --- Thêm phiếu xuất ---
                    // (Vẫn dùng TenKhachHang text, chưa dùng idKH)
                    // Nên thêm idNguoiLap nếu có
                    $sql_insert = "INSERT INTO phieuxuat (TenKhachHang, idSP, SoLuong, NgayXuat, TongTien)
                                   VALUES ('$TenKH', $idSP, $SoLuong, '$NgayXuat', $TongTien)";
                    if (!mysqli_query($conn, $sql_insert)) {
                        throw new Exception("Lỗi khi thêm phiếu xuất: " . mysqli_error($conn));
                    }

                    // --- Cập nhật (trừ) số lượng tồn kho sản phẩm ---
                    $sql_update = "UPDATE sanpham SET SoLuongTonKho = SoLuongTonKho - $SoLuong WHERE idSP = $idSP";
                    if (!mysqli_query($conn, $sql_update)) {
                        throw new Exception("Lỗi khi cập nhật số lượng sản phẩm: " . mysqli_error($conn));
                    }
                    // Có thể kiểm tra mysqli_affected_rows == 0 ở đây nếu cần

                    // --- Commit transaction ---
                    mysqli_commit($conn);

                    // --- Thông báo và chuyển hướng ---
                    echo "<script>alert('Thêm phiếu xuất thành công!'); window.location.href='index_phieu_xuat.php';</script>";
                    exit();

                } catch (Exception $e) {
                    // --- Rollback nếu có lỗi ---
                    mysqli_rollback($conn);
                    $error_message = "Lỗi Giao Dịch: " . $e->getMessage();
                }
            } // Đóng else kiểm tra tồn kho
        } // Đóng else kiểm tra tìm thấy sản phẩm
    } // Đóng else kiểm tra dữ liệu hợp lệ
} // Đóng if submit
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Thêm Phiếu Xuất</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS cho container form */
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        /* CSS cho tiêu đề */
        h3.form-title {
            color: #dc3545; /* Màu đỏ cho phiếu xuất */
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }
        /* Căn giữa nút */
        .button-group {
            text-align: center;
            margin-top: 25px;
        }
         .button-group .btn {
             margin: 0 10px;
             padding: 10px 20px;
         }
         /* Dấu * màu đỏ */
         .required-mark {
             color: red;
             font-weight: bold;
             margin-left: 2px;
         }
         /* Bỏ CSS nút cũ */
    </style>
</header>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">THÊM PHIẾU XUẤT MỚI</h3>

        <?php
        // Hiển thị lỗi nếu có
        if (!empty($error_message)) {
            echo "<div class='alert alert-danger text-center' role='alert'><strong>Lỗi:</strong> " . htmlspecialchars($error_message) . "</div>";
        }
        ?>

        <form method="post" action="them_phieu_xuat.php" id="form-them-phieu-xuat">

             <div class="mb-3">
                <label for="TenKH" class="form-label"><strong>Tên Khách Hàng:<span class="required-mark">*</span></strong></label>
                <input type="text" class="form-control" id="TenKH" name="TenKH" required
                       value="<?php echo isset($_POST['TenKH']) ? htmlspecialchars($_POST['TenKH']) : ''; ?>" placeholder="Nhập tên người mua/nhận hàng">
            </div>

            <div class="mb-3">
                <label for="idSP" class="form-label"><strong>Chọn Sản Phẩm:<span class="required-mark">*</span></strong></label>
                <select class="form-select" id="idSP" name="idSP" required>
                    <option value="" disabled <?php echo empty($_POST['idSP']) ? 'selected' : ''; ?>>-- Chọn sản phẩm --</option>
                    <?php
                    // Lấy danh sách sản phẩm (chỉ lấy SP có tồn kho > 0 ?)
                    $query_sanpham = "SELECT idSP, TenSP, SoLuongTonKho FROM sanpham WHERE SoLuongTonKho > 0 ORDER BY TenSP ASC";
                    $result_sanpham = mysqli_query($conn, $query_sanpham);
                    if ($result_sanpham) {
                        while ($row = mysqli_fetch_assoc($result_sanpham)) {
                            $selected = (isset($_POST['idSP']) && $_POST['idSP'] == $row['idSP']) ? 'selected' : '';
                            // Hiển thị cả số lượng tồn kho để người dùng biết
                            echo "<option value='{$row['idSP']}' $selected>" . htmlspecialchars($row['TenSP']) . " (Tồn: {$row['SoLuongTonKho']})</option>";
                        }
                         mysqli_free_result($result_sanpham);
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="SoLuong" class="form-label"><strong>Số Lượng Xuất:<span class="required-mark">*</span></strong></label>
                <input type="number" class="form-control" id="SoLuong" name="SoLuong" min="1" required
                       value="<?php echo isset($_POST['SoLuong']) ? htmlspecialchars($_POST['SoLuong']) : '1'; ?>" placeholder="Nhập số lượng bán/xuất kho">
                 </div>

            <div class="mb-3">
                <label for="NgayXuat" class="form-label"><strong>Ngày Xuất:<span class="required-mark">*</span></strong></label>
                <input type="date" class="form-control" id="NgayXuat" name="NgayXuat" value="<?php echo isset($_POST['NgayXuat']) ? htmlspecialchars($_POST['NgayXuat']) : date('Y-m-d'); ?>" required max="<?php echo date('Y-m-d'); // Thường ngày xuất không ở tương lai ?>">
            </div>

             <div class="button-group">
                <button type="submit" name="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check-circle"></i> Tạo Phiếu Xuất
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.href='index_phieu_xuat.php';"> <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>

    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>