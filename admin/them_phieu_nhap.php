<?php
// Bắt đầu session nếu chưa có (cần thiết để lấy ID người dùng)
if (!isset($_SESSION)) {
    session_start();
    // ob_start(); // Cân nhắc dùng ob_start nếu muốn dùng header() để chuyển hướng
}

// Bao gồm file kết nối trước tiên
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

// --- KIỂM TRA QUYỀN ADMIN HOẶC NGƯỜI DÙNG ĐƯỢC PHÉP ---
// Ví dụ: Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['IDUser'])) {
    // Nếu chưa đăng nhập, chuyển hướng về trang đăng nhập
    header('Location: ../site/DangNhap.php'); // Hoặc trang đăng nhập admin tương ứng
    exit();
}
$idNguoiLap = intval($_SESSION['IDUser']); // Lấy ID người dùng đang đăng nhập


// --- KHỞI TẠO BIẾN ---
$error_message = null;
$success_message = null; // Có thể dùng để hiển thị thông báo thành công ngay trên trang thay vì chỉ alert

// --- XỬ LÝ KHI FORM ĐƯỢC SUBMIT ---
if (isset($_POST['submit'])) {

    // --- Lấy dữ liệu từ form ---
    // Dùng isset và intval/floatval/mysqli_real_escape_string để an toàn hơn
    $idNCC = isset($_POST['idNCC']) ? intval($_POST['idNCC']) : 0;
    $idSP = isset($_POST['idSP']) ? intval($_POST['idSP']) : 0;
    // Lấy đơn giá nhập, loại bỏ dấu phẩy nếu người dùng nhập (ví dụ: 1,500,000)
    $DonGiaNhap = isset($_POST['DonGiaNhap']) ? floatval(str_replace(',', '', $_POST['DonGiaNhap'])) : 0;
    $SoLuong = isset($_POST['SoLuong']) ? intval($_POST['SoLuong']) : 0;
    $NgayNhap = isset($_POST['NgayNhap']) ? $_POST['NgayNhap'] : date('Y-m-d');
    // Lấy các trường tùy chọn
    $SoHoaDonNCC = isset($_POST['SoHoaDonNCC']) ? trim(mysqli_real_escape_string($conn, $_POST['SoHoaDonNCC'])) : null;
    $GhiChuPN = isset($_POST['GhiChuPN']) ? trim(mysqli_real_escape_string($conn, $_POST['GhiChuPN'])) : null;


    // --- Kiểm tra dữ liệu đầu vào ---
    if ($idNCC <= 0) {
        $error_message = "Vui lòng chọn nhà cung cấp.";
    } elseif ($idSP <= 0) {
         $error_message = "Vui lòng chọn sản phẩm.";
    } elseif ($DonGiaNhap <= 0) { // Đơn giá nhập phải lớn hơn 0
        $error_message = "Vui lòng nhập đơn giá nhập hợp lệ (lớn hơn 0).";
    } elseif ($SoLuong <= 0) {
        $error_message = "Vui lòng nhập số lượng nhập hợp lệ (lớn hơn 0).";
    } elseif (empty($NgayNhap) || !DateTime::createFromFormat('Y-m-d', $NgayNhap)) { // Kiểm tra ngày hợp lệ
        $error_message = "Vui lòng chọn ngày nhập hợp lệ.";
    } else {
        // --- Dữ liệu đầu vào hợp lệ ---

        // --- BỎ HOÀN TOÀN VIỆC LẤY GIÁ BÁN TỪ BẢNG SANPHAM ---
        // Giá nhập ($DonGiaNhap) đã được người dùng cung cấp từ form.

        // --- Tính tổng tiền ---
        $TongTien = $DonGiaNhap * $SoLuong;

        // --- Bắt đầu Transaction để đảm bảo tính toàn vẹn dữ liệu ---
        mysqli_begin_transaction($conn);

        try {
            // --- Chuẩn bị giá trị cho câu lệnh SQL (xử lý NULL) ---
            $idNguoiLap_sql = ($idNguoiLap === null) ? 'NULL' : $idNguoiLap; // Nên đảm bảo $idNguoiLap luôn có giá trị nếu bắt buộc đăng nhập
            $SoHoaDonNCC_sql = ($SoHoaDonNCC === null || $SoHoaDonNCC === '') ? 'NULL' : "'$SoHoaDonNCC'";
            $GhiChuPN_sql = ($GhiChuPN === null || $GhiChuPN === '') ? 'NULL' : "'$GhiChuPN'";
            // Ngày nhập đã được kiểm tra định dạng, nên có thể dùng trực tiếp (nhưng nên dùng prepared statement)
            $NgayNhap_sql = "'$NgayNhap'";

            // --- Tạo câu lệnh INSERT với các cột mới ---
            // Thay TenNhaCungCap bằng idNCC
            // Thêm DonGiaNhap
            // Thêm các cột tùy chọn idNguoiLap, SoHoaDonNCC, GhiChuPN
            $sql_insert = "INSERT INTO phieunhap
                           (idNCC, idSP, SoLuong, DonGiaNhap, NgayNhap, TongTien, idNguoiLap, SoHoaDonNCC, GhiChuPN)
                           VALUES
                           ($idNCC, $idSP, $SoLuong, $DonGiaNhap, $NgayNhap_sql, $TongTien, $idNguoiLap_sql, $SoHoaDonNCC_sql, $GhiChuPN_sql)";

            // Thực thi INSERT phiếu nhập
            if (!mysqli_query($conn, $sql_insert)) {
                // Ném Exception nếu có lỗi SQL
                throw new Exception("Lỗi khi thêm phiếu nhập: " . mysqli_error($conn));
            }

            // --- Cập nhật số lượng tồn kho sản phẩm ---
            // Nên kiểm tra xem sản phẩm có tồn tại không trước khi update
            $sql_update = "UPDATE sanpham SET SoLuongTonKho = SoLuongTonKho + $SoLuong WHERE idSP = $idSP";
            if (!mysqli_query($conn, $sql_update)) {
                 // Ném Exception nếu có lỗi SQL
                throw new Exception("Lỗi khi cập nhật số lượng tồn kho sản phẩm: " . mysqli_error($conn));
            }
             // Kiểm tra xem có dòng nào được cập nhật không (để chắc chắn idSP tồn tại)
            if (mysqli_affected_rows($conn) == 0) {
                 throw new Exception("Lỗi: Không tìm thấy sản phẩm ID $idSP để cập nhật tồn kho.");
            }


            // --- Nếu mọi thứ thành công, commit transaction ---
            mysqli_commit($conn);

            // --- Thông báo thành công và chuyển hướng ---
            // Cách 1: Dùng JS Alert (như code cũ)
             echo "<script>alert('Thêm phiếu nhập thành công!'); window.location.href='index_phieu_nhap.php';</script>"; // Chuyển về trang danh sách phiếu nhập
             exit(); // Dừng script

            // Cách 2: Dùng session và header() (linh hoạt hơn để hiển thị thông báo trên trang danh sách)
            // $_SESSION['success_message'] = "Thêm phiếu nhập thành công!";
            // if (ob_get_level() > 0) ob_end_clean(); // Xóa buffer nếu dùng header
            // header('Location: index_phieu_nhap.php'); // Chuyển về trang danh sách phiếu nhập
            // exit();

        } catch (Exception $e) {
            // --- Nếu có lỗi, rollback transaction ---
            mysqli_rollback($conn);
            // Lưu lỗi vào biến để hiển thị trên form
            $error_message = $e->getMessage();
        }
    } // Đóng else kiểm tra dữ liệu hợp lệ
} // Đóng if submit form
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Thêm Phiếu Nhập Mới</title> <?php include_once('header2.php'); ?>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Giữ lại CSS cho container form */
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        h3.form-title {
            color: #007bff;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }
        .button-group {
            text-align: center;
            margin-top: 25px; /* Tăng khoảng cách trên nút */
        }
         .button-group .btn {
             margin: 0 10px;
             padding: 10px 20px; /* Điều chỉnh kích thước nút */
         }
         /* Đánh dấu trường bắt buộc */
         .required-mark {
             color: red;
             font-weight: bold; /* Làm đậm dấu * */
             margin-left: 2px;
         }
         /* Căn lề phải cho label */
         .form-label {
            /* text-align: right; */ /* Bỏ nếu không muốn căn phải */
         }
         /* Có thể dùng grid của Bootstrap để căn label và input đẹp hơn */
         /*
         .row-form-group { margin-bottom: 1rem; }
         .row-form-group .form-label { text-align: right; padding-top: 7px; }
         */
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4"> <div class="form-container">
        <h3 class="form-title">THÊM PHIẾU NHẬP MỚI</h3>

        <?php
        // Hiển thị thông báo lỗi nếu có
        if (!empty($error_message)) {
            echo "<div class='alert alert-danger text-center' role='alert'><strong>Lỗi:</strong> " . htmlspecialchars($error_message) . "</div>";
        }
        ?>

        <form method="post" action="them_phieu_nhap.php" id="form-them-phieu-nhap">

            <div class="mb-3">
                <label for="idNCC" class="form-label"><strong>Chọn Nhà Cung Cấp:</strong><span class="required-mark">*</span></label>
                <select class="form-select" id="idNCC" name="idNCC" required>
                    <option value="" disabled <?php echo empty($_POST['idNCC']) ? 'selected' : ''; ?>>-- Chọn nhà cung cấp --</option>
                    <?php
                    // Lấy danh sách nhà cung cấp từ DB
                    $query_ncc = "SELECT idNCC, TenNCC FROM nhacungcap ORDER BY TenNCC ASC";
                    $result_ncc = mysqli_query($conn, $query_ncc);
                    if ($result_ncc) {
                        while ($ncc_row = mysqli_fetch_assoc($result_ncc)) {
                            // Giữ lại giá trị đã chọn nếu form submit lỗi
                            $selected = (isset($_POST['idNCC']) && $_POST['idNCC'] == $ncc_row['idNCC']) ? 'selected' : '';
                            echo "<option value='{$ncc_row['idNCC']}' $selected>" . htmlspecialchars($ncc_row['TenNCC']) . "</option>";
                        }
                        mysqli_free_result($result_ncc); // Giải phóng bộ nhớ
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="idSP" class="form-label"><strong>Chọn Sản Phẩm:</strong><span class="required-mark">*</span></label>
                <select class="form-select" id="idSP" name="idSP" required>
                    <option value="" disabled <?php echo empty($_POST['idSP']) ? 'selected' : ''; ?>>-- Chọn sản phẩm --</option>
                    <?php
                    // Lấy danh sách sản phẩm
                    $query_sanpham = "SELECT idSP, TenSP FROM sanpham ORDER BY TenSP ASC";
                    $result_sanpham = mysqli_query($conn, $query_sanpham);
                    if ($result_sanpham) {
                        while ($row = mysqli_fetch_assoc($result_sanpham)) {
                            $selected = (isset($_POST['idSP']) && $_POST['idSP'] == $row['idSP']) ? 'selected' : '';
                            echo "<option value='{$row['idSP']}' $selected>" . htmlspecialchars($row['TenSP']) . "</option>";
                        }
                         mysqli_free_result($result_sanpham); // Giải phóng bộ nhớ
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="DonGiaNhap" class="form-label"><strong>Đơn Giá Nhập (VNĐ):</strong><span class="required-mark">*</span></label>
                <input type="number" step="any" class="form-control" id="DonGiaNhap" name="DonGiaNhap" min="1" required value="<?php echo isset($_POST['DonGiaNhap']) ? htmlspecialchars($_POST['DonGiaNhap']) : ''; ?>" placeholder="Nhập giá mua vào thực tế (VD: 150000)">
                <div class="form-text">Nhập số tiền không có dấu phẩy hoặc chấm.</div>
            </div>

            <div class="mb-3">
                <label for="SoLuong" class="form-label"><strong>Số Lượng Nhập:</strong><span class="required-mark">*</span></label>
                <input type="number" class="form-control" id="SoLuong" name="SoLuong" min="1" required
                       value="<?php echo isset($_POST['SoLuong']) ? htmlspecialchars($_POST['SoLuong']) : '1'; ?>">
            </div>

            <div class="mb-3">
                <label for="NgayNhap" class="form-label"><strong>Ngày Nhập:</strong><span class="required-mark">*</span></label>
                <input type="date" class="form-control" id="NgayNhap" name="NgayNhap" value="<?php echo isset($_POST['NgayNhap']) ? htmlspecialchars($_POST['NgayNhap']) : date('Y-m-d'); ?>" required max="<?php echo date('Y-m-d'); // Không cho nhập ngày tương lai ?>">
            </div>

             <div class="mb-3">
                <label for="SoHoaDonNCC" class="form-label"><strong>Số Hóa Đơn NCC (Nếu có):</strong></label>
                <input type="text" class="form-control" id="SoHoaDonNCC" name="SoHoaDonNCC" maxlength="50"
                       value="<?php echo isset($_POST['SoHoaDonNCC']) ? htmlspecialchars($_POST['SoHoaDonNCC']) : ''; ?>">
            </div>

             <div class="mb-3">
                <label for="GhiChuPN" class="form-label"><strong>Ghi Chú:</strong></label>
                 <textarea class="form-control" id="GhiChuPN" name="GhiChuPN" rows="3"><?php echo isset($_POST['GhiChuPN']) ? htmlspecialchars($_POST['GhiChuPN']) : ''; ?></textarea>
            </div>

            <div class="button-group">
                <button type="submit" name="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Lưu Phiếu Nhập
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.href='index_phieu_nhap.php';"> <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>

    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>