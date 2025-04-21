<?php
// Bắt đầu session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    // ob_start(); // Bật nếu bạn cần dùng header() sau khi có output
}

// Bao gồm file kết nối CSDL
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

// --- KIỂM TRA QUYỀN ADMIN HOẶC NGƯỜI DÙNG ĐƯỢC PHÉP ---
if (!isset($_SESSION['IDUser'])) { // Thay 'IDUser' bằng key session đúng của bạn
    // Có thể kiểm tra thêm quyền admin nếu cần
    // if (!isset($_SESSION['IsAdmin']) || $_SESSION['IsAdmin'] !== true) { ... }
    header('Location: ../site/DangNhap.php'); // Chuyển hướng đến trang đăng nhập
    exit();
}
$idNguoiLap = intval($_SESSION['IDUser']); // Lấy ID người dùng đang đăng nhập

// --- KHỞI TẠO BIẾN ---
$error_message = null;
$success_message = null; // Thường không dùng vì chuyển hướng
$uploaded_filename = null; // Lưu tên file đã upload thành công để xóa nếu rollback

// --- XỬ LÝ KHI FORM ĐƯỢC SUBMIT ---
if (isset($_POST['submit_sp_moi'])) { // Sử dụng name của nút submit

    // --- Lấy dữ liệu Sản phẩm mới ---
    $TenSP_new = isset($_POST['TenSP_new']) ? trim($_POST['TenSP_new']) : null;
    $idL_new = isset($_POST['idL_new']) ? intval($_POST['idL_new']) : 0;
    $brandInput = isset($_POST['idNH_new']) ? trim($_POST['idNH_new']) : null; // ID hoặc Tên Thương hiệu mới
    $GiaBan_new = isset($_POST['GiaBan_new']) ? floatval(str_replace(',', '', $_POST['GiaBan_new'])) : 0;
    $MoTa_new = isset($_POST['MoTa_new']) ? trim($_POST['MoTa_new']) : null;
    // Không lấy urlHinh_new từ POST nữa, sẽ xử lý từ $_FILES

    // --- Lấy dữ liệu Phiếu nhập ---
    $idNCC = isset($_POST['idNCC']) ? intval($_POST['idNCC']) : 0;
    $DonGiaNhap = isset($_POST['DonGiaNhap']) ? floatval(str_replace(',', '', $_POST['DonGiaNhap'])) : 0;
    $SoLuong = isset($_POST['SoLuong']) ? intval($_POST['SoLuong']) : 0;
    $NgayNhap = isset($_POST['NgayNhap']) ? $_POST['NgayNhap'] : date('Y-m-d');
    $SoHoaDonNCC = isset($_POST['SoHoaDonNCC']) ? trim($_POST['SoHoaDonNCC']) : null;
    $GhiChuPN = isset($_POST['GhiChuPN']) ? trim($_POST['GhiChuPN']) : null;

    // --- Biến xử lý ---
    $idNH_final = null; // ID thương hiệu cuối cùng sẽ dùng
    $is_new_brand = false; // Flag cho biết có tạo thương hiệu mới không
    $new_brand_name = null;
    $escaped_new_sp_name = mysqli_real_escape_string($conn, $TenSP_new); // Escape tên SP mới

    // --- Kiểm tra dữ liệu bắt buộc ---
    if (empty($TenSP_new)) {
        $error_message = "Vui lòng nhập Tên sản phẩm mới.";
    } elseif ($idL_new <= 0) {
        $error_message = "Vui lòng chọn Loại sản phẩm.";
    } elseif (empty($brandInput)) {
        $error_message = "Vui lòng chọn hoặc nhập Thương hiệu.";
    } elseif ($GiaBan_new <= 0) {
        $error_message = "Vui lòng nhập Giá bán hợp lệ (>0).";
    } elseif ($idNCC <= 0) {
        $error_message = "Vui lòng chọn Nhà cung cấp.";
    } elseif ($DonGiaNhap <= 0) {
        $error_message = "Vui lòng nhập Đơn giá nhập hợp lệ (> 0).";
    } elseif ($SoLuong <= 0) {
        $error_message = "Vui lòng nhập Số lượng nhập hợp lệ (> 0).";
    } elseif (empty($NgayNhap) || !DateTime::createFromFormat('Y-m-d', $NgayNhap)) {
        $error_message = "Vui lòng chọn Ngày nhập hợp lệ.";
    } else {
         // B1: Kiểm tra trùng tên SP
         $check_name_sql = "SELECT idSP FROM sanpham WHERE TenSP = ?";
         $stmt_check_name = mysqli_prepare($conn, $check_name_sql);
         mysqli_stmt_bind_param($stmt_check_name, "s", $escaped_new_sp_name);
         mysqli_stmt_execute($stmt_check_name);
         mysqli_stmt_store_result($stmt_check_name);
         if (mysqli_stmt_num_rows($stmt_check_name) > 0) {
             $error_message = "Sản phẩm với tên '" . htmlspecialchars($TenSP_new) . "' đã tồn tại.";
         }
         mysqli_stmt_close($stmt_check_name);

         // B2: Xử lý input Thương hiệu (nếu không có lỗi trùng tên SP)
         if (empty($error_message)) {
             if (is_numeric($brandInput) && intval($brandInput) > 0) {
                 // Chọn thương hiệu cũ
                 $idNH_final = intval($brandInput);
                 $check_nh_sql = "SELECT idNH FROM nhanhieu WHERE idNH = ?"; // Kiểm tra TH tồn tại
                 $stmt_check_nh = mysqli_prepare($conn, $check_nh_sql);
                 mysqli_stmt_bind_param($stmt_check_nh, "i", $idNH_final);
                 mysqli_stmt_execute($stmt_check_nh);
                 mysqli_stmt_store_result($stmt_check_nh);
                 if (mysqli_stmt_num_rows($stmt_check_nh) == 0){
                      $error_message = "Thương hiệu được chọn (ID: $idNH_final) không tồn tại.";
                      $idNH_final = null; // Đặt lại để không tiếp tục
                 }
                 mysqli_stmt_close($stmt_check_nh);
             } elseif (!empty($brandInput)) { // Chỉ xử lý nếu brandInput không rỗng
                 // Nhập tên thương hiệu mới
                 $is_new_brand = true; // Giả định là mới
                 $new_brand_name = $brandInput;
                 $escaped_new_brand_name = mysqli_real_escape_string($conn, $new_brand_name);

                 // Kiểm tra xem tên thương hiệu mới này đã tồn tại chưa
                 $check_bname_sql = "SELECT idNH FROM nhanhieu WHERE TenNH = ?";
                 $stmt_check_bname = mysqli_prepare($conn, $check_bname_sql);
                 mysqli_stmt_bind_param($stmt_check_bname, "s", $escaped_new_brand_name);
                 mysqli_stmt_execute($stmt_check_bname);
                 $result_bname = mysqli_stmt_get_result($stmt_check_bname);
                 if ($existing_brand = mysqli_fetch_assoc($result_bname)) {
                     $idNH_final = $existing_brand['idNH']; // Tên đã tồn tại -> dùng ID cũ
                     $is_new_brand = false; // Không cần tạo mới nữa
                 }
                 // Nếu không tìm thấy, is_new_brand vẫn là true, idNH_final sẽ được gán sau khi insert
                 mysqli_stmt_close($stmt_check_bname);
             } else { // Trường hợp brandInput rỗng
                  $error_message = "Vui lòng chọn hoặc nhập Thương hiệu.";
             }
         } // Kết thúc xử lý Thương hiệu

         // B3: Xử lý Upload Ảnh (nếu không có lỗi nào trước đó)
         $sp_urlHinh = 'default_product.png'; // Mặc định
         if(empty($error_message)) {
            $upload_dir = "../images/"; // !!! THAY ĐỔI ĐƯỜNG DẪN NẾU CẦN !!!
            // Tạo thư mục nếu chưa có (nên làm thủ công hoặc kiểm tra kỹ quyền)
            // if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_file_size = 5 * 1024 * 1024; // 5MB

            if (isset($_FILES['urlHinh_new']) && $_FILES['urlHinh_new']['error'] == UPLOAD_ERR_OK) {
                $file_tmp_path = $_FILES['urlHinh_new']['tmp_name'];
                $file_name = basename($_FILES['urlHinh_new']['name']); // Lấy tên file gốc an toàn hơn
                $file_size = $_FILES['urlHinh_new']['size'];
                $file_type = mime_content_type($file_tmp_path);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (!in_array($file_type, $allowed_types)) {
                    $error_message = "Lỗi: Chỉ chấp nhận file ảnh JPG, PNG, GIF, WEBP.";
                } elseif ($file_size > $max_file_size) {
                    $error_message = "Lỗi: Kích thước file ảnh không được vượt quá 5MB.";
                } else {
                    // Tạo tên file duy nhất và an toàn
                    $sanitized_base_name = preg_replace("/[^a-zA-Z0-9_\-]/", "_", pathinfo($file_name, PATHINFO_FILENAME));
                    $uploaded_filename = time() . '_' . $sanitized_base_name . '.' . $file_ext;
                    $dest_path = $upload_dir . $uploaded_filename;

                    if (move_uploaded_file($file_tmp_path, $dest_path)) {
                        $sp_urlHinh = $uploaded_filename; // Lưu tên file mới vào biến để insert CSDL
                    } else {
                        $error_message = "Lỗi: Không thể lưu file ảnh đã tải lên. Kiểm tra quyền ghi thư mục '$upload_dir'.";
                        error_log("File upload move error for: " . $file_name . " to " . $dest_path);
                    }
                }
            } elseif (isset($_FILES['urlHinh_new']) && $_FILES['urlHinh_new']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['urlHinh_new']['error'] != UPLOAD_ERR_OK) {
                 $error_message = "Lỗi tải lên file ảnh. Mã lỗi: " . $_FILES['urlHinh_new']['error'];
                 error_log("File upload error code: " . $_FILES['urlHinh_new']['error']);
            }
            // Nếu không có file hoặc lỗi (ngoài size/type), dùng ảnh mặc định
         } // Kết thúc xử lý Upload Ảnh

        // --- B4: Nếu không có lỗi -> Tiến hành Transaction ---
        if (empty($error_message)) {
            $TongTienPN = $DonGiaNhap * $SoLuong;

            mysqli_begin_transaction($conn);
            try {
                 $idSP_newly_created = null; // Biến để lưu ID SP mới

                 // ---- THÊM THƯƠNG HIỆU MỚI NẾU CẦN ----
                 if ($is_new_brand) {
                      $brand_idL = $idL_new;
                      $brand_ThuTu = 1;
                      $brand_AnHien = 1;

                      $sql_insert_nh = "INSERT INTO nhanhieu (TenNH, idL, ThuTu, AnHien) VALUES (?, ?, ?, ?)";
                      $stmt_insert_nh = mysqli_prepare($conn, $sql_insert_nh);
                      if (!$stmt_insert_nh) throw new Exception("Lỗi chuẩn bị thêm TH mới: " . mysqli_error($conn));
                      mysqli_stmt_bind_param($stmt_insert_nh, "siii", $escaped_new_brand_name, $brand_idL, $brand_ThuTu, $brand_AnHien);
                      if (!mysqli_stmt_execute($stmt_insert_nh)){
                           if (mysqli_errno($conn) == 1062) throw new Exception("Lỗi trùng lặp khi thêm Thương hiệu mới.");
                           else throw new Exception("Lỗi khi thêm Thương hiệu mới: " . mysqli_stmt_error($stmt_insert_nh));
                      }
                      $idNH_final = mysqli_insert_id($conn); // Lấy ID thương hiệu mới
                      mysqli_stmt_close($stmt_insert_nh);
                      if ($idNH_final <= 0) throw new Exception("Không lấy được ID Thương hiệu mới.");
                 }

                 // ---- THÊM SẢN PHẨM MỚI ----
                 // idNH_final đã có giá trị
                 $sp_MoTa = !empty($MoTa_new) ? $MoTa_new : '';
                 // $sp_urlHinh đã được xử lý ở trên
                 $sp_GiaKM = 0;
                 $sp_SoLanXem = 0;
                 $sp_SoLanMua = 0;
                 $sp_AnHien = 1;

                 $sql_insert_sp = "INSERT INTO sanpham
                                     (TenSP, idNH, idL, MoTa, NgayCapNhat, GiaBan, GiaKhuyenmai, urlHinh, SoLanXem, SoLuongTonKho, SoLanMua, AnHien)
                                   VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, 0, ?, ?)";
                 $stmt_insert_sp = mysqli_prepare($conn, $sql_insert_sp);
                 if (!$stmt_insert_sp) throw new Exception("Lỗi chuẩn bị thêm SP mới: " . mysqli_error($conn));
                 mysqli_stmt_bind_param($stmt_insert_sp, "siisdisiii",
                     $escaped_new_sp_name, $idNH_final, $idL_new, $sp_MoTa,
                     $GiaBan_new, $sp_GiaKM, $sp_urlHinh, $sp_SoLanXem,
                     $sp_SoLanMua, $sp_AnHien
                 );
                 if (!mysqli_stmt_execute($stmt_insert_sp)) {
                      if (mysqli_errno($conn) == 1062) throw new Exception("Lỗi trùng lặp khi thêm sản phẩm mới.");
                      else throw new Exception("Lỗi khi thêm sản phẩm mới: " . mysqli_stmt_error($stmt_insert_sp));
                 }
                 $idSP_newly_created = mysqli_insert_id($conn); // Lấy ID sản phẩm mới
                 mysqli_stmt_close($stmt_insert_sp);
                 if ($idSP_newly_created <= 0) throw new Exception("Không lấy được ID sản phẩm mới.");

                // ---- THÊM PHIẾU NHẬP ----
                 $sql_insert_pn = "INSERT INTO phieunhap (idNCC, idSP, SoLuong, DonGiaNhap, NgayNhap, TongTien, idNguoiLap, SoHoaDonNCC, GhiChuPN)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                 $stmt_insert_pn = mysqli_prepare($conn, $sql_insert_pn);
                 if (!$stmt_insert_pn) throw new Exception("Lỗi chuẩn bị thêm PN: " . mysqli_error($conn));
                 mysqli_stmt_bind_param($stmt_insert_pn, "iiidsdiss", // Đã sửa lần trước
                      $idNCC, $idSP_newly_created, $SoLuong, $DonGiaNhap, $NgayNhap, $TongTienPN, $idNguoiLap, $SoHoaDonNCC, $GhiChuPN
                 );
                 if (!mysqli_stmt_execute($stmt_insert_pn)) throw new Exception("Lỗi khi thêm PN: " . mysqli_stmt_error($stmt_insert_pn));
                 mysqli_stmt_close($stmt_insert_pn);

                // ---- CẬP NHẬT TỒN KHO BAN ĐẦU ----
                $sql_update_kho = "UPDATE sanpham SET SoLuongTonKho = ? WHERE idSP = ?";
                 $stmt_update_kho = mysqli_prepare($conn, $sql_update_kho);
                 if (!$stmt_update_kho) throw new Exception("Lỗi chuẩn bị cập nhật kho: " . mysqli_error($conn));
                 mysqli_stmt_bind_param($stmt_update_kho, "ii", $SoLuong, $idSP_newly_created);
                if (!mysqli_stmt_execute($stmt_update_kho)) throw new Exception("Lỗi cập nhật kho: " . mysqli_stmt_error($stmt_update_kho));
                mysqli_stmt_close($stmt_update_kho);

                // ---- HOÀN TẤT TRANSACTION ----
                mysqli_commit($conn);
                $_SESSION['flash_message_pn'] = "Thêm sản phẩm mới '$TenSP_new' và phiếu nhập đầu tiên thành công!";
                header("Location: index_phieu_nhap.php"); // Chuyển hướng về danh sách PN
                exit();

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error_message = "Giao dịch thất bại: " . $e->getMessage();
                // Xóa file ảnh đã upload nếu có lỗi transaction
                if ($uploaded_filename && file_exists($upload_dir . $uploaded_filename)) {
                     if (unlink($upload_dir . $uploaded_filename)) {
                          error_log("Rolled back transaction, deleted uploaded file: " . $uploaded_filename);
                     } else {
                          error_log("Rolled back transaction, FAILED to delete uploaded file: " . $uploaded_filename);
                     }
                }
                error_log("Transaction failed for adding new product purchase order: " . $e->getMessage());
            }
        } // Kết thúc if empty($error_message) cuối cùng
    } // Kết thúc else kiểm tra dữ liệu cơ bản
} // Kết thúc if submit form
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); // Đảm bảo header1 không chứa session_start() hoặc output ?>
    <title>Thêm Phiếu Nhập Cho Sản Phẩm Mới</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> <?php // Nâng version FA nếu cần ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .form-container { max-width: 800px; margin: 30px auto; padding: 30px; background-color: #f9f9f9; border-radius: 10px; border: 1px solid #ddd; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        h3.form-title { color: #198754; text-align: center; margin-bottom: 1.5rem; font-weight: bold; }
        .button-group { text-align: center; margin-top: 25px; }
        .button-group .btn { margin: 0 10px; padding: 10px 20px; }
        .required-mark { color: red; font-weight: bold; margin-left: 2px; }
        /* CSS Select2 */
        .select2-container--bootstrap-5 .select2-selection--single { height: calc(1.5em + .75rem + 2px) !important; padding: .375rem .75rem !important; border: 1px solid #ced4da !important; border-radius: .25rem !important; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 1.5 !important; padding-left: 0 !important; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow { height: calc(1.5em + .75rem) !important; top: 1px !important; }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection { border-color: #86b7fe !important; box-shadow: 0 0 0 0.25rem rgb(13 110 253 / 25%) !important; }
        /* Select2 trong modal/dropdown cha */
        .select2-container--bootstrap-5 .select2-dropdown { z-index: 1060; /* Đảm bảo cao hơn modal Bootstrap nếu dùng */ }
        .form-text { font-size: 0.875em; color: #6c757d; }
        fieldset { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; border-radius: 5px; background-color: #fff; }
        legend { font-size: 1.2em; font-weight: bold; color: #0d6efd; width: auto; padding: 0 10px; margin-bottom: 15px; border: none; /* Bỏ border của legend */ }
        legend i { margin-right: 8px; }
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">NHẬP HÀNG CHO SẢN PHẨM MỚI</h3>

        <?php
        // Hiển thị lỗi nếu có
        if (!empty($error_message)) {
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
            echo "<strong>Lỗi:</strong> " . htmlspecialchars($error_message);
            echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
            echo "</div>";
        }
        ?>

        <?php // Form với enctype ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="form-them-phieu-nhap-moi" enctype="multipart/form-data">

            <fieldset>
                <legend><i class="fas fa-box-open"></i> Thông Tin Sản Phẩm Mới</legend>

                <div class="mb-3">
                     <label for="TenSP_new" class="form-label"><strong>Tên Sản Phẩm:</strong><span class="required-mark">*</span></label>
                     <input type="text" class="form-control" id="TenSP_new" name="TenSP_new" required value="<?php echo isset($_POST['TenSP_new']) ? htmlspecialchars($_POST['TenSP_new']) : ''; ?>" placeholder="Nhập tên đầy đủ của sản phẩm">
                 </div>

                 <div class="row">
                     <div class="col-md-6 mb-3">
                         <label for="idL_new" class="form-label"><strong>Loại Sản Phẩm:</strong><span class="required-mark">*</span></label>
                         <select class="form-select" id="idL_new" name="idL_new" required>
                             <option value="" disabled <?php echo (!isset($_POST['idL_new']) || $_POST['idL_new'] == '') ? 'selected' : ''; ?>>-- Chọn loại sản phẩm --</option>
                             <?php
                             $query_loai = "SELECT idL, TenL FROM loaisp ORDER BY TenL ASC";
                             $result_loai = mysqli_query($conn, $query_loai);
                             if ($result_loai) {
                                 while ($loai_row = mysqli_fetch_assoc($result_loai)) {
                                     $selected = (isset($_POST['idL_new']) && $_POST['idL_new'] == $loai_row['idL']) ? 'selected' : '';
                                     echo "<option value='{$loai_row['idL']}' $selected>" . htmlspecialchars($loai_row['TenL']) . "</option>";
                                 }
                                 mysqli_free_result($result_loai);
                             }
                             ?>
                         </select>
                     </div>
                     <div class="col-md-6 mb-3">
                          <label for="idNH_new" class="form-label"><strong>Chọn hoặc Nhập Thương Hiệu:</strong><span class="required-mark">*</span></label>
                          <select class="form-select" id="idNH_new" name="idNH_new" required style="width: 100%;">
                               <?php
                               // Giữ lại giá trị cũ của Thương hiệu khi có lỗi
                               if (isset($_POST['idNH_new'])) {
                                   if(is_numeric($_POST['idNH_new'])){
                                       $selected_nh_id = intval($_POST['idNH_new']);
                                       $query_selected_nh = "SELECT TenNH FROM nhanhieu WHERE idNH = ?"; // Use prepared statement
                                       $stmt_sel_nh = mysqli_prepare($conn, $query_selected_nh);
                                       mysqli_stmt_bind_param($stmt_sel_nh, "i", $selected_nh_id);
                                       mysqli_stmt_execute($stmt_sel_nh);
                                       $result_selected_nh = mysqli_stmt_get_result($stmt_sel_nh);
                                       if ($result_selected_nh && $selected_nh_row = mysqli_fetch_assoc($result_selected_nh)) {
                                            echo "<option value='$selected_nh_id' selected>" . htmlspecialchars($selected_nh_row['TenNH']) . "</option>";
                                       }
                                       mysqli_stmt_close($stmt_sel_nh);
                                   } else { // Nếu là tên mới đã nhập
                                       echo "<option value='".htmlspecialchars($_POST['idNH_new'])."' selected>" . htmlspecialchars($_POST['idNH_new']) . "</option>";
                                   }
                               } else {
                                    echo '<option value="" disabled selected>-- Chọn hoặc gõ tên thương hiệu --</option>';
                               }
                               // Truy vấn danh sách Thương hiệu
                               $query_nh = "SELECT idNH, TenNH FROM nhanhieu ORDER BY TenNH ASC";
                               $result_nh = mysqli_query($conn, $query_nh);
                               if ($result_nh) {
                                   while ($nh_row = mysqli_fetch_assoc($result_nh)) {
                                       echo "<option value='{$nh_row['idNH']}'>" . htmlspecialchars($nh_row['TenNH']) . "</option>";
                                   }
                                   mysqli_free_result($result_nh);
                               }
                               ?>
                          </select>
                          <div class="form-text">Chọn hoặc gõ tên thương hiệu mới và nhấn Enter.</div>
                     </div>
                 </div>

                 <div class="mb-3">
                    <label for="GiaBan_new" class="form-label"><strong>Giá Bán Dự Kiến (VNĐ):</strong><span class="required-mark">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="GiaBan_new" name="GiaBan_new" min="1" required value="<?php echo isset($_POST['GiaBan_new']) ? htmlspecialchars($_POST['GiaBan_new']) : ''; ?>" placeholder="Nhập giá bán dự kiến cho khách">
                 </div>

                 <div class="mb-3">
                    <label for="MoTa_new" class="form-label"><strong>Mô Tả Sản Phẩm (Nếu có):</strong></label>
                    <textarea class="form-control" id="MoTa_new" name="MoTa_new" rows="3"><?php echo isset($_POST['MoTa_new']) ? htmlspecialchars($_POST['MoTa_new']) : ''; ?></textarea>
                 </div>

                 <div class="mb-3">
                    <label for="urlHinh_new" class="form-label"><strong>Hình Ảnh Sản Phẩm:</strong></label>
                    <input class="form-control" type="file" id="urlHinh_new" name="urlHinh_new" accept="image/jpeg,image/png,image/gif,image/webp"> <?php // accept chính xác hơn ?>
                     <div class="form-text">Chọn file ảnh (JPG, PNG, GIF, WEBP - Tối đa 5MB). Để trống sẽ dùng ảnh mặc định.</div>
                 </div>

            </fieldset>

             <fieldset>
                <legend><i class="fas fa-receipt"></i> Thông Tin Phiếu Nhập Hàng Đầu Tiên</legend>

                 <div class="mb-3">
                     <label for="idNCC" class="form-label"><strong>Chọn Nhà Cung Cấp:</strong><span class="required-mark">*</span></label>
                     <select class="form-select" id="idNCC" name="idNCC" required>
                         <option value="" disabled <?php echo (!isset($_POST['idNCC']) || $_POST['idNCC'] == '') ? 'selected' : ''; ?>>-- Chọn nhà cung cấp --</option>
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

                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="DonGiaNhap" class="form-label"><strong>Đơn Giá Nhập (VNĐ):</strong><span class="required-mark">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="DonGiaNhap" name="DonGiaNhap" min="1" required value="<?php echo isset($_POST['DonGiaNhap']) ? htmlspecialchars($_POST['DonGiaNhap']) : ''; ?>" placeholder="Giá mua vào">
                    </div>
                     <div class="col-md-6 mb-3">
                         <label for="SoLuong" class="form-label"><strong>Số Lượng Nhập:</strong><span class="required-mark">*</span></label>
                         <input type="number" class="form-control" id="SoLuong" name="SoLuong" min="1" required value="<?php echo isset($_POST['SoLuong']) ? htmlspecialchars($_POST['SoLuong']) : ''; ?>" placeholder="Số lượng lô hàng đầu tiên">
                     </div>
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
                    <label for="GhiChuPN" class="form-label"><strong>Ghi Chú Phiếu Nhập:</strong></label>
                     <textarea class="form-control" id="GhiChuPN" name="GhiChuPN" rows="3"><?php echo isset($_POST['GhiChuPN']) ? htmlspecialchars($_POST['GhiChuPN']) : ''; ?></textarea>
                </div>

            </fieldset>

            <div class="button-group">
                 <?php // Đảm bảo name khớp với PHP check ?>
                <button type="submit" name="submit_sp_moi" class="btn btn-success btn-lg">
                    <i class="fas fa-plus-circle"></i> Thêm Sản Phẩm & Phiếu Nhập
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.href='index_phieu_nhap.php';">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>

    </div>
</div>

<?php include_once('footer.php'); ?>

<?php // Script section ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> <?php // Thêm Bootstrap JS nếu cần ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Khởi tạo Select2 cho các dropdown
     $('#idNCC').select2({
         placeholder: "-- Chọn nhà cung cấp --",
         allowClear: true,
         theme: "bootstrap-5"
     });
     $('#idL_new').select2({
         placeholder: "-- Chọn loại sản phẩm --",
         theme: "bootstrap-5",
         allowClear: true
        });
     $('#idNH_new').select2({
         placeholder: "-- Chọn hoặc gõ tên thương hiệu mới --",
         theme: "bootstrap-5",
         tags: true, // Cho phép tạo mới thương hiệu
         allowClear: true,
         createTag: function (params) {
              var term = $.trim(params.term);
              if (term === '') { return null; }
              // Chỉ tạo tag nếu nó là text (không phải là số)
               if (!$.isNumeric(term)) {
                   // Thêm chữ (mới) vào text hiển thị của tag
                  return { id: term, text: term + ' (mới)' }
               }
              // Nếu là số thì không tạo tag, để Select2 chọn option có sẵn
              return null;
         }
     });

});
</script>

</body>
</html>