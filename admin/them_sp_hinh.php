<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Giả định các file này đã bao gồm link CSS Bootstrap 5 và các cấu hình cần thiết
    include_once("../connection/connect_database.php");
    include_once("header1.php");
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <title>Thêm hình ảnh sản phẩm</title>
    <?php include_once('header2.php'); ?>

    <style>
        /* Giữ lại hoặc tùy chỉnh thêm nếu cần */
        .page-title {
            color: #007bff; /* Màu xanh dương */
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }

        /* Tùy chỉnh thẻ Card của Bootstrap nếu muốn */
        .card {
            margin-bottom: 2rem; /* Khoảng cách dưới các card */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Điều chỉnh đổ bóng nhẹ hơn */
        }

        .card-header h4 {
             margin-bottom: 0; /* Bỏ margin mặc định của h4 trong card-header */
             font-size: 1.1rem;
             font-weight: 600;
        }

        .image-gallery .img-thumbnail {
             margin-bottom: 0.5rem;
             /* Có thể thêm hiệu ứng khi hover nếu muốn */
             /* transition: transform 0.2s ease-in-out; */
        }
        /* .image-gallery .img-thumbnail:hover {
             transform: scale(1.05);
        } */

         .image-item .btn-delete-img {
             /* Tùy chỉnh nút xóa nếu cần */
             /* position: absolute; top: 5px; right: 5px; */
         }
         .form-text {
            font-size: 0.875em;
         }
         label strong {
            font-weight: 600; /* Đảm bảo label đậm rõ */
         }
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4 mb-5"> <?php // Thêm mb-5 để có khoảng cách dưới cùng ?>
    <h3 class="page-title">THÊM HÌNH ẢNH MÔ TẢ SẢN PHẨM</h3>

    <div class="card">
        <div class="card-header">
             Thêm hình ảnh mới
        </div>
        <div class="card-body">
            <form method="post" action="" name="ThemHinhSP" id="ThemHinhSPForm" enctype="multipart/form-data">
                <div class="row mb-3 align-items-center"> <?php // Thêm align-items-center ?>
                    <label for="idSP" class="col-sm-3 col-form-label"><strong>Chọn sản phẩm</strong><span class="text-danger">*</span></label> <?php // Thêm dấu * đỏ ?>
                    <div class="col-sm-9">
                        <?php
                        // Lấy idSP đang được chọn (ưu tiên POST, sau đó GET nếu có)
                        $selected_idSP = null;
                        if (isset($_POST['idSP'])) {
                            $selected_idSP = (int)$_POST['idSP'];
                        } elseif (isset($_GET['idSP'])) { // Thêm hỗ trợ GET để link tới dễ hơn
                            $selected_idSP = (int)$_GET['idSP'];
                        }

                        $sl_sanpham = "select idSP, TenSP from sanpham order by idSP desc"; // Chỉ lấy id và tên
                        $rs_sanpham = mysqli_query($conn, $sl_sanpham);
                        if (!$rs_sanpham) {
                            echo "<div class='alert alert-danger'>Không thể kết nối đến bảng sản phẩm!</div>";
                        } else {
                        ?>
                        <select id="idSP" name="idSP" class="form-select" required onchange="this.form.submit()"> <?php // Giữ nguyên required và onchange ?>
                            <option value="">-- Chọn sản phẩm --</option> <?php // Giữ dòng mặc định ?>
                            <?php
                            while ($r = $rs_sanpham->fetch_assoc()) {
                                $is_selected = ($selected_idSP == $r["idSP"]) ? "selected" : ""; // Kiểm tra để chọn đúng
                                echo "<option value='" . $r["idSP"] . "' " . $is_selected . ">" . $r['idSP'] . " - " . htmlspecialchars($r['TenSP']) . "</option>";
                            }
                            ?>
                        </select>
                        <?php } // end if $rs_sanpham ?>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <label for="file" class="col-sm-3 col-form-label"><strong>Chọn hình ảnh</strong><span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="hidden" name="MAX_FILE_SIZE" value="5000000"> <?php // Giữ nguyên ?>
                        <input type="file" name="file[]" id="file" class="form-control" multiple required accept="image/*"> <?php // Giữ nguyên required, accept ?>
                        <small class="form-text text-muted">Chọn một hoặc nhiều file ảnh (jpg, png, gif...). Tối đa 5MB mỗi file.</small>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <label for="AnHien" class="col-sm-3 col-form-label"><strong>Trạng thái</strong></label>
                    <div class="col-sm-9">
                        <select id="AnHien" name="AnHien" class="form-select">
                            <option value="1" selected><strong>Hiện</strong></option> <?php // Giữ nguyên ?>
                            <option value="0"><strong>Ẩn</strong></option>
                        </select>
                    </div>
                </div>

                <div class="row mt-4"> <?php // Thêm khoảng cách trên cho nút ?>
                    <div class="col-sm-9 offset-sm-3">
                        <button name="Ok" id="Ok" type="submit" class="btn btn-primary me-2">
                           <i class="bi bi-plus-circle-fill me-2"></i>Thêm hình ảnh
                        </button>
                        <button type="button" id="Huy" name="Huy" onclick="getConfirmation()" class="btn btn-secondary">
                           <i class="bi bi-x-circle me-2"></i>Hủy bỏ
                        </button>
                    </div>
                </div>
            </form>
        </div> <?php // end card-body ?>
    </div> <?php // end card ?>


    <?php
    // Hiển thị danh sách hình ảnh của sản phẩm được chọn (Trong Card riêng)
    if ($selected_idSP && $rs_sanpham) { // Chỉ hiển thị nếu đã chọn SP và kết nối DB thành công
        $sql_hinh = "SELECT id_hinh, urlHinh FROM sanpham_hinh WHERE idSP = ? ORDER BY id_hinh DESC"; // Sắp xếp mới nhất lên đầu
        $stmt_hinh = mysqli_prepare($conn, $sql_hinh);

        if($stmt_hinh) {
            mysqli_stmt_bind_param($stmt_hinh, "i", $selected_idSP);
            mysqli_stmt_execute($stmt_hinh);
            $rs_hinh = mysqli_stmt_get_result($stmt_hinh);

            if (mysqli_num_rows($rs_hinh) > 0) {
                echo "<div class='card image-gallery'>"; // Bắt đầu Card cho gallery
                echo "<div class='card-header'><h4>Hình ảnh hiện tại của sản phẩm:</h4></div>";
                echo "<div class='card-body'>";
                echo "<div class='row g-3'>"; // Thêm g-3 để có khoảng cách giữa các cột

                while ($row = mysqli_fetch_assoc($rs_hinh)) {
                    echo "<div class='col-6 col-md-4 col-lg-3 image-item text-center'>"; // Giữ nguyên cấu trúc cột
                    echo "<img src='../images/" . htmlspecialchars($row['urlHinh']) . "' class='img-thumbnail' alt='Hình sản phẩm " . htmlspecialchars($row['id_hinh']) . "' />"; // Thêm id vào alt
                    // Form xóa ảnh
                    echo "<form method='post' action='#delete' onsubmit='return confirm(\"Bạn có chắc chắn muốn xóa hình này?\");' style='display: inline-block;'>"; // Giữ nguyên
                    echo "<input type='hidden' name='delete_id' value='" . $row['id_hinh'] . "' />";
                    echo "<input type='hidden' name='idSP' value='" . $selected_idSP . "' />"; // Giữ nguyên
                    echo "<button type='submit' name='delete_img' class='btn btn-danger btn-sm btn-delete-img' title='Xóa hình ảnh'>"; // Thêm class và title
                    echo "<i class='bi bi-trash-fill'></i> Xóa"; // Thêm icon
                    echo "</button>";
                    echo "</form>";
                    echo "</div>"; // end image-item col
                }
                echo "</div>"; // end row
                echo "</div>"; // end card-body
                echo "</div>"; // end card image-gallery
            } else {
                echo "<div class='alert alert-info'>Sản phẩm này chưa có hình ảnh mô tả nào.</div>"; // Giữ nguyên
            }
            mysqli_stmt_close($stmt_hinh);
        } else {
            echo "<div class='alert alert-warning'>Lỗi khi chuẩn bị truy vấn hình ảnh.</div>"; // Giữ nguyên
        }
    }
    ?>
    <div id="delete"></div> <?php // Anchor cho action form xóa ?>

</div> <?php // end container ?>

<script type="text/javascript">
    // Giữ nguyên JavaScript vì không yêu cầu sửa chức năng
    function getConfirmation() {
        var retVal = confirm("Bạn có muốn hủy và quay lại trang danh sách sản phẩm?");
        if (retVal == true) {
            window.location.href = 'index_ds_sp.php'; // Điều hướng về trang danh sách
        }
    }

    document.getElementById('ThemHinhSPForm').addEventListener('submit', function(event) {
        var fileInput = document.getElementById('file');
        var idSPSelect = document.getElementById('idSP');

        if (idSPSelect.value === '') {
            alert('Vui lòng chọn một sản phẩm.');
            event.preventDefault(); // Prevent form submission
            return;
        }

        if (fileInput.files.length === 0 && !document.querySelector('input[name="delete_img"]')) { // Chỉ kiểm tra nếu không phải là form xóa
             // Kiểm tra xem có file nào được chọn không
             var hasFiles = false;
             for (var i = 0; i < fileInput.files.length; i++) {
                 if(fileInput.files[i]) {
                     hasFiles = true;
                     break;
                 }
             }
             if (!hasFiles) {
                alert('Vui lòng chọn ít nhất một hình ảnh để tải lên.');
                event.preventDefault(); // Prevent form submission
             }
        }
        // Có thể thêm kiểm tra kích thước, loại file ở đây nếu muốn (nhưng PHP đã xử lý)
    });
</script>

<?php
// --- Xử lý PHP ---
// Giữ nguyên toàn bộ phần xử lý PHP vì không yêu cầu sửa đổi logic
// 1. Xử lý XÓA ảnh
if (isset($_POST['delete_img']) && isset($_POST['delete_id'])) {
    // ... (toàn bộ code xử lý xóa ảnh như cũ) ...
     $delete_id = (int)$_POST['delete_id'];
     $idSP_after_delete = isset($_POST['idSP']) ? (int)$_POST['idSP'] : null; // Lấy idSP để biết load lại sản phẩm nào

     // Sử dụng prepared statement để lấy tên file an toàn hơn
     $query_get_filename = mysqli_prepare($conn, "SELECT urlHinh FROM sanpham_hinh WHERE id_hinh = ?");
     if ($query_get_filename) {
         mysqli_stmt_bind_param($query_get_filename, "i", $delete_id);
         mysqli_stmt_execute($query_get_filename);
         $result_filename = mysqli_stmt_get_result($query_get_filename);
         $data = mysqli_fetch_assoc($result_filename);
         mysqli_stmt_close($query_get_filename);

         if ($data) {
             $filename = $data['urlHinh'];
             $filepath = '../images/' . $filename;

             // Xóa file vật lý trước
             $file_deleted = false;
             if (file_exists($filepath) && is_file($filepath)) { // Kiểm tra là file trước khi xóa
                 if (unlink($filepath)) {
                     $file_deleted = true;
                 }
             } else {
                 // File không tồn tại vật lý, vẫn có thể xóa DB record
                 $file_deleted = true; // Coi như đã xóa thành công để tiếp tục xóa DB
                 // Ghi log nếu cần: error_log("Attempted to delete non-existent file: $filepath");
             }

             // Nếu xóa file vật lý thành công (hoặc file không tồn tại), thì xóa DB record
             if ($file_deleted) {
                 $delete_query = mysqli_prepare($conn, "DELETE FROM sanpham_hinh WHERE id_hinh = ?");
                 if ($delete_query) {
                     mysqli_stmt_bind_param($delete_query, "i", $delete_id);
                     if (mysqli_stmt_execute($delete_query)) {
                         echo "<script>alert('Xóa hình thành công'); window.location.href = window.location.pathname + '?idSP=" . $idSP_after_delete . "#delete';</script>"; // Redirect lại với idSP
                         exit; // Dừng script sau khi redirect
                     } else {
                         echo "<script>alert('Lỗi: Không thể xóa bản ghi hình ảnh trong cơ sở dữ liệu.'); window.location.href = window.location.pathname + '?idSP=" . $idSP_after_delete . "#delete';</script>";
                         exit;
                     }
                     mysqli_stmt_close($delete_query);
                 } else {
                     echo "<script>alert('Lỗi: Không thể chuẩn bị câu lệnh xóa.'); window.location.href = window.location.pathname + '?idSP=" . $idSP_after_delete . "#delete';</script>";
                     exit;
                 }
             } else {
                 echo "<script>alert('Lỗi: Không thể xóa file hình ảnh trên server.'); window.location.href = window.location.pathname + '?idSP=" . $idSP_after_delete . "#delete';</script>";
                 exit;
             }
         } else {
             echo "<script>alert('Lỗi: Không tìm thấy thông tin hình ảnh để xóa.'); window.location.href = window.location.pathname + '?idSP=" . $idSP_after_delete . "#delete';</script>";
             exit;
         }
     } else {
         echo "<script>alert('Lỗi: Không thể chuẩn bị truy vấn lấy tên file.'); window.location.href = window.location.pathname + '?idSP=" . $idSP_after_delete . "#delete';</script>";
         exit;
     }
}


// 2. Xử lý THÊM ảnh
if (isset($_POST["Ok"]) && isset($_POST['idSP']) && !empty($_POST['idSP'])) {
    // ... (toàn bộ code xử lý thêm ảnh như cũ) ...
     $idSP = (int)$_POST['idSP'];
     $AnHien = (int)$_POST['AnHien'];
     $errors = [];
     $success_count = 0;

     // Kiểm tra file upload có tồn tại và không có lỗi cơ bản
     if (isset($_FILES['file']) && count($_FILES['file']['name']) > 0 && $_FILES['file']['error'][0] !== UPLOAD_ERR_NO_FILE)
     {
         $path = '../images/'; // Thư mục lưu ảnh
         if (!is_dir($path)) {
             mkdir($path, 0777, true); // Cố gắng tạo nếu chưa có
         }
         $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/jpg'];
         $max_file_size = 5000000; // 5MB

         // Prepared statement cho INSERT (chuẩn bị một lần ngoài vòng lặp)
         $sql_them = "INSERT INTO sanpham_hinh (idSP, urlHinh, AnHien) VALUES (?, ?, ?)";
         $stmt_them = mysqli_prepare($conn, $sql_them);

         if (!$stmt_them) {
             $errors[] = "Lỗi khi chuẩn bị câu lệnh INSERT: " . mysqli_error($conn);
         } else {
             // Duyệt qua từng file
             for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
                 // Bỏ qua nếu có lỗi upload cho file cụ thể này
                 if ($_FILES['file']['error'][$i] !== UPLOAD_ERR_OK) {
                     // Ghi lỗi nếu cần, ví dụ: $errors[] = "Lỗi upload file '" . $_FILES['file']['name'][$i] . "': " . $_FILES['file']['error'][$i];
                     continue; // Bỏ qua file này, xử lý file tiếp theo
                 }

                 $tmp_name = $_FILES['file']['tmp_name'][$i];
                 $original_name = $_FILES['file']['name'][$i];
                 $type = $_FILES['file']['type'][$i];
                 $size = $_FILES['file']['size'][$i];

                 // --- Kiểm tra Type & Size ---
                 if (!in_array($type, $allowed_types)) {
                     $errors[] = "File '" . htmlspecialchars($original_name) . "' có định dạng không hợp lệ.";
                     continue;
                 }
                 if ($size > $max_file_size) {
                     $errors[] = "File '" . htmlspecialchars($original_name) . "' vượt quá kích thước cho phép (5MB).";
                     continue;
                 }

                 // --- Xử lý tên file để tránh trùng lặp và ký tự đặc biệt ---
                 $extension = pathinfo($original_name, PATHINFO_EXTENSION);
                 $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
                 // Tạo tên file duy nhất bằng cách thêm idSP và timestamp
                 $unique_name = $safe_name . '_sp' . $idSP . '_' . time() . '_' . $i . '.' . $extension;
                 $destination = $path . $unique_name;

                 // --- Di chuyển file và Insert vào DB ---
                 if (move_uploaded_file($tmp_name, $destination)) {
                     // Bind và Execute prepared statement
                     mysqli_stmt_bind_param($stmt_them, "isi", $idSP, $unique_name, $AnHien);
                     if (mysqli_stmt_execute($stmt_them)) {
                         $success_count++;
                     } else {
                         $errors[] = "Không thể thêm thông tin file '" . htmlspecialchars($original_name) . "' vào CSDL: " . mysqli_stmt_error($stmt_them);
                         // Xóa file đã upload nếu insert DB lỗi
                         if (file_exists($destination)) unlink($destination);
                     }
                 } else {
                     $errors[] = "Không thể di chuyển file '" . htmlspecialchars($original_name) . "' đến thư mục đích.";
                 }
             } // end for loop

             mysqli_stmt_close($stmt_them); // Đóng statement sau khi vòng lặp kết thúc
         } // end if $stmt_them prepared successfully

         // --- Thông báo kết quả ---
         $message = "";
         if ($success_count > 0) {
             $message .= "Thêm thành công " . $success_count . " hình ảnh. ";
         }
         if (!empty($errors)) {
             $message .= "Có lỗi xảy ra với một số file: " . implode("; ", $errors);
             echo "<script language='javascript'>alert('" . addslashes($message) . "'); window.location.href = window.location.pathname + '?idSP=" . $idSP . "';</script>";
         } elseif ($success_count > 0) {
             echo "<script language='javascript'>alert('Thêm thành công " . $success_count . " hình ảnh!'); window.location.href = window.location.pathname + '?idSP=" . $idSP . "';</script>";
         } else {
             // Trường hợp không có file nào được xử lý thành công và cũng không có lỗi rõ ràng (ít xảy ra)
             echo "<script language='javascript'>alert('Không có hình ảnh nào được thêm.'); window.location.href = window.location.pathname + '?idSP=" . $idSP . "';</script>";
         }
         exit; // Dừng script sau khi xử lý và redirect

     } elseif (isset($_POST["Ok"])) { // Nếu nhấn OK nhưng không chọn file hoặc có lỗi upload cơ bản
         echo "<script language='javascript'>alert('Bạn chưa chọn hình ảnh nào hoặc có lỗi xảy ra khi tải lên.');</script>";
     }
}

?>

<?php include_once('footer.php'); ?>
</body>
</html>