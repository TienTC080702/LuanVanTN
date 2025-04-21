<?php
// Bắt đầu session nếu chưa có (cần thiết để lấy ID người dùng hoặc thông báo lỗi/thành công)
if (!isset($_SESSION)) {
    session_start();
    // ob_start(); // Bỏ comment nếu bạn định dùng header() để chuyển hướng ở phần xử lý PHP này
}

// Bao gồm file kết nối trước tiên
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng
// include_once('../libs/lib.php'); // Include libs nếu cần

// --- KIỂM TRA QUYỀN ADMIN HOẶC NGƯỜI DÙNG ĐƯỢC PHÉP ---
// Ví dụ: Kiểm tra xem người dùng đã đăng nhập chưa và có quyền admin không
/* // Bỏ comment và chỉnh sửa nếu cần
if (!isset($_SESSION['IDAdmin'])) { // Hoặc kiểm tra quyền cụ thể
    $_SESSION['message'] = "Bạn không có quyền truy cập trang này.";
    $_SESSION['message_type'] = "warning";
    header('Location: ../admin/login.php'); // Hoặc trang đăng nhập admin tương ứng
    exit();
}
*/
// Lấy ID người thực hiện (ví dụ)
$idNguoiLap = isset($_SESSION['IDAdmin']) ? intval($_SESSION['IDAdmin']) : (isset($_SESSION['IDUser']) ? intval($_SESSION['IDUser']) : null);


// --- KHỞI TẠO BIẾN ---
$error_message = null; // Lưu thông báo lỗi từ server-side validation hoặc DB
// Biến lưu giá trị form cũ để điền lại nếu lỗi
$old_input = [
    'idSP' => '', 'TieuDe' => '', 'MoTa' => '', 'NoiDung' => '', 'AnHien' => '1' // Mặc định là Hiện
];

// --- XỬ LÝ KHI FORM ĐƯỢC SUBMIT ---
if (isset($_POST["Ok"])) { // Kiểm tra nút submit có tên "Ok"

    // Lưu lại dữ liệu đã nhập để điền lại form nếu có lỗi
    $old_input = $_POST;
    // Đảm bảo checkbox là mảng (nếu có checkbox trong form này)
    // $old_input['ten_checkbox'] = isset($_POST['ten_checkbox']) && is_array($_POST['ten_checkbox']) ? $_POST['ten_checkbox'] : [];


    // --- Xử lý file upload ---
    $upload_ok = false; // Cờ báo upload file thành công (chỉ file, chưa tính DB)
    $hinh_anh_name = null; // Tên file để lưu vào DB, mặc định là NULL
    $destination = null; // Đường dẫn đầy đủ file ảnh
    $tmp_name = null; // Tên file tạm

    // Chỉ xử lý upload nếu người dùng có chọn file (error = 0) và có size > 0
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK && $_FILES['file']['size'] > 0) {
        $path = '../images/baiviet/'; // *** Thư mục lưu ảnh bài viết (Đảm bảo tồn tại và có quyền ghi) ***
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true) && !is_dir($path)) {
                $error_message = 'Lỗi: Không thể tạo thư mục lưu ảnh: ' . htmlspecialchars($path);
            } else { @chmod($path, 0777); } // Thử set quyền
        }

        // Chỉ tiếp tục nếu không có lỗi tạo thư mục VÀ thư mục tồn tại, có quyền ghi
        if (empty($error_message) && is_dir($path) && is_writable($path)) {
            $tmp_name = $_FILES['file']['tmp_name'];
            $original_name = $_FILES['file']['name'];
            $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // Cho phép các định dạng ảnh phổ biến
            $max_size = 5 * 1024 * 1024; // 5MB

            // Kiểm tra kiểu MIME thực sự của file để tăng bảo mật
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!in_array($file_extension, $allowed_extensions) || !in_array($file_type, $allowed_mime_types)) {
                 $error_message = "Lỗi: Chỉ cho phép tải lên file ảnh (jpg, jpeg, png, gif, webp).";
            } elseif ($_FILES['file']['size'] > $max_size) {
                 $error_message = "Lỗi: Kích thước file ảnh quá lớn (tối đa 5MB).";
            } else {
                 // Tạo tên file mới duy nhất
                 $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
                 $hinh_anh_name = 'bv_' . $safe_name . '_' . time() . '.' . $file_extension; // Tên file cuối cùng
                 $destination = $path . $hinh_anh_name; // Lưu lại đường dẫn đầy đủ

                 // Di chuyển file ngay bây giờ để kiểm tra thành công upload
                 if (move_uploaded_file($tmp_name, $destination)) {
                    $upload_ok = true; // Đánh dấu upload file thành công
                 } else {
                    $move_error = error_get_last();
                    $error_detail = $move_error ? htmlspecialchars($move_error['message']) : 'Không rõ nguyên nhân.';
                    $error_message = "Lỗi: Không thể di chuyển file ảnh đã tải lên.\\nChi tiết: " . $error_detail;
                    $hinh_anh_name = null; // Reset tên ảnh nếu không di chuyển được
                 }
            }
        } else if(empty($error_message)) { // Nếu chưa có lỗi nhưng thư mục không ổn
             $error_message = 'Lỗi: Thư mục lưu ảnh không tồn tại hoặc không có quyền ghi.';
        }
    } elseif (isset($_FILES['file']) && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['file']['error'] != UPLOAD_ERR_OK) {
        // Có lỗi upload khác (ví dụ: quá size server cho phép...)
         $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => 'File vượt quá giới hạn upload_max_filesize.',
            UPLOAD_ERR_FORM_SIZE  => 'File vượt quá giới hạn MAX_FILE_SIZE.',
            UPLOAD_ERR_PARTIAL    => 'File chỉ được upload một phần.',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm.',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file vào đĩa.',
            UPLOAD_ERR_EXTENSION  => 'Một extension PHP đã chặn việc upload file.',
         ];
         $error_code = $_FILES['file']['error'];
         $error_message = 'Lỗi upload hình ảnh: ' . ($upload_errors[$error_code] ?? 'Lỗi không xác định.');
    }
    // Nếu không chọn file (UPLOAD_ERR_NO_FILE), $hinh_anh_name sẽ vẫn là null, và không có lỗi.
    // Ảnh đại diện cho bài viết là không bắt buộc theo logic hiện tại.

    // --- Chỉ tiếp tục INSERT vào DB nếu không có lỗi nào từ upload hoặc validate ---
    if (!isset($error_message) && empty($errors)) { // $errors có thể dùng cho validate khác nếu có

        // Lấy và làm sạch các dữ liệu text/select
        $idSP_clean = isset($_POST['idSP']) ? intval($_POST['idSP']) : 0;
        $tieuDe_clean = isset($_POST['TieuDe']) ? trim(mysqli_real_escape_string($conn, $_POST['TieuDe'])) : '';
        $moTa_clean = isset($_POST['MoTa']) ? trim(mysqli_real_escape_string($conn, $_POST['MoTa'])) : ''; // CKEditor thường tự escape, nhưng escape lại cho chắc
        $noiDung_clean = isset($_POST['NoiDung']) ? trim(mysqli_real_escape_string($conn, $_POST['NoiDung'])) : '';
        $ngayCapNhat_clean = date("Y-m-d H:i:s");
        $anHien_clean = isset($_POST['AnHien']) ? intval($_POST['AnHien']) : 1;

        // --- Validate dữ liệu text/select phía Server ---
        if ($idSP_clean <= 0) { $error_message = "Vui lòng chọn sản phẩm liên quan."; }
        elseif (empty($tieuDe_clean)) { $error_message = "Tiêu đề không được để trống."; }
        elseif (empty($moTa_clean)) { $error_message = "Mô tả ngắn không được để trống."; }
        elseif (empty($noiDung_clean)) { $error_message = "Nội dung chi tiết không được để trống."; }
        else {
             // --- Dữ liệu hợp lệ, chuẩn bị INSERT ---
             // Tên ảnh đã được xác định ở bước upload ($hinh_anh_name), có thể là null nếu không upload
             // *** Nên dùng Prepared Statement ***
             $hinh_anh_sql = ($hinh_anh_name !== null) ? "'".mysqli_real_escape_string($conn, $hinh_anh_name)."'" : 'NULL';

             $q_baiviet = "INSERT INTO baiviet (idSP, TieuDe, NoiDung, NgayCapNhat, AnHien, MoTa, img)
                           VALUES ($idSP_clean, '$tieuDe_clean', '$noiDung_clean', '$ngayCapNhat_clean', $anHien_clean, '$moTa_clean', $hinh_anh_sql)";

             $rs = mysqli_query($conn, $q_baiviet);

             if ($rs) {
                 // Insert thành công
                 echo "<script language='javascript'>alert('Thêm bài viết thành công!'); location.href='baiviet.php';</script>";
                 exit();
             } else {
                 // Lỗi INSERT DB
                 $error_message = "Lỗi khi thêm bài viết vào CSDL: " . mysqli_error($conn);
                 // Nếu đã upload ảnh thành công trước đó, nên xóa đi vì DB lỗi
                 if ($upload_ok && $destination !== null && file_exists($destination)) {
                     @unlink($destination); // Cố gắng xóa file ảnh đã upload
                 }
             }
        } // end else validate text/select
    } // Kết thúc if (!isset($error_message))

    // Nếu có lỗi ở bất kỳ bước nào, hiển thị bằng JS alert
    if (!empty($error_message)) {
         echo "<script language='javascript'>alert('Lỗi:\\n" . addslashes(str_replace("<br>", "\\n", $error_message)) . "');</script>";
         // Giữ lại giá trị cũ trong $old_input để điền lại form (đã làm ở đầu)
    }

} // Kết thúc if (isset($_POST["Ok"]))
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php // include_once("header1.php"); // Link CSS chung ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <title>Thêm Bài Viết Mới</title>
    <script type="text/javascript" src="../ckeditor/ckeditor.js"></script> <?php // Đảm bảo đường dẫn đúng ?>
    <style>
        /* Giữ nguyên CSS của bạn */
        body { background-color: #f8f9fa; }
        h3.form-title { color: #0d6efd; text-align: center; margin-top: 20px; margin-bottom: 30px; font-weight: bold; }
        .custom-form-container { max-width: 900px; margin: 20px auto 40px auto; background-color: #ffffff; padding: 35px; border-radius: 15px; box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); border: 1px solid #dee2e6; }
        .form-label strong { font-weight: 600; }
        .row .col-form-label { display: flex; align-items: center; padding-top: calc(0.375rem + 1px); padding-bottom: calc(0.375rem + 1px); }
        .button-group { text-align: center; margin-top: 35px; padding-top: 25px; border-top: 1px solid #dee2e6; }
        .button-group .btn { margin-left: 10px; margin-right: 10px; min-width: 130px; padding: 10px 20px; font-size: 1rem; }
        .checkbox-group-label { margin-bottom: 0.75rem; display: block; font-weight: 600; }
        .form-check-inline { margin-right: 1.5rem; margin-bottom: 0.75rem; }
        .form-check-input { margin-top: 0.2rem; }
        .text-danger { color: #dc3545 !important; font-size: 1.1em; vertical-align: middle; }
        .form-control:focus, .form-select:focus { border-color: #86b7fe; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); }
        #cke_MoTa { margin-top: 5px; }
        small.form-text { font-size: 0.85em; }
        .required-field::after { content: " *"; color: #dc3545; font-weight: bold; }
        /* CSS Select2 theme */
        .select2-container--bootstrap-5 .select2-selection--single { height: calc(1.5em + .75rem + 2px) !important; padding: .375rem .75rem !important; border: 1px solid #ced4da !important; border-radius: .25rem !important; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 1.5 !important; padding-left: 0 !important; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow { height: calc(1.5em + .75rem) !important; top: 1px !important; }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection { border-color: #86b7fe !important; box-shadow: 0 0 0 0.25rem rgb(13 110 253 / 25%) !important; }
    </style>
</head>
<body>
<?php // include_once('header3.php'); // Thanh menu ?>
<div class="container mt-4 mb-5">
    <h3 class="form-title">THÊM BÀI VIẾT MỚI</h3>

    <div class="custom-form-container">
        <?php
        // Hiển thị lỗi PHP nếu không dùng alert JS (ít dùng khi đã có alert)
        // if (!empty($error_message) && !isset($_POST["Ok"])) { // Chỉ hiển thị nếu ko phải submit lỗi
        //      echo "<div class='alert alert-danger text-center mb-4' role='alert'><strong>Lỗi:</strong><br>" . nl2br(htmlspecialchars($error_message)) . "</div>";
        // }
        ?>
        <form method="post" action="them_baiviet.php" name="ThemBaiViet" enctype="multipart/form-data" id="articleForm" novalidate>

            <div class="mb-3">
                <label for="idSP" class="form-label"><strong>Sản phẩm liên quan:<span class="required-mark">*</span></strong></label>
                <select class="form-select" name="idSP" id="idSP" required style="width: 100%;">
                    <option value="" disabled <?php echo empty($old_input['idSP']) ? 'selected' : ''; ?>>-- Chọn sản phẩm --</option>
                    <?php
                    if (isset($conn)) {
                        $sl_sanpham = "SELECT idSP, TenSP FROM sanpham ORDER BY TenSP ASC";
                        $rs_sanpham = mysqli_query($conn, $sl_sanpham);
                        if (!$rs_sanpham) { echo "<option value='' disabled>Lỗi tải sản phẩm</option>"; }
                        else {
                            while ($r = $rs_sanpham->fetch_assoc()) {
                                $selected = ($old_input['idSP'] == $r['idSP']) ? 'selected' : '';
                                echo "<option value='{$r['idSP']}' {$selected}>" . htmlspecialchars($r['TenSP']) . "</option>";
                            }
                            mysqli_free_result($rs_sanpham);
                        }
                    } else { echo "<option value='' disabled>Lỗi kết nối CSDL</option>"; }
                    ?>
                </select>
                 <div class="invalid-feedback">Vui lòng chọn sản phẩm liên quan.</div>
            </div>

            <div class="mb-3">
                <label for="TieuDe" class="form-label"><strong>Tiêu đề bài viết:<span class="required-mark">*</span></strong></label>
                <input type="text" class="form-control" id="TieuDe" name="TieuDe" required value="<?php echo htmlspecialchars($old_input['TieuDe']); ?>">
                 <div class="invalid-feedback">Vui lòng nhập tiêu đề.</div>
            </div>

            <div class="mb-3">
                <label for="file" class="form-label"><strong>Hình đại diện:</strong></label>
                <input class="form-control" type="file" id="file" name="file" accept="image/jpeg, image/png, image/gif, image/webp">
                <div class="form-text">Chọn ảnh đại diện cho bài viết (nếu có). Định dạng: JPG, PNG, GIF, WEBP. Tối đa 5MB.</div>
                 <div class="invalid-feedback">Vui lòng chọn file ảnh hợp lệ.</div>
            </div>

            <div class="mb-3">
                <label for="MoTa" class="form-label"><strong>Mô tả ngắn:<span class="required-mark">*</span></strong></label>
                <textarea class="form-control" name="MoTa" id="MoTa" rows="5" required><?php echo htmlspecialchars($old_input['MoTa']); ?></textarea>
                <script type="text/javascript"> CKEDITOR.replace( 'MoTa', {height: 150} ); </script> <?php // Có thể thêm chiều cao ?>
                 <div class="invalid-feedback" id="mota-feedback">Vui lòng nhập mô tả ngắn.</div>
            </div>

            <div class="mb-3">
                 <label for="NoiDung" class="form-label"><strong>Nội dung chi tiết:<span class="required-mark">*</span></strong></label>
                 <textarea class="form-control" name="NoiDung" id="NoiDung" rows="10" required><?php echo htmlspecialchars($old_input['NoiDung']); ?></textarea>
                 <script type="text/javascript"> CKEDITOR.replace( 'NoiDung', {height: 300} ); </script> <?php // Có thể thêm chiều cao ?>
                  <div class="invalid-feedback" id="noidung-feedback">Vui lòng nhập nội dung chi tiết.</div>
            </div>

            <div class="mb-3">
                 <label for="NgayCapNhatView" class="form-label"><strong>Ngày tạo/cập nhật:</strong></label>
                 <input type="text" class="form-control" id="NgayCapNhatView" name="NgayCapNhatView" readonly value="<?php echo date("d/m/Y H:i:s"); ?>">
            </div>

            <div class="mb-3">
                 <label for="AnHien" class="form-label"><strong>Trạng thái:</strong></label>
                 <select class="form-select" name="AnHien" id="AnHien">
                     <option value="1" <?php echo ($old_input['AnHien'] == 1) ? 'selected' : ''; ?>>Hiện</option>
                     <option value="0" <?php echo ($old_input['AnHien'] == 0) ? 'selected' : ''; ?>>Ẩn</option>
                 </select>
            </div>

             <div class="button-group">
                 <button type="submit" name="Ok" class="btn btn-success btn-lg"> <?php // Đổi màu nút ?>
                     <i class="fas fa-save"></i> Lưu Bài Viết
                 </button>
                 <button type="button" name="Huy" value="Hủy" class="btn btn-secondary btn-lg" onclick="getConfirmation();">
                     <i class="fas fa-times"></i> Hủy
                 </button>
            </div>

        </form>
    </div> </div> <?php // include_once('footer.php'); // Footer chung ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
    function getConfirmation(){
        var retVal = confirm("Bạn có chắc chắn muốn hủy và quay lại danh sách?");
        if( retVal == true ){
            // Chuyển hướng về trang danh sách bài viết (sửa lại nếu tên khác)
            window.location.href = 'baiviet.php';
        }
    }

     // --- Khởi tạo Select2 ---
     $(document).ready(function() {
        // Áp dụng Select2 cho dropdown Sản phẩm liên quan
        $('#idSP').select2({
            placeholder: "-- Nhập hoặc chọn sản phẩm --", // Placeholder text
            allowClear: true, // Cho phép xóa lựa chọn hiện tại
            theme: "bootstrap-5" // Sử dụng theme Bootstrap 5
        });

         // --- Xử lý validation cho CKEditor khi submit ---
         var form = document.getElementById('articleForm'); // Đổi ID form nếu cần
         if(form) {
             form.addEventListener('submit', function(event) {
                let ckEditorValid = true;
                let firstInvalidCk = null;

                 // Kiểm tra CKEditor cho Mô tả ngắn
                 var moTaEditor = typeof CKEDITOR !== 'undefined' ? CKEDITOR.instances.MoTa : null;
                 var moTaTextarea = document.getElementById('MoTa');
                 var moTaFeedback = moTaTextarea ? moTaTextarea.parentElement.querySelector('.invalid-feedback') : null;

                 if (moTaEditor) {
                     moTaEditor.updateElement();
                     if (moTaTextarea && moTaTextarea.value.trim() === '') {
                         ckEditorValid = false;
                         if(moTaFeedback) moTaFeedback.style.display = 'block';
                          if (!firstInvalidCk) firstInvalidCk = moTaEditor;
                     } else {
                         if(moTaFeedback) moTaFeedback.style.display = 'none';
                     }
                 } else if (moTaTextarea && moTaTextarea.hasAttribute('required') && moTaTextarea.value.trim() === '') {
                    ckEditorValid = false;
                    if (!firstInvalidCk) firstInvalidCk = moTaTextarea;
                 }

                  // Kiểm tra CKEditor cho Nội dung chi tiết
                 var noiDungEditor = typeof CKEDITOR !== 'undefined' ? CKEDITOR.instances.NoiDung : null;
                 var noiDungTextarea = document.getElementById('NoiDung');
                 var noiDungFeedback = noiDungTextarea ? noiDungTextarea.parentElement.querySelector('.invalid-feedback') : null;

                 if (noiDungEditor) {
                     noiDungEditor.updateElement();
                     if (noiDungTextarea && noiDungTextarea.value.trim() === '') {
                         ckEditorValid = false;
                         if(noiDungFeedback) noiDungFeedback.style.display = 'block';
                          if (!firstInvalidCk) firstInvalidCk = noiDungEditor;
                     } else {
                         if(noiDungFeedback) noiDungFeedback.style.display = 'none';
                     }
                 } else if (noiDungTextarea && noiDungTextarea.hasAttribute('required') && noiDungTextarea.value.trim() === '') {
                    ckEditorValid = false;
                    if (!firstInvalidCk) firstInvalidCk = noiDungTextarea;
                 }


                // Nếu CKEditor không hợp lệ, ngăn submit và focus
                if (!ckEditorValid) {
                     if (!event.defaultPrevented) event.preventDefault();
                     if (!event.cancelBubble) event.stopPropagation();
                      if (firstInvalidCk && typeof firstInvalidCk.focus === 'function') { firstInvalidCk.focus(); }
                      else if (firstInvalidCk) { firstInvalidCk.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                }

             }, true); // Bắt sự kiện submit ở capturing phase
         } // end if form exists

     }); // --- Kết thúc $(document).ready() ---


     // --- Bootstrap 5 Validation Script ---
     (function () {
       'use strict'
       var forms = document.querySelectorAll('#articleForm'); // Lấy form theo ID
       Array.prototype.slice.call(forms)
         .forEach(function (form) {
           form.addEventListener('submit', function (event) {
             // CKEditor đã kiểm tra trong ready() và preventDefault nếu cần
             // Chỉ cần kiểm tra các trường còn lại

             let otherFieldsValid = true;
             let firstInvalidOther = null;

             // Kiểm tra input/select required (trừ textarea CKEditor)
             form.querySelectorAll('input[required], select[required]').forEach(function(input) {
                 if (!input.checkValidity()) {
                     otherFieldsValid = false;
                      if (!firstInvalidOther) firstInvalidOther = input;
                 }
                 if (!input.checkValidity()) { input.classList.add('is-invalid'); } else { input.classList.remove('is-invalid');}
             });

             // Kiểm tra file input (không bắt buộc)
             var fileInput = document.getElementById('file');
             if (fileInput) {
                 var fileFeedback = fileInput.parentElement.querySelector('.invalid-feedback');
                 var fileValid = true;
                 if (fileInput.files.length > 0) {
                     var file = fileInput.files[0];
                     var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.webp)$/i;
                     var maxSize = 5 * 1024 * 1024; // 5MB
                     if (!allowedExtensions.exec(fileInput.value)) {
                         fileValid = false; if (!firstInvalidOther) firstInvalidOther = fileInput; if(fileFeedback) fileFeedback.textContent = 'Định dạng file ảnh không hợp lệ.';
                     } else if (file.size > maxSize) {
                          fileValid = false; if (!firstInvalidOther) firstInvalidOther = fileInput; if(fileFeedback) fileFeedback.textContent = 'Kích thước file quá lớn (tối đa 5MB).';
                     }
                 }
                 if (!fileValid) {
                     otherFieldsValid = false;
                     fileInput.classList.add('is-invalid');
                     if(fileFeedback) fileFeedback.style.display = 'block';
                 } else {
                     fileInput.classList.remove('is-invalid');
                     if(fileFeedback) { fileFeedback.style.display = 'none'; }
                 }
             }

             // Ngăn submit nếu các trường khác không hợp lệ (chỉ khi CKEditor hợp lệ)
             if (!otherFieldsValid && !event.defaultPrevented) {
                event.preventDefault()
                event.stopPropagation()
                 if (firstInvalidOther) { firstInvalidOther.focus(); }
             }

            // Thêm class was-validated để hiển thị styles lỗi/thành công của Bootstrap
            setTimeout(function() { form.classList.add('was-validated'); }, 0);

           }, false)
         })
     })()
 </script>

</body>
</html>