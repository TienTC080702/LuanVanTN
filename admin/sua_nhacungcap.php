<?php
if (!isset($_SESSION)) {
    session_start();
}
// include_once('session_check_admin.php'); // Đảm bảo admin đã đăng nhập
include_once('../connection/connect_database.php'); // Kết nối CSDL

$idNCC = null;
$ncc = null; // Biến lưu thông tin nhà cung cấp
$errors = []; // Mảng lưu lỗi validation
// $success_message = ''; // Không cần biến này nữa vì dùng session flash

// 1. Lấy ID từ URL và kiểm tra tính hợp lệ
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $idNCC = (int)$_GET['id'];

    // 2. Xử lý khi form được submit (POST request)
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
        // Lấy ID từ hidden input để đảm bảo an toàn
        $id_hidden = isset($_POST['idNCC']) ? (int)$_POST['idNCC'] : 0;

        // Chỉ xử lý nếu ID ẩn khớp với ID từ GET (hoặc là ID hợp lệ)
        if ($id_hidden == $idNCC && $idNCC > 0) {
            // Lấy dữ liệu từ form
            $tenNCC = isset($_POST['TenNCC']) ? trim($_POST['TenNCC']) : '';
            $diaChiNCC = isset($_POST['DiaChiNCC']) ? trim($_POST['DiaChiNCC']) : '';
            $dienThoaiNCC = isset($_POST['DienThoaiNCC']) ? trim($_POST['DienThoaiNCC']) : '';
            $emailNCC = isset($_POST['EmailNCC']) ? trim($_POST['EmailNCC']) : '';
            // Lấy dữ liệu GhiChu, cho phép rỗng hoặc NULL nếu DB cho phép
            $ghiChu = isset($_POST['GhiChu']) ? trim($_POST['GhiChu']) : null;

            // --- Validation ---
            if (empty($tenNCC)) {
                $errors['TenNCC'] = "Tên nhà cung cấp không được để trống.";
            }
            if (empty($diaChiNCC)) {
                $errors['DiaChiNCC'] = "Địa chỉ không được để trống.";
            }
             if (empty($dienThoaiNCC)) {
                $errors['DienThoaiNCC'] = "Điện thoại không được để trống.";
            } elseif (!preg_match('/^[0-9\s\-\+\(\)]+$/', $dienThoaiNCC)) { // Kiểm tra định dạng cơ bản
                 $errors['DienThoaiNCC'] = "Định dạng điện thoại không hợp lệ.";
             }
            if (!empty($emailNCC) && !filter_var($emailNCC, FILTER_VALIDATE_EMAIL)) {
                $errors['EmailNCC'] = "Địa chỉ email không hợp lệ.";
            }
            // Thêm các validation khác nếu cần

            // Nếu không có lỗi
            if (empty($errors)) {
                // Chuẩn bị câu lệnh UPDATE bằng Prepared Statement (thêm GhiChu)
                $sql_update = "UPDATE nhacungcap SET TenNCC = ?, DiaChiNCC = ?, DienThoaiNCC = ?, EmailNCC = ?, GhiChu = ? WHERE idNCC = ?";
                $stmt = mysqli_prepare($conn, $sql_update);

                if ($stmt) {
                    // Bind parameters (s = string, i = integer) - thêm 's' cho GhiChu
                    mysqli_stmt_bind_param($stmt, "sssssi",
                        $tenNCC,
                        $diaChiNCC,
                        $dienThoaiNCC,
                        $emailNCC,
                        $ghiChu, // Bind biến ghi chú
                        $idNCC
                    );

                    // Thực thi câu lệnh
                    if (mysqli_stmt_execute($stmt)) {
                        // Đặt thông báo thành công vào session để hiển thị sau khi redirect
                        $_SESSION['success_message'] = "Cập nhật thông tin nhà cung cấp thành công!";
                         // Chuyển hướng về trang danh sách nhà cung cấp (đúng tên file)
                        header("Location: ds_nhacungcap.php"); // <--- ĐÃ SỬA
                        exit(); // Dừng script ngay sau khi chuyển hướng
                    } else {
                        $errors['db'] = "Lỗi khi cập nhật cơ sở dữ liệu: " . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $errors['db'] = "Lỗi khi chuẩn bị câu lệnh: " . mysqli_error($conn);
                }
            }
            // Nếu có lỗi validation, $errors sẽ được dùng để hiển thị bên dưới
            // và form sẽ được điền lại bằng dữ liệu POST (xử lý ở phần HTML)
        } else {
            // Lỗi ID không khớp hoặc không hợp lệ trong POST
            $errors['general'] = "Có lỗi xảy ra hoặc ID nhà cung cấp không hợp lệ.";
             // Không nên hiển thị $errors['general'] = "ID nhà cung cấp không hợp lệ hoặc không khớp."; vì có thể gây nhầm lẫn
             $idNCC = null; // Reset ID để ngăn form hiển thị nếu có lỗi ID nghiêm trọng
        }
    }

    // 3. Lấy thông tin nhà cung cấp hiện tại để hiển thị (GET request hoặc sau POST lỗi)
    // Chỉ lấy lại từ DB nếu không phải là POST request thành công (vì đã redirect)
    // hoặc nếu là POST request nhưng có lỗi validation (để điền lại form với data cũ nếu POST data ko hợp lệ)
    if ($idNCC && ($_SERVER["REQUEST_METHOD"] != "POST" || !empty($errors))) {
         $sql_select = "SELECT * FROM nhacungcap WHERE idNCC = ?"; // Lấy tất cả các cột
         $stmt_select = mysqli_prepare($conn, $sql_select);
         if ($stmt_select) {
             mysqli_stmt_bind_param($stmt_select, "i", $idNCC);
             mysqli_stmt_execute($stmt_select);
             $result_select = mysqli_stmt_get_result($stmt_select);
             $ncc = mysqli_fetch_assoc($result_select); // $ncc sẽ chứa cả GhiChu và NgayTao
             mysqli_stmt_close($stmt_select);

             if (!$ncc) {
                 // Nếu không tìm thấy NCC với ID này
                 $errors['general'] = "Không tìm thấy nhà cung cấp với ID (" . htmlspecialchars($idNCC) . ") này.";
                 $idNCC = null; // Reset ID để không hiển thị form
             }
         } else {
              $errors['db'] = "Lỗi khi lấy thông tin nhà cung cấp: " . mysqli_error($conn);
              $idNCC = null; // Reset ID
         }
     } elseif (!$idNCC && $_SERVER["REQUEST_METHOD"] == "POST" && empty($errors['general']) ) {
         // Trường hợp POST nhưng ID bị lỗi từ trước (VD: $id_hidden != $idNCC)
         // Không cần làm gì thêm ở đây, lỗi đã được đặt trong $errors['general']
     }

} else {
    // ID không hợp lệ ngay từ đầu (trong GET)
    $errors['general'] = "ID nhà cung cấp không hợp lệ hoặc không được cung cấp.";
    $idNCC = null; // Đảm bảo ID null
}

// Gán giá trị cho form: ưu tiên dữ liệu POST (nếu có lỗi), sau đó mới đến dữ liệu từ DB ($ncc)
// Nếu $ncc không tồn tại (do lỗi ID), các biến $form_ sẽ là chuỗi rỗng
$form_tenNCC = isset($_POST['TenNCC']) && !empty($errors) ? htmlspecialchars($_POST['TenNCC']) : ($ncc ? htmlspecialchars($ncc['TenNCC']) : '');
$form_diaChiNCC = isset($_POST['DiaChiNCC']) && !empty($errors) ? htmlspecialchars($_POST['DiaChiNCC']) : ($ncc ? htmlspecialchars($ncc['DiaChiNCC']) : '');
$form_dienThoaiNCC = isset($_POST['DienThoaiNCC']) && !empty($errors) ? htmlspecialchars($_POST['DienThoaiNCC']) : ($ncc ? htmlspecialchars($ncc['DienThoaiNCC']) : '');
$form_emailNCC = isset($_POST['EmailNCC']) && !empty($errors) ? htmlspecialchars($_POST['EmailNCC']) : ($ncc ? htmlspecialchars($ncc['EmailNCC']) : '');
$form_ghiChu = isset($_POST['GhiChu']) && !empty($errors) ? htmlspecialchars($_POST['GhiChu']) : ($ncc ? htmlspecialchars($ncc['GhiChu'] ?? '') : '');

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Nhà Cung Cấp</title>
    <?php include_once("header1.php"); ?>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Styles giữ nguyên như trước */
        h3.form-title { color: #007bff; text-align: center; margin: 1.5rem 0; font-weight: bold; }
        .custom-form-container {
            max-width: 700px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
        }
        .form-label strong { font-weight: 600; }
        .button-group { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #dee2e6; }
        .button-group .btn { margin: 0 10px; min-width: 120px; }
        .alert ul { margin-bottom: 0; padding-left: 20px; }
        .is-invalid { border-color: #dc3545 !important; }
        .invalid-feedback { color: #dc3545; display: block; font-size: 0.875em; }
        .required-field::after { content: " *"; color: #dc3545; }
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <h3 class="form-title">CHỈNH SỬA NHÀ CUNG CẤP</h3>

    <div class="custom-form-container">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php elseif (!empty($errors['db'])): ?>
             <div class="alert alert-danger">Lỗi CSDL: <?php echo htmlspecialchars($errors['db']); ?></div>
        <?php endif; ?>

        <?php if ($idNCC && $ncc): // Chỉ hiển thị form nếu có ID hợp lệ và tìm thấy NCC ?>
            <form method="post" action="sua_nhacungcap.php?id=<?php echo $idNCC; ?>" name="SuaNCC" id="supplierForm" novalidate>
                <input type="hidden" name="idNCC" value="<?php echo $idNCC; ?>">

                <div class="mb-3">
                    <label for="TenNCC" class="form-label"><strong>Tên Nhà Cung Cấp<span class="required-field"></span></strong></label>
                    <input type="text" name="TenNCC" id="TenNCC" class="form-control <?php echo isset($errors['TenNCC']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo $form_tenNCC; ?>" required>
                    <?php if (isset($errors['TenNCC'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['TenNCC']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="DiaChiNCC" class="form-label"><strong>Địa Chỉ<span class="required-field"></span></strong></label>
                    <textarea name="DiaChiNCC" id="DiaChiNCC" class="form-control <?php echo isset($errors['DiaChiNCC']) ? 'is-invalid' : ''; ?>" rows="3" required><?php echo $form_diaChiNCC; ?></textarea>
                    <?php if (isset($errors['DiaChiNCC'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['DiaChiNCC']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="DienThoaiNCC" class="form-label"><strong>Điện Thoại<span class="required-field"></span></strong></label>
                    <input type="tel" name="DienThoaiNCC" id="DienThoaiNCC" class="form-control <?php echo isset($errors['DienThoaiNCC']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo $form_dienThoaiNCC; ?>" required>
                     <?php if (isset($errors['DienThoaiNCC'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['DienThoaiNCC']; ?></div>
                     <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="EmailNCC" class="form-label"><strong>Email</strong></label>
                    <input type="email" name="EmailNCC" id="EmailNCC" class="form-control <?php echo isset($errors['EmailNCC']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo $form_emailNCC; ?>">
                     <?php if (isset($errors['EmailNCC'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['EmailNCC']; ?></div>
                     <?php endif; ?>
                </div>

                 <div class="mb-3">
                    <label for="GhiChu" class="form-label"><strong>Ghi chú</strong></label>
                    <textarea name="GhiChu" id="GhiChu" class="form-control" rows="3"><?php echo $form_ghiChu; ?></textarea>
                     </div>

                <div class="button-group">
                    <button name="save" type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Lưu Thay Đổi
                    </button>
                    <a href="ds_nhacungcap.php" class="btn btn-secondary"> <i class="fas fa-times me-2"></i> Hủy bỏ
                    </a>
                </div>
            </form>
        <?php else: ?>
            <?php // Hiển thị nút quay lại nếu có lỗi chung hoặc lỗi DB ?>
             <?php if (!empty($errors['general']) || !empty($errors['db'])): ?>
                 <div class="text-center mt-3">
                      <a href="ds_nhacungcap.php" class="btn btn-secondary"> <i class="fas fa-arrow-left me-2"></i> Quay Lại Danh Sách
                      </a>
                 </div>
             <?php elseif(empty($errors)) : // Trường hợp không có NCC và cũng không có lỗi nào được set ?>
                  <div class="alert alert-warning">Không có thông tin nhà cung cấp để hiển thị hoặc ID không hợp lệ.</div>
                   <div class="text-center mt-3">
                      <a href="ds_nhacungcap.php" class="btn btn-secondary"> <i class="fas fa-arrow-left me-2"></i> Quay Lại Danh Sách
                      </a>
                 </div>
             <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<?php include_once('footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Đóng kết nối nếu cần
if(isset($conn)) mysqli_close($conn);
?>