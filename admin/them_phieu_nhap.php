<?php
// Bắt đầu session nếu chưa có (cần thiết để lấy ID người dùng)
if (!isset($_SESSION)) {
    session_start();
    // ob_start(); // Cân nhắc dùng ob_start nếu muốn dùng header() để chuyển hướng
}

// Bao gồm file kết nối trước tiên
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng
// include_once('../libs/lib.php'); // Include libs nếu cần

// --- KIỂM TRA QUYỀN ADMIN HOẶC NGƯỜI DÙNG ĐƯỢC PHÉP ---
if (!isset($_SESSION['IDUser'])) {
    header('Location: ../site/DangNhap.php'); // Hoặc trang đăng nhập admin tương ứng
    exit();
}
$idNguoiLap = intval($_SESSION['IDUser']);

// --- KHỞI TẠO BIẾN ---
$error_message = null;
$success_message = null;

// --- XỬ LÝ KHI FORM ĐƯỢC SUBMIT ---
if (isset($_POST['submit'])) {
    // --- Lấy dữ liệu từ form ---
    $idNCC = isset($_POST['idNCC']) ? intval($_POST['idNCC']) : 0;
    $idSP = isset($_POST['idSP']) ? intval($_POST['idSP']) : 0; // idSP sẽ được lấy từ select#idSP
    $DonGiaNhap = isset($_POST['DonGiaNhap']) ? floatval(str_replace(',', '', $_POST['DonGiaNhap'])) : 0;
    $SoLuong = isset($_POST['SoLuong']) ? intval($_POST['SoLuong']) : 0;
    $NgayNhap = isset($_POST['NgayNhap']) ? $_POST['NgayNhap'] : date('Y-m-d');
    $SoHoaDonNCC = isset($_POST['SoHoaDonNCC']) ? trim(mysqli_real_escape_string($conn, $_POST['SoHoaDonNCC'])) : null;
    $GhiChuPN = isset($_POST['GhiChuPN']) ? trim(mysqli_real_escape_string($conn, $_POST['GhiChuPN'])) : null;

    // --- Kiểm tra dữ liệu đầu vào ---
    if ($idNCC <= 0) {
        $error_message = "Vui lòng chọn nhà cung cấp.";
    } elseif ($idSP <= 0) {
        $error_message = "Vui lòng chọn sản phẩm."; // Lỗi này sẽ ít xảy ra nếu Select2 được dùng đúng cách
    } elseif ($DonGiaNhap <= 0) {
        $error_message = "Vui lòng nhập đơn giá nhập hợp lệ (lớn hơn 0).";
    } elseif ($SoLuong <= 0) {
        $error_message = "Vui lòng nhập số lượng nhập hợp lệ (lớn hơn 0).";
    } elseif (empty($NgayNhap) || !DateTime::createFromFormat('Y-m-d', $NgayNhap)) {
        $error_message = "Vui lòng chọn ngày nhập hợp lệ.";
    } else {
        // --- Dữ liệu đầu vào hợp lệ ---
        $TongTien = $DonGiaNhap * $SoLuong;

        mysqli_begin_transaction($conn);
        try {
            $idNguoiLap_sql = ($idNguoiLap === null) ? 'NULL' : $idNguoiLap;
            $SoHoaDonNCC_sql = ($SoHoaDonNCC === null || $SoHoaDonNCC === '') ? 'NULL' : "'$SoHoaDonNCC'";
            $GhiChuPN_sql = ($GhiChuPN === null || $GhiChuPN === '') ? 'NULL' : "'$GhiChuPN'";
            $NgayNhap_sql = "'$NgayNhap'";

            $sql_insert = "INSERT INTO phieunhap (idNCC, idSP, SoLuong, DonGiaNhap, NgayNhap, TongTien, idNguoiLap, SoHoaDonNCC, GhiChuPN)
                           VALUES ($idNCC, $idSP, $SoLuong, $DonGiaNhap, $NgayNhap_sql, $TongTien, $idNguoiLap_sql, $SoHoaDonNCC_sql, $GhiChuPN_sql)";

            if (!mysqli_query($conn, $sql_insert)) {
                throw new Exception("Lỗi khi thêm phiếu nhập: " . mysqli_error($conn));
            }

            $sql_update = "UPDATE sanpham SET SoLuongTonKho = SoLuongTonKho + $SoLuong WHERE idSP = $idSP";
            if (!mysqli_query($conn, $sql_update)) {
                throw new Exception("Lỗi khi cập nhật số lượng tồn kho sản phẩm: " . mysqli_error($conn));
            }
            if (mysqli_affected_rows($conn) == 0) {
                throw new Exception("Lỗi: Không tìm thấy sản phẩm ID $idSP để cập nhật tồn kho.");
            }

            mysqli_commit($conn);
            echo "<script>alert('Thêm phiếu nhập thành công!'); window.location.href='index_phieu_nhap.php';</script>";
            exit();

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Thêm Phiếu Nhập Mới</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* CSS cũ của bạn */
        .form-container { max-width: 800px; margin: 30px auto; padding: 30px; background-color: #f9f9f9; border-radius: 10px; border: 1px solid #ddd; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        h3.form-title { color: #007bff; text-align: center; margin-bottom: 1.5rem; font-weight: bold; }
        .button-group { text-align: center; margin-top: 25px; }
        .button-group .btn { margin: 0 10px; padding: 10px 20px; }
        .required-mark { color: red; font-weight: bold; margin-left: 2px; }
        .form-label { /* text-align: right; */ }

        /* CSS để Select2 hiển thị đúng kích thước */
        .select2-container--bootstrap-5 .select2-selection--single {
            height: calc(1.5em + .75rem + 2px) !important; /* Chiều cao giống input Bootstrap */
            padding: .375rem .75rem !important;
            border: 1px solid #ced4da !important;
            border-radius: .25rem !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 1.5 !important;
            padding-left: 0 !important; /* Reset padding */
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
             height: calc(1.5em + .75rem) !important; /* Điều chỉnh chiều cao mũi tên */
             top: 1px !important;
        }
         /* Fix focus outline (optional) */
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
             border-color: #86b7fe !important;
             box-shadow: 0 0 0 0.25rem rgb(13 110 253 / 25%) !important;
        }
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">THÊM PHIẾU NHẬP MỚI</h3>

        <?php
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
                    $query_ncc = "SELECT idNCC, TenNCC FROM nhacungcap ORDER BY TenNCC ASC";
                    $result_ncc = mysqli_query($conn, $query_ncc);
                    if ($result_ncc) {
                        while ($ncc_row = mysqli_fetch_assoc($result_ncc)) {
                            $selected = (isset($_POST['idNCC']) && $_POST['idNCC'] == $ncc_row['idNCC']) ? 'selected' : '';
                            echo "<option value='{$ncc_row['idNCC']}' $selected>" . htmlspecialchars($ncc_row['TenNCC']) . "</option>";
                        }
                        mysqli_free_result($result_ncc);
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="idSP" class="form-label"><strong>Chọn Sản Phẩm:</strong><span class="required-mark">*</span></label>
                <select class="form-select" id="idSP" name="idSP" required style="width: 100%;"> <?php // Thêm style width 100% cho select2 hoạt động tốt ?>
                    <option value="" disabled <?php echo empty($_POST['idSP']) ? 'selected' : ''; ?>>-- Chọn sản phẩm --</option>
                    <?php
                    $query_sanpham = "SELECT idSP, TenSP FROM sanpham ORDER BY TenSP ASC";
                    $result_sanpham = mysqli_query($conn, $query_sanpham);
                    if ($result_sanpham) {
                        while ($row = mysqli_fetch_assoc($result_sanpham)) {
                            $selected = (isset($_POST['idSP']) && $_POST['idSP'] == $row['idSP']) ? 'selected' : '';
                            echo "<option value='{$row['idSP']}' $selected>" . htmlspecialchars($row['TenSP']) . "</option>";
                        }
                        mysqli_free_result($result_sanpham);
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
                <input type="number" class="form-control" id="SoLuong" name="SoLuong" min="1" required value="<?php echo isset($_POST['SoLuong']) ? htmlspecialchars($_POST['SoLuong']) : '1'; ?>">
            </div>

            <div class="mb-3">
                <label for="NgayNhap" class="form-label"><strong>Ngày Nhập:</strong><span class="required-mark">*</span></label>
                <input type="date" class="form-control" id="NgayNhap" name="NgayNhap" value="<?php echo isset($_POST['NgayNhap']) ? htmlspecialchars($_POST['NgayNhap']) : date('Y-m-d'); ?>" required max="<?php echo date('Y-m-d'); ?>">
            </div>

             <div class="mb-3">
                <label for="SoHoaDonNCC" class="form-label"><strong>Số Hóa Đơn NCC (Nếu có):</strong></label>
                <input type="text" class="form-control" id="SoHoaDonNCC" name="SoHoaDonNCC" maxlength="50" value="<?php echo isset($_POST['SoHoaDonNCC']) ? htmlspecialchars($_POST['SoHoaDonNCC']) : ''; ?>">
            </div>

             <div class="mb-3">
                <label for="GhiChuPN" class="form-label"><strong>Ghi Chú:</strong></label>
                 <textarea class="form-control" id="GhiChuPN" name="GhiChuPN" rows="3"><?php echo isset($_POST['GhiChuPN']) ? htmlspecialchars($_POST['GhiChuPN']) : ''; ?></textarea>
            </div>

            <div class="button-group">
                <button type="submit" name="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Lưu Phiếu Nhập
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.href='index_phieu_nhap.php';">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>

    </div> </div> <?php include_once('footer.php'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Áp dụng Select2 cho dropdown sản phẩm
    $('#idSP').select2({
        placeholder: "-- Nhập hoặc chọn sản phẩm --", // Placeholder text
        allowClear: true, // Cho phép xóa lựa chọn hiện tại
        theme: "bootstrap-5" // Sử dụng theme Bootstrap 5 (nếu đã include CSS theme)
    });

    // Tùy chọn: Áp dụng Select2 cho dropdown nhà cung cấp nếu muốn
    /*
    $('#idNCC').select2({
        placeholder: "-- Chọn nhà cung cấp --",
        allowClear: true,
        theme: "bootstrap-5"
    });
    */
});
</script>

</body>
</html>