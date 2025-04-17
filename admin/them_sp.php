<?php
if (!isset($_SESSION)) { // Bắt đầu session nếu chưa có
    session_start();
}
include_once("../connection/connect_database.php");

// Phần xử lý PHP khi form được submit
$ngaytao = date("Y-m-d"); // Ngày hiện tại
if (isset($_POST["Ok"])) { // Kiểm tra nút submit có tên "Ok"

    // --- Xử lý dữ liệu từ Checkbox ---
    $loai_da_arr = isset($_POST['loai_da_phu_hop']) && is_array($_POST['loai_da_phu_hop']) ? $_POST['loai_da_phu_hop'] : [];
    $van_de_arr = isset($_POST['van_de_da_giai_quyet']) && is_array($_POST['van_de_da_giai_quyet']) ? $_POST['van_de_da_giai_quyet'] : [];

    // Chuyển mảng thành chuỗi, cách nhau bằng ', '
    $loai_da_str = implode(', ', $loai_da_arr);
    $van_de_str = implode(', ', $van_de_arr);
    // --- Kết thúc xử lý Checkbox ---


    // --- Xử lý File Upload ---
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/jpg'];
        $file_type = $_FILES['file']['type'];
        $file_size = $_FILES['file']['size'];
        $max_file_size = 2000000; // 2MB

        if (in_array($file_type, $allowed_types)) {
            if ($file_size <= $max_file_size) {
                $path = '../images/'; // Thư mục lưu ảnh (đảm bảo tồn tại và có quyền ghi)
                if (!is_dir($path)) {
                    // Versuche, das Verzeichnis rekursiv zu erstellen
                    if (!mkdir($path, 0777, true) && !is_dir($path)) {
                        // Konnte Verzeichnis nicht erstellen
                        echo "<script language='javascript'>alert('Lỗi: Không thể tạo thư mục lưu ảnh: " . htmlspecialchars($path) . "');</script>";
                        // Beende die Ausführung oder handle den Fehler entsprechend
                        // exit; // Beenden, wenn das Verzeichnis kritisch ist
                    } else {
                        // Setze die Berechtigungen explizit, falls mkdir sie nicht korrekt setzt
                        chmod($path, 0777);
                    }
                }


                $tmp_name = $_FILES['file']['tmp_name'];
                $original_name = $_FILES['file']['name'];
                $extension = pathinfo($original_name, PATHINFO_EXTENSION);
                // Làm sạch tên file, thay ký tự đặc biệt bằng '_'
                $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
                // Tạo tên file duy nhất bằng cách thêm timestamp
                $unique_name = $safe_name . '_' . time() . '.' . $extension;
                $destination = $path . $unique_name;

                // --- Dùng Prepared Statement để INSERT ---
                // Cập nhật tên cột SoLuongTonKho trong câu lệnh SQL
                $sql_them = "INSERT INTO sanpham (idNH, idL, TenSP, NgayCapNhat, MoTa, GiaBan, urlHinh, GiaKhuyenmai, SoLanXem, SoLuongTonKho, GhiChu, SoLanMua, loai_da_phu_hop, van_de_da_giai_quyet, AnHien)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 15 placeholders

                $stmt = mysqli_prepare($conn, $sql_them);

                if ($stmt) {
                    // Ép kiểu dữ liệu các biến số
                    $idNH = (int)$_POST['idNH'];
                    $idL = (int)$_POST['idL'];
                    $TenSP = $_POST['TenSP'];
                    $MoTa = $_POST['MoTa'];
                    $GiaBan = (float)$_POST['GiaBan'];
                    $GiaKhuyenmai = isset($_POST['GiaKhuyenmai']) ? (float)$_POST['GiaKhuyenmai'] : 0.0; // Default to 0 if not set
                    $SoLanXem = isset($_POST['SoLanXem']) ? (int)$_POST['SoLanXem'] : 0; // Default to 0
                     // Lấy dữ liệu từ POST với tên cột đúng là SoLuongTonKho
                    $SoLuongTonKho = (int)$_POST['SoLuongTonKho'];
                    $GhiChu = isset($_POST['GhiChu']) ? $_POST['GhiChu'] : ''; // Default to empty string
                    $SoLanMua = isset($_POST['SoLanMua']) ? (int)$_POST['SoLanMua'] : 0; // Default to 0
                    $AnHien = (int)$_POST['AnHien'];

                    // Bind parameters: đảm bảo thứ tự và kiểu dữ liệu khớp với câu lệnh SQL và các dấu ?
                    // i = integer, d = double, s = string, b = blob
                    // idNH(i), idL(i), TenSP(s), NgayCapNhat(s), MoTa(s), GiaBan(d), urlHinh(s), GiaKhuyenmai(d), SoLanXem(i), SoLuongTonKho(i), GhiChu(s), SoLanMua(i), loai_da_phu_hop(s), van_de_da_giai_quyet(s), AnHien(i)
                     // Cập nhật biến $SoLuongTonKho trong bind_param
                    mysqli_stmt_bind_param($stmt, "iisssdsiiisisis", // Kiểm tra lại type string: 15 types
                        $idNH,
                        $idL,
                        $TenSP,
                        $ngaytao, // Dùng $ngaytao đã được lấy ở đầu file
                        $MoTa,
                        $GiaBan,
                        $unique_name, // Lưu tên file ảnh đã xử lý
                        $GiaKhuyenmai,
                        $SoLanXem,
                        $SoLuongTonKho, // Sử dụng biến đã sửa tên
                        $GhiChu,
                        $SoLanMua,
                        $loai_da_str, // Bind chuỗi loại da
                        $van_de_str, // Bind chuỗi vấn đề da
                        $AnHien
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        // Chỉ di chuyển file nếu insert thành công
                        if (move_uploaded_file($tmp_name, $destination)) {
                            echo "<script language='javascript'>alert('Thêm sản phẩm thành công!'); location.href='index_ds_sp.php';</script>";
                            exit; // Thoát sau khi chuyển hướng
                        } else {
                             // Lỗi di chuyển file, ghi log hoặc hiển thị thông báo chi tiết hơn (nếu cần thiết cho debug)
                             $move_error = error_get_last();
                             $error_detail = $move_error ? htmlspecialchars($move_error['message']) : 'Không rõ nguyên nhân.';
                             echo "<script language='javascript'>alert('Thêm thông tin sản phẩm thành công nhưng LỖI UPLOAD hình ảnh!\\nNguyên nhân có thể: Kiểm tra quyền ghi của thư mục images.\\nChi tiết lỗi: " . $error_detail ."'); location.href='index_ds_sp.php';</script>";
                            // Cân nhắc: Xóa bản ghi vừa thêm nếu hình ảnh là bắt buộc và upload lỗi
                            // $last_id = mysqli_insert_id($conn); // Chỉ hoạt động với mysqli_query, không phải stmt
                            // $last_id = mysqli_stmt_insert_id($stmt); // Lấy ID sau khi execute stmt
                            // if ($last_id) {
                            //    mysqli_query($conn, "DELETE FROM sanpham WHERE idSP = $last_id");
                            // }
                             exit;
                        }
                    } else {
                        // Hiển thị lỗi SQL chi tiết hơn khi debug (chỉ trong môi trường phát triển)
                        // echo "<script language='javascript'>alert('Thêm không thành công! Lỗi SQL: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "');</script>";
                         echo "<script language='javascript'>alert('Thêm không thành công! Có lỗi xảy ra khi lưu vào cơ sở dữ liệu.');</script>";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                     echo "<script language='javascript'>alert('Lỗi khi chuẩn bị câu lệnh SQL: " . htmlspecialchars(mysqli_error($conn)) ."!');</script>";
                }
            } else {
                echo "<script language='javascript'>alert('Kích thước file quá lớn (tối đa 2MB)!');</script>";
            }
        } else {
            echo "<script language='javascript'>alert('Định dạng file không hợp lệ (chỉ chấp nhận jpg, jpeg, png, gif)!');</script>";
        }
    } elseif (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        // Xử lý các lỗi upload khác nếu có
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => 'File vượt quá giới hạn upload_max_filesize trong php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'File vượt quá giới hạn MAX_FILE_SIZE.',
            UPLOAD_ERR_PARTIAL    => 'File chỉ được upload một phần.',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm.',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file vào đĩa.',
            UPLOAD_ERR_EXTENSION  => 'Một extension PHP đã chặn việc upload file.',
        ];
        $error_code = $_FILES['file']['error'];
        $error_message = $upload_errors[$error_code] ?? 'Lỗi upload không xác định.';
        echo "<script language='javascript'>alert('Lỗi upload hình ảnh: " . htmlspecialchars($error_message) . "');</script>";

    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        // Người dùng chưa chọn file nhưng file là bắt buộc (theo thuộc tính required trong HTML)
         echo "<script language='javascript'>alert('Bạn chưa chọn hình đại diện!');</script>";
    }
} // Kết thúc khối if (isset($_POST["Ok"]))
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Giả sử header1.php chứa link Bootstrap CSS
    // include_once("header1.php");
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


    <title>Thêm sản phẩm mới</title>
    <?php // include_once('header2.php'); // CSS/JS tùy chỉnh khác ?>
    <script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        h3.form-title {
            color: #0d6efd; /* Bootstrap primary color */
            text-align: center;
            margin-top: 20px;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .custom-form-container {
            max-width: 900px;
            margin: 20px auto 40px auto; /* Thêm margin bottom */
            background-color: #ffffff;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
        }
        .form-label strong {
           font-weight: 600;
           /* color: #495057; */
        }
         /* Đảm bảo label thẳng hàng với input trong layout grid */
         .row .col-form-label {
             display: flex;
             align-items: center;
             padding-top: calc(0.375rem + 1px); /* Căn chỉnh với input mặc định của Bootstrap */
             padding-bottom: calc(0.375rem + 1px);
         }

        .button-group {
            text-align: center;
            margin-top: 35px; /* Tăng khoảng cách */
            padding-top: 25px; /* Thêm padding trên */
            border-top: 1px solid #dee2e6; /* Thêm đường kẻ phân cách */
        }
        .button-group .btn {
            margin-left: 10px;
            margin-right: 10px;
            min-width: 130px; /* Tăng độ rộng nút */
            padding: 10px 20px; /* Tăng padding nút */
            font-size: 1rem; /* Cỡ chữ */
        }
        /* Style cho nhóm checkbox */
        .checkbox-group-label {
             margin-bottom: 0.75rem; /* Khoảng cách dưới label của nhóm */
             display: block; /* Để chiếm 1 dòng */
             font-weight: 600; /* Làm đậm label nhóm */
        }
        .form-check-inline {
             margin-right: 1.5rem; /* Tăng khoảng cách giữa các checkbox */
             margin-bottom: 0.75rem; /* Thêm khoảng cách dưới */
        }
        .form-check-input {
             margin-top: 0.2rem; /* Căn chỉnh checkbox với label */
        }
        .text-danger {
            color: #dc3545 !important;
            font-size: 1.1em; /* Làm dấu * to hơn chút */
            vertical-align: middle;
        }
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
         /* Style cho CKEditor */
         #cke_MoTa {
             margin-top: 5px;
         }
         small.form-text {
             font-size: 0.85em;
         }
         .required-field::after {
             content: " *";
             color: #dc3545;
             font-weight: bold;
         }

    </style>
</head>
<body>
<?php // include_once('header3.php'); // Thanh menu навігації ?>
<div class="container mt-4 mb-5">
    <h3 class="form-title">THÊM SẢN PHẨM MỚI</h3>

    <div class="custom-form-container">
        <form method="post" action="them_sp.php" name="ThemSP" enctype="multipart/form-data" id="productForm" novalidate>
            <div class="row mb-3">
                <label for="TenSP" class="col-sm-3 col-form-label"><strong>Tên sản phẩm<span class="text-danger">*</span></strong></label>
                <div class="col-sm-9">
                    <input type="text" name="TenSP" id="TenSP" class="form-control" required>
                    <div class="invalid-feedback">Vui lòng nhập tên sản phẩm.</div>
                </div>
            </div>

            <div class="row mb-3">
                <label for="idNH" class="col-sm-3 col-form-label"><strong>Nhãn hiệu<span class="text-danger">*</span></strong></label>
                <div class="col-sm-9">
                    <?php
                    // Luôn khởi tạo $conn trước khi sử dụng
                    if (isset($conn)) {
                        $sl_nhanhieu = "SELECT idNH, TenNH FROM nhanhieu ORDER BY TenNH ASC"; // Sắp xếp A-Z
                        $rs_nhanhieu = mysqli_query($conn, $sl_nhanhieu);
                        if (!$rs_nhanhieu) {
                            echo '<div class="alert alert-danger">Lỗi tải nhãn hiệu: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
                        } else {
                            echo '<select id="idNH" name="idNH" class="form-select" required>';
                            echo '<option value="" selected disabled>-- Chọn nhãn hiệu --</option>'; // Thêm option mặc định
                            while ($r = mysqli_fetch_assoc($rs_nhanhieu)) { // Sử dụng mysqli_fetch_assoc
                                echo '<option value="' . $r["idNH"] . '">' . htmlspecialchars($r['TenNH']) . '</option>';
                            }
                            echo '</select>';
                            echo '<div class="invalid-feedback">Vui lòng chọn nhãn hiệu.</div>';
                        }
                    } else {
                         echo '<div class="alert alert-danger">Lỗi kết nối cơ sở dữ liệu. Không thể tải nhãn hiệu.</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="row mb-3">
                <label for="idL" class="col-sm-3 col-form-label"><strong>Loại sản phẩm<span class="text-danger">*</span></strong></label>
                <div class="col-sm-9">
                    <?php
                    if (isset($conn)) {
                        $sl_l = "SELECT idL, TenL FROM loaisp ORDER BY TenL ASC"; // Sắp xếp A-Z
                        $rs_l = mysqli_query($conn, $sl_l);
                         if (!$rs_l) {
                            echo '<div class="alert alert-danger">Lỗi tải loại SP: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
                         } else {
                             echo '<select id="idL" name="idL" class="form-select" required>';
                             echo '<option value="" selected disabled>-- Chọn loại sản phẩm --</option>'; // Thêm option mặc định
                             while ($row_l = mysqli_fetch_assoc($rs_l)) { // Sử dụng mysqli_fetch_assoc
                                 echo '<option value="' . $row_l["idL"] . '">' . htmlspecialchars($row_l['TenL']) . '</option>';
                             }
                             echo '</select>';
                             echo '<div class="invalid-feedback">Vui lòng chọn loại sản phẩm.</div>';
                         }
                    } else {
                        echo '<div class="alert alert-danger">Lỗi kết nối cơ sở dữ liệu. Không thể tải loại sản phẩm.</div>';
                    }
                    ?>
                </div>
            </div>

             <div class="mb-3">
                <label class="form-label checkbox-group-label"><strong>Loại Da Phù Hợp:</strong> <small class="text-muted">(Chọn một hoặc nhiều)</small></label>
                <div class="ps-3">
                    <?php
                    $cac_loai_da = ["Da dầu", "Da khô", "Da hỗn hợp", "Da thường", "Da nhạy cảm", "Mọi loại da"];
                    foreach ($cac_loai_da as $loai_da):
                        $id_checkbox = "loai_da_" . str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $loai_da)); // Tạo id an toàn hơn
                    ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="loai_da_phu_hop[]" value="<?php echo htmlspecialchars($loai_da); ?>" id="<?php echo $id_checkbox; ?>">
                            <label class="form-check-label" for="<?php echo $id_checkbox; ?>">
                                <?php echo htmlspecialchars($loai_da); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label checkbox-group-label"><strong>Vấn Đề Da Giải Quyết:</strong> <small class="text-muted">(Chọn một hoặc nhiều)</small></label>
                 <div class="ps-3">
                    <?php
                     // ----- ĐÃ THÊM "Làm sạch da" VÀO MẢNG NÀY -----
                    $cac_van_de = ["Mụn", "Lỗ chân lông to", "Thâm mụn", "Da không đều màu", "Nám, tàn nhang", "Lão hóa", "Da khô, thiếu ẩm", "Da nhạy cảm, mẩn đỏ", "Kiềm dầu", "Làm sáng da", "Dưỡng ẩm", "Chống nắng", "Làm sạch da"];
                    foreach ($cac_van_de as $van_de):
                        $id_checkbox = "van_de_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $van_de); // Tạo id an toàn hơn
                    ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="van_de_da_giai_quyet[]" value="<?php echo htmlspecialchars($van_de); ?>" id="<?php echo $id_checkbox; ?>">
                            <label class="form-check-label" for="<?php echo $id_checkbox; ?>">
                                <?php echo htmlspecialchars($van_de); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                 </div>
                 <small class="form-text text-muted ps-3">Chọn các vấn đề chính mà sản phẩm này giúp cải thiện.</small>
            </div>

            <div class="row mb-3">
                <label for="GiaBan" class="col-sm-3 col-form-label"><strong>Giá bán<span class="text-danger">*</span></strong></label>
                <div class="col-sm-9">
                    <input type="number" id="GiaBan" name="GiaBan" value="0" min="0" step="1000" class="form-control" required>
                     <div class="invalid-feedback">Giá bán phải là số không âm.</div>
                </div>
            </div>

            <div class="row mb-3">
                <label for="GiaKhuyenmai" class="col-sm-3 col-form-label"><strong>Giá khuyến mãi</strong></label>
                <div class="col-sm-9">
                    <input type="number" id="GiaKhuyenmai" name="GiaKhuyenmai" value="0" min="0" step="1000" class="form-control">
                    <small class="form-text text-muted">Nhập 0 nếu không có khuyến mãi.</small>
                </div>
            </div>

            <div class="row mb-3">
                 <label for="SoLuongTonKho" class="col-sm-3 col-form-label"><strong>Số lượng tồn kho<span class="text-danger">*</span></strong></label>
                 <div class="col-sm-9">
                      <input type="number" id="SoLuongTonKho" name="SoLuongTonKho" value="0" min="0" class="form-control" required>
                      <div class="invalid-feedback">Số lượng tồn kho phải là số không âm.</div>
                 </div>
            </div>

            <div class="row mb-3">
                <label for="file" class="col-sm-3 col-form-label"><strong>Hình đại diện<span class="text-danger">*</span></strong></label>
                <div class="col-sm-9">
                    <input type="hidden" name="MAX_FILE_SIZE" value="2000000">
                    <input type="file" name="file" id="file" class="form-control" required accept="image/jpeg, image/png, image/gif, image/bmp, image/jpg">
                    <small class="form-text text-muted">Chọn file ảnh (jpg, png, gif...). Tối đa 2MB.</small>
                     <div class="invalid-feedback">Vui lòng chọn hình đại diện hợp lệ (jpg, png, gif, tối đa 2MB).</div>
                </div>
            </div>

            <div class="row mb-3">
                <label for="SoLanXem" class="col-sm-3 col-form-label"><strong>Số lần xem</strong></label>
                <div class="col-sm-9">
                    <input type="number" id="SoLanXem" name="SoLanXem" value="0" min="0" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <label for="SoLanMua" class="col-sm-3 col-form-label"><strong>Số lần mua</strong></label>
                <div class="col-sm-9">
                    <input type="number" id="SoLanMua" name="SoLanMua" value="0" min="0" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <label for="GhiChu" class="col-sm-3 col-form-label"><strong>Ghi chú</strong></label>
                <div class="col-sm-9">
                    <textarea id="GhiChu" name="GhiChu" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="row mb-3">
                <label for="MoTa" class="col-sm-3 col-form-label"><strong>Mô tả chi tiết<span class="text-danger">*</span></strong></label>
                <div class="col-sm-9">
                    <textarea id="MoTa" name="MoTa" class="form-control" required></textarea>
                    <script type="text/javascript">
                        // Đảm bảo CKEditor được khởi tạo sau khi DOM sẵn sàng
                        document.addEventListener("DOMContentLoaded", function() {
                            CKEDITOR.replace('MoTa');
                            // Tích hợp CKEditor với validation (tùy chọn)
                            CKEDITOR.instances.MoTa.on('change', function() {
                                CKEDITOR.instances.MoTa.updateElement(); // Cập nhật textarea ẩn
                                // Trigger validation nếu cần
                            });
                        });
                    </script>
                     <div class="invalid-feedback" id="mota-feedback">Vui lòng nhập mô tả chi tiết.</div>
                </div>
            </div>

            <div class="row mb-3">
                <label for="AnHien" class="col-sm-3 col-form-label"><strong>Trạng thái<span class="text-danger">*</span></strong></label>
                <div class="col-sm-9">
                    <select id="AnHien" name="AnHien" class="form-select" required>
                        <option value="1" selected>Hiện</option>
                        <option value="0">Ẩn</option>
                    </select>
                    <div class="invalid-feedback">Vui lòng chọn trạng thái.</div>
                </div>
            </div>

            <div class="button-group">
                <button name="Ok" id="Ok" type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Lưu sản phẩm
                 </button>
                 <button type="button" id="Huy" name="Huy" class="btn btn-secondary" onclick="goBack()">
                     <i class="fas fa-times me-2"></i> Hủy bỏ
                 </button>
            </div>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>


<script type="text/javascript">
    // Hàm quay lại trang trước
    function goBack() {
        var goback = confirm("Bạn có chắc chắn muốn hủy và quay lại trang danh sách?");
         if (goback == true) {
            window.location.href = 'index_ds_sp.php'; // Hoặc trang danh sách của bạn
         }
    }

    // --- Kiểm tra phía Client bằng Bootstrap 5 Validation ---
    (function () {
      'use strict'

      // Lấy tất cả các form cần áp dụng validation styles của Bootstrap
      var forms = document.querySelectorAll('#productForm') // Chỉ định form cụ thể

      // Lặp qua chúng và ngăn chặn việc submit nếu không hợp lệ
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            let isValid = true; // Biến cờ tổng thể
            let firstInvalidElement = null; // Để focus vào phần tử lỗi đầu tiên

            // --- Kiểm tra CKEditor ---
            var moTaEditor = CKEDITOR.instances.MoTa;
            var moTaValue = '';
            var moTaTextarea = document.getElementById('MoTa');
            var moTaFeedback = document.getElementById('mota-feedback');

            if (moTaEditor) {
                moTaEditor.updateElement(); // Cập nhật dữ liệu vào textarea ẩn
                moTaValue = moTaTextarea.value.trim();
                if (moTaValue === '') {
                   isValid = false;
                   // Thêm class is-invalid cho container của CKEditor (nếu muốn)
                   // moTaEditor.container.$.classList.add('is-invalid'); // Cần kiểm tra cấu trúc DOM của CKEditor
                   moTaFeedback.style.display = 'block'; // Hiển thị thông báo lỗi riêng
                   if (!firstInvalidElement) firstInvalidElement = moTaTextarea; // Focus textarea liên kết
                 } else {
                   // moTaEditor.container.$.classList.remove('is-invalid');
                    moTaFeedback.style.display = 'none';
                 }
            } else if (moTaTextarea.value.trim() === '') {
                 // Fallback nếu CKEditor chưa tải
                 isValid = false;
                 moTaTextarea.classList.add('is-invalid');
                 if (!firstInvalidElement) firstInvalidElement = moTaTextarea;
             }


            // --- Kiểm tra các trường input/select thông thường ---
            form.querySelectorAll('input[required], select[required]').forEach(function(input) {
                if (!input.checkValidity()) {
                    isValid = false;
                     if (!firstInvalidElement) firstInvalidElement = input; // Lưu phần tử lỗi đầu tiên
                }
                // Thêm/xóa class is-invalid để Bootstrap hiển thị lỗi
                if (!input.checkValidity()) {
                   input.classList.add('is-invalid');
                } else {
                   input.classList.remove('is-invalid');
                }
            });

             // --- Kiểm tra file input riêng ---
             var fileInput = document.getElementById('file');
             var fileFeedback = fileInput.nextElementSibling.nextElementSibling; // Giả sử div invalid-feedback ngay sau small
             var fileValid = true;
             if (fileInput.hasAttribute('required') && fileInput.files.length === 0) {
                isValid = false;
                fileValid = false;
                 if (!firstInvalidElement) firstInvalidElement = fileInput;
             } else if (fileInput.files.length > 0) {
                 var file = fileInput.files[0];
                 var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.bmp)$/i;
                 var maxSize = 2 * 1024 * 1024; // 2MB

                 if (!allowedExtensions.exec(fileInput.value)) {
                     fileValid = false;
                      if (!firstInvalidElement) firstInvalidElement = fileInput;
                      fileFeedback.textContent = 'Định dạng file ảnh không hợp lệ (chỉ .jpg, .jpeg, .png, .gif, .bmp).';
                 } else if (file.size > maxSize) {
                    fileValid = false;
                     if (!firstInvalidElement) firstInvalidElement = fileInput;
                     fileFeedback.textContent = 'Kích thước file quá lớn (tối đa 2MB).';
                 }
             }

             if (!fileValid) {
                 isValid = false;
                 fileInput.classList.add('is-invalid');
                 fileFeedback.style.display = 'block';
             } else {
                 fileInput.classList.remove('is-invalid');
                 fileFeedback.style.display = 'none';
             }

             // --- Kiểm tra các trường number không âm (ngoài required) ---
             form.querySelectorAll('input[type="number"]').forEach(function(input) {
                 if (input.value !== '' && parseFloat(input.value) < 0) {
                     isValid = false;
                     input.classList.add('is-invalid');
                     // Cập nhật hoặc thêm div invalid-feedback nếu cần
                     var feedback = input.nextElementSibling;
                     if (feedback && feedback.classList.contains('invalid-feedback')) {
                         feedback.textContent = 'Giá trị không được âm.';
                         feedback.style.display = 'block';
                     }
                      if (!firstInvalidElement) firstInvalidElement = input;
                 } else if (input.checkValidity()) { // Xóa lỗi nếu giá trị hợp lệ
                     input.classList.remove('is-invalid');
                     var feedback = input.nextElementSibling;
                     if (feedback && feedback.classList.contains('invalid-feedback') && !input.hasAttribute('required')) { // Chỉ ẩn nếu không phải lỗi required
                         feedback.style.display = 'none';
                     }
                 }
             });


            // Nếu form không hợp lệ
            if (!isValid) {
              event.preventDefault() // Ngăn chặn việc submit form
              event.stopPropagation() // Ngừng lan truyền sự kiện

              // Focus vào phần tử không hợp lệ đầu tiên
               if (firstInvalidElement) {
                 firstInvalidElement.focus();
               }

              // Hiển thị thông báo lỗi chung (tùy chọn)
              alert("Vui lòng kiểm tra lại các trường được đánh dấu đỏ.");

            } else {
                // Form hợp lệ, có thể thêm hiệu ứng loading hoặc xử lý khác ở đây trước khi submit
                console.log("Form is valid, submitting...");
            }

            // Thêm class 'was-validated' vào form để hiển thị styles lỗi/thành công của Bootstrap
            // Lưu ý: Có thể bạn muốn thêm class này *chỉ khi* submit không thành công lần đầu
            // hoặc sau khi người dùng tương tác với trường bị lỗi.
            // Để đơn giản, ta thêm ngay sau khi kiểm tra.
            form.classList.add('was-validated')


          }, false) // Tham số false cho capturing phase
        })
    })()

</script>

<?php // include_once('footer.php'); ?>

</body>
</html>