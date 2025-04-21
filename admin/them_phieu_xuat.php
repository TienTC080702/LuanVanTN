<?php
// Bắt đầu session nếu chưa có
if (!isset($_SESSION)) {
    session_start();
    // ob_start(); // Nếu cần chuyển hướng bằng header() sau khi có output
}

// Bao gồm file kết nối
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

// Kiểm tra đăng nhập nếu cần
// ... (Thêm code kiểm tra đăng nhập/quyền nếu bạn có) ...

$error_message = null; // Biến lưu thông báo lỗi
$old_input = [ // Mảng lưu giá trị form cũ
    'TenKH' => '',
    'idSP' => '',
    'SoLuong' => '1', // Mặc định là 1
    'NgayXuat' => date('Y-m-d')
];


// Phần xử lý PHP khi submit form
if (isset($_POST['submit'])) {
    // Lưu lại giá trị đã nhập
    $old_input = $_POST;

    $TenKH = isset($_POST['TenKH']) ? trim(mysqli_real_escape_string($conn, $_POST['TenKH'])) : '';
    $idSP = isset($_POST['idSP']) ? intval($_POST['idSP']) : 0;
    $SoLuong = isset($_POST['SoLuong']) ? intval($_POST['SoLuong']) : 0;
    $NgayXuat = isset($_POST['NgayXuat']) ? $_POST['NgayXuat'] : date('Y-m-d');

    // --- Kiểm tra dữ liệu đầu vào ---
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
        // *** NÊN DÙNG PREPARED STATEMENT CHO TRUY VẤN NÀY ***
        $query_sp = "SELECT GiaBan, SoLuongTonKho FROM sanpham WHERE idSP = $idSP";
        $result_sp = mysqli_query($conn, $query_sp);

        if (!$result_sp || mysqli_num_rows($result_sp) == 0) { // Kiểm tra cả lỗi query và không tìm thấy
            $error_message = "Lỗi: Không tìm thấy sản phẩm đã chọn!";
        } else {
            $row_sp = mysqli_fetch_assoc($result_sp);
            $GiaBan = floatval($row_sp['GiaBan']);
            $SoLuongTonKho = intval($row_sp['SoLuongTonKho']);
            mysqli_free_result($result_sp); // Giải phóng

            // --- Kiểm tra số lượng tồn kho ---
            if ($SoLuong > $SoLuongTonKho) {
                $error_message = "Lỗi: Số lượng tồn kho không đủ! (Chỉ còn {$SoLuongTonKho})";
            } else {
                // --- Dữ liệu hợp lệ, tiến hành xử lý ---
                $TongTien = $GiaBan * $SoLuong;

                mysqli_begin_transaction($conn);
                try {
                    // --- Thêm phiếu xuất ---
                    // *** NÊN DÙNG PREPARED STATEMENT CHO INSERT NÀY ***
                    $sql_insert = "INSERT INTO phieuxuat (TenKhachHang, idSP, SoLuong, NgayXuat, TongTien)
                                   VALUES ('$TenKH', $idSP, $SoLuong, '$NgayXuat', $TongTien)";
                    if (!mysqli_query($conn, $sql_insert)) {
                        throw new Exception("Lỗi khi thêm phiếu xuất: " . mysqli_error($conn));
                    }

                    // --- Cập nhật tồn kho ---
                    // *** NÊN DÙNG PREPARED STATEMENT CHO UPDATE NÀY ***
                    $sql_update = "UPDATE sanpham SET SoLuongTonKho = SoLuongTonKho - $SoLuong WHERE idSP = $idSP";
                    if (!mysqli_query($conn, $sql_update)) {
                        throw new Exception("Lỗi khi cập nhật số lượng sản phẩm: " . mysqli_error($conn));
                    }
                    // Không cần kiểm tra affected_rows ở đây nếu đã kiểm tra tồn kho trước đó

                    mysqli_commit($conn);
                    echo "<script>alert('Thêm phiếu xuất thành công!'); window.location.href='index_phieu_xuat.php';</script>";
                    exit();

                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error_message = "Lỗi Giao Dịch: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php // include_once("header1.php"); // Giả sử chứa CSS Bootstrap ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <title>Thêm Phiếu Xuất</title>
    <?php // include_once('header2.php'); // CSS tùy chỉnh ?>
    <style>
        /* CSS cũ của bạn */
        .form-container { max-width: 800px; margin: 30px auto; padding: 30px; background-color: #f9f9f9; border-radius: 10px; border: 1px solid #ddd; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        h3.form-title { color: #dc3545; text-align: center; margin-bottom: 1.5rem; font-weight: bold; }
        .button-group { text-align: center; margin-top: 25px; }
        .button-group .btn { margin: 0 10px; padding: 10px 20px; }
        .required-mark { color: red; font-weight: bold; margin-left: 2px; }

        /* CSS Select2 theme (nếu chưa có trong file CSS chung) */
        .select2-container--bootstrap-5 .select2-selection--single { height: calc(1.5em + .75rem + 2px) !important; padding: .375rem .75rem !important; border: 1px solid #ced4da !important; border-radius: .25rem !important; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 1.5 !important; padding-left: 0 !important; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow { height: calc(1.5em + .75rem) !important; top: 1px !important; }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection { border-color: #86b7fe !important; box-shadow: 0 0 0 0.25rem rgb(13 110 253 / 25%) !important; }
    </style>
</head>
<body>
<?php // include_once('header3.php'); // Menu ?>

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
                 <input type="text" class="form-control" id="TenKH" name="TenKH" required value="<?php echo htmlspecialchars($old_input['TenKH']); ?>" placeholder="Nhập tên người mua/nhận hàng">
             </div>

            <div class="mb-3">
                <label for="idSP" class="form-label"><strong>Chọn Sản Phẩm:<span class="required-mark">*</span></strong></label>
                <select class="form-select" id="idSP" name="idSP" required style="width: 100%;">
                    <option value="" disabled <?php echo empty($old_input['idSP']) ? 'selected' : ''; ?>>-- Nhập hoặc chọn sản phẩm --</option>
                    <?php
                    // Lấy danh sách sản phẩm CÓ HÀNG TỒN KHO > 0
                    $query_sanpham = "SELECT idSP, TenSP, SoLuongTonKho FROM sanpham WHERE SoLuongTonKho > 0 ORDER BY TenSP ASC";
                    $result_sanpham = mysqli_query($conn, $query_sanpham);
                    if ($result_sanpham) {
                        while ($row = mysqli_fetch_assoc($result_sanpham)) {
                            $selected = (isset($old_input['idSP']) && $old_input['idSP'] == $row['idSP']) ? 'selected' : '';
                            echo "<option value='{$row['idSP']}' $selected>" . htmlspecialchars($row['TenSP']) . " (Tồn: {$row['SoLuongTonKho']})</option>";
                        }
                         mysqli_free_result($result_sanpham);
                    }
                    ?>
                </select>
            </div>

             <div class="mb-3">
                 <label for="SoLuong" class="form-label"><strong>Số Lượng Xuất:<span class="required-mark">*</span></strong></label>
                 <input type="number" class="form-control" id="SoLuong" name="SoLuong" min="1" required value="<?php echo htmlspecialchars($old_input['SoLuong']); ?>" placeholder="Nhập số lượng bán/xuất kho">
             </div>

            <div class="mb-3">
                 <label for="NgayXuat" class="form-label"><strong>Ngày Xuất:<span class="required-mark">*</span></strong></label>
                 <input type="date" class="form-control" id="NgayXuat" name="NgayXuat" value="<?php echo htmlspecialchars($old_input['NgayXuat']); ?>" required max="<?php echo date('Y-m-d'); ?>">
            </div>

             <div class="button-group">
                 <button type="submit" name="submit" class="btn btn-success btn-lg">
                     <i class="fas fa-check-circle"></i> Tạo Phiếu Xuất
                 </button>
                 <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.href='index_phieu_xuat.php';"> <i class="fas fa-times"></i> Hủy
                 </button>
            </div>
        </form>

    </div> </div> <?php // include_once('footer.php'); // Footer chung ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script> <?php // Thêm Popper nếu Bootstrap JS cần ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Áp dụng Select2 cho dropdown Sản phẩm
    $('#idSP').select2({
        placeholder: "-- Nhập hoặc chọn sản phẩm --", // Placeholder text
        allowClear: true, // Cho phép xóa lựa chọn hiện tại
        theme: "bootstrap-5" // Sử dụng theme Bootstrap 5
    });

    // (Tùy chọn) Có thể thêm JS để kiểm tra số lượng nhập so với số tồn kho được chọn
    $('#idSP').on('change', function() {
        // Lấy số lượng tồn từ text của option được chọn
        var selectedOptionText = $(this).find('option:selected').text();
        var matches = selectedOptionText.match(/\(Tồn:\s*(\d+)\)/);
        var stock = matches ? parseInt(matches[1], 10) : Infinity; // Lấy số tồn

        // Cập nhật thuộc tính max cho ô số lượng
        if (stock !== Infinity && stock >= 0) { // Chỉ cập nhật nếu lấy được số tồn hợp lệ
             $('#SoLuong').attr('max', stock);
        } else {
             $('#SoLuong').removeAttr('max'); // Bỏ max nếu không lấy được tồn kho
        }
         // Reset giá trị nếu vượt quá max mới (tùy chọn)
         // if (parseInt($('#SoLuong').val()) > stock) { $('#SoLuong').val(stock > 0 ? 1 : 0); }
    });

     // Trigger change event để set max cho lần tải đầu tiên nếu có giá trị cũ
     if ($('#idSP').val()) {
        $('#idSP').trigger('change');
     }

});
</script>

</body>
</html>