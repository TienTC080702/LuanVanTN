<?php
session_start();
ob_start(); // Bắt đầu bộ đệm đầu ra

include_once('../connection/connect_database.php');
include_once('../libs/lib.php'); // Include libs nếu cần

// --- PHẦN XỬ LÝ LOGIC PHP ---

$user_data = null; // Biến lưu dữ liệu người dùng
$thongbao = ""; // Biến lưu thông báo lỗi/thành công
$update_success = false; // Biến cờ cho biết cập nhật thành công

// Kiểm tra đăng nhập
if (!isset($_SESSION['IDUser'])) {
    // Nếu chưa đăng nhập, chuyển hướng về trang đăng nhập hoặc trang chủ
    header('Location: DangNhap.php'); // Hoặc index.php
    exit; // Dừng thực thi script ngay lập tức
}

// Lấy thông tin người dùng hiện tại để hiển thị form
$sl = "SELECT * FROM Users WHERE idUser=" . $_SESSION['IDUser'];
$kq = mysqli_query($conn, $sl);
if ($kq && mysqli_num_rows($kq) > 0) {
    $user_data = mysqli_fetch_array($kq);
} else {
    // Xử lý trường hợp không tìm thấy user (dù đã đăng nhập?)
    // Có thể hủy session và chuyển hướng
    session_destroy();
    header('Location: DangNhap.php');
    exit; // Dừng thực thi script
}

// Xử lý khi form được gửi đi (nhấn nút 'Sua')
if (isset($_POST['Sua'])) {
    // Lấy tất cả user để kiểm tra trùng lặp sau này
    $query = mysqli_query($conn, "SELECT * FROM Users");
    $thongbao = ""; // Reset thông báo lỗi/thành công

    // Lấy dữ liệu từ form, dùng trim để loại bỏ khoảng trắng thừa, dùng ?? '' để tránh lỗi nếu key không tồn tại
    $input_username = trim($_POST['Username'] ?? '');
    $input_hotenk = trim($_POST['HoTenK'] ?? '');
    $input_diachi = trim($_POST['DiaChi'] ?? '');
    $input_dienthoai = trim($_POST['DienThoai'] ?? '');
    $input_email = trim($_POST['Email'] ?? '');
    $input_pass_old = $_POST['Password_old'] ?? ''; // Không trim mật khẩu
    $input_pass_new = $_POST['Password'] ?? '';
    $input_pass_confirm = $_POST['Password_1'] ?? '';

    // --- Validate dữ liệu cơ bản ---
    if (empty($input_username) || empty($input_hotenk) || empty($input_dienthoai) || empty($input_email) ) {
        $thongbao .= "Vui lòng nhập đầy đủ Username, Họ tên, Điện thoại, Email. ";
    }

    // --- Validate mật khẩu (chỉ khi người dùng cố gắng thay đổi mật khẩu) ---
    $isChangingPassword = !empty($input_pass_old) || !empty($input_pass_new) || !empty($input_pass_confirm);
    if ($isChangingPassword) {
        // Nếu muốn đổi MK thì phải nhập cả 3 trường
        if (empty($input_pass_old) || empty($input_pass_new) || empty($input_pass_confirm)) {
            $thongbao .= "Vui lòng nhập đủ Mật khẩu cũ, Mật khẩu mới và Nhập lại mật khẩu mới nếu muốn đổi. ";
        } else {
            // Kiểm tra mật khẩu cũ
            if (md5($input_pass_old) != $user_data['Password']) { // So sánh MD5 (Lưu ý: MD5 không an toàn)
                $thongbao .= "Mật khẩu cũ không chính xác. ";
            }
            // Kiểm tra mật khẩu mới trùng khớp
            if ($input_pass_new !== $input_pass_confirm) {
                $thongbao .= "Mật khẩu mới không trùng khớp. ";
            }
            // Kiểm tra độ dài mật khẩu mới
            if (strlen($input_pass_new) < 8) {
                $thongbao .= 'Mật khẩu mới phải chứa ít nhất 8 ký tự. ';
            }
        }
    }

    // --- Validate định dạng Email ---
    if (!empty($input_email) && !filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
       $thongbao .= "Địa chỉ email không hợp lệ. ";
    }

    // --- Validate số điện thoại (10-11 chữ số) ---
    if (!empty($input_dienthoai) && !preg_match('/^[0-9]{10,11}$/', $input_dienthoai)) {
        $thongbao .= "Số điện thoại không hợp lệ (phải là 10 hoặc 11 chữ số). ";
    }

    // --- Kiểm tra trùng lặp (chỉ kiểm tra nếu không có lỗi nào trước đó) ---
    if (empty($thongbao) && $query) {
        while ($r = mysqli_fetch_assoc($query)) {
            // Bỏ qua việc so sánh với chính user hiện tại
            if ($r['idUser'] == $_SESSION['IDUser']) {
                continue;
            }
            // So sánh không phân biệt hoa thường cho Username và Email
            if (strcasecmp($r['HoTen'], $input_username) == 0) {
                $thongbao .= 'Username này đã được người khác sử dụng. ';
            }
            if (strcasecmp($r['Email'], $input_email) == 0) {
                $thongbao .= 'Email này đã được người khác sử dụng. ';
            }
            if ($r['DienThoai'] == $input_dienthoai) {
                $thongbao .= 'Số điện thoại này đã được người khác sử dụng. ';
            }
        }
        mysqli_free_result($query); // Giải phóng bộ nhớ
    } else if (!$query) {
         $thongbao .= "Lỗi truy vấn CSDL để kiểm tra trùng lặp. ";
    }

    // --- Xử lý upload Avatar (chỉ khi không có lỗi và có file được tải lên) ---
    $avatarPath = $user_data['Avatar']; // Giữ avatar cũ làm mặc định
    if (empty($thongbao) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $avatarTmpName = $_FILES['avatar']['tmp_name'];
        $avatarName = basename($_FILES['avatar']['name']); // Lấy tên file gốc, dùng basename để an toàn
        $avatarSize = $_FILES['avatar']['size'];
        $avatarError = $_FILES['avatar']['error'];

        // Kiểm tra kiểu file thực sự bằng finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $avatarMimeType = finfo_file($finfo, $avatarTmpName);
        finfo_close($finfo);

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if (in_array($avatarMimeType, $allowedTypes)) {
            if ($avatarSize <= $maxFileSize) {
                $avatarExt = pathinfo($avatarName, PATHINFO_EXTENSION);
                // Tạo tên file mới duy nhất để tránh ghi đè và đặt tên chuẩn
                $avatarNewName = "avatar_" . $_SESSION['IDUser'] . "_" . time() . "." . strtolower($avatarExt);
                $avatarDir = "../avatar/"; // Thư mục lưu avatar
                $avatarUploadPath = $avatarDir . $avatarNewName;

                // Tạo thư mục nếu chưa tồn tại
                if (!is_dir($avatarDir)) {
                    mkdir($avatarDir, 0755, true); // Tạo thư mục với quyền phù hợp
                }

                // Xóa avatar cũ nếu tồn tại và khác file mặc định (nếu có)
                $oldAvatarFullPath = $avatarDir . $user_data['Avatar'];
                if (!empty($user_data['Avatar']) && file_exists($oldAvatarFullPath) /* && $user_data['Avatar'] != 'default.png' */) {
                    @unlink($oldAvatarFullPath); // Dùng @ để tránh báo lỗi nếu không xóa được file
                }

                // Di chuyển file đã upload vào thư mục avatar
                if (move_uploaded_file($avatarTmpName, $avatarUploadPath)) {
                    $avatarPath = $avatarNewName; // Cập nhật tên file avatar mới để lưu vào DB
                } else {
                    $thongbao .= "Lỗi khi di chuyển file ảnh đại diện đã tải lên. ";
                }
            } else {
                $thongbao .= "Kích thước ảnh đại diện quá lớn (tối đa 5MB). ";
            }
        } else {
            $thongbao .= "Định dạng ảnh đại diện không hợp lệ (chỉ chấp nhận JPG, PNG, GIF). ";
        }
    } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['avatar']['error'] != UPLOAD_ERR_OK) {
        // Chỉ thông báo lỗi nếu có lỗi thực sự, bỏ qua trường hợp không chọn file (UPLOAD_ERR_NO_FILE)
        $thongbao .= "Có lỗi xảy ra trong quá trình tải ảnh lên. Mã lỗi: " . $_FILES['avatar']['error'];
    }

    // --- Cập nhật CSDL nếu không có lỗi nào xảy ra ---
    if (empty($thongbao)) {
        // Nên dùng Prepared Statements để chống SQL Injection
        $update_username = mysqli_real_escape_string($conn, $input_username);
        $update_hotenk = mysqli_real_escape_string($conn, $input_hotenk);
        $update_diachi = mysqli_real_escape_string($conn, $input_diachi);
        $update_dienthoai = mysqli_real_escape_string($conn, $input_dienthoai);
        $update_email = mysqli_real_escape_string($conn, $input_email);
        $update_avatar = mysqli_real_escape_string($conn, $avatarPath); // Tên file avatar mới (hoặc cũ nếu không đổi)

        // Chuỗi SQL cập nhật mật khẩu (chỉ thêm nếu người dùng muốn đổi và validate thành công)
        $password_update_sql_part = "";
        if ($isChangingPassword && empty($thongbao)) { // Kiểm tra lại $thongbao lần nữa cho chắc
            $hashed_new_password = md5($input_pass_new); // !!! Cảnh báo: MD5 rất yếu, nên dùng password_hash() !!!
            // Lưu ý: Nên dùng password_hash() khi đăng ký và password_verify() khi đăng nhập/kiểm tra MK cũ
            // Ví dụ dùng password_hash(): $hashed_new_password = password_hash($input_pass_new, PASSWORD_DEFAULT);
            $password_update_sql_part = " Password='" . $hashed_new_password . "', ";
        }

        // Câu lệnh SQL UPDATE hoàn chỉnh
        $sqlUpdate = "UPDATE Users SET
                        HoTen='" . $update_username . "',
                        HoTenK='" . $update_hotenk . "'," .
                        $password_update_sql_part . // Thêm phần cập nhật password nếu có
                        " DiaChi='" . $update_diachi . "',
                        DienThoai='" . $update_dienthoai . "',
                        Email='" . $update_email . "',
                        Avatar='" . $update_avatar . "'
                      WHERE idUser=" . $_SESSION['IDUser'];

        $kq1 = mysqli_query($conn, $sqlUpdate);

        if ($kq1) {
            // Cập nhật lại thông tin trong Session nếu cần thiết
            $_SESSION['Username'] = $input_username; // Giả sử session lưu Username là HoTen
            $_SESSION['HoTenK'] = $input_hotenk;
            // Cập nhật avatar trong session nếu có lưu
            // $_SESSION['Avatar'] = $avatarPath;

            // Thông báo thành công và tải lại trang để thấy thay đổi
            echo "<script language='javascript'>alert('Cập nhật thông tin thành công!'); location.href='Sua_TaiKhoan.php';</script>";
            exit; // Dừng thực thi sau khi chuyển hướng bằng JS
        } else {
            // Thông báo lỗi nếu cập nhật thất bại
            $thongbao .= "Lỗi cập nhật cơ sở dữ liệu: " . mysqli_error($conn);
        }
    }

    // Nếu có lỗi (từ validate, upload hoặc update DB), hiển thị thông báo bằng JS alert
    if (!empty($thongbao)) {
        // addslashes để tránh lỗi JS nếu thông báo chứa dấu nháy đơn hoặc kép
        echo "<script language='javascript'>alert('" . addslashes($thongbao) . "');</script>";
    }
}
// --- KẾT THÚC XỬ LÝ LOGIC PHP ---
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include_once("header1.php"); ?> <?php // Chứa các thẻ meta, link CSS chung ?>
    <title>Sửa thông tin tài khoản</title>
    <?php // include_once("header2.php"); ?> <?php // Header2 thường chứa menu, sẽ include trong body ?>
    <link rel="stylesheet" href="../css/bootstrap.min.css"> <?php // Đảm bảo đã link bootstrap ?>
    <style>
        /* --- CSS CHO KHUNG BAO NGOÀI GIỐNG TRANG CHỦ --- */
        body {
            background-color: #f0f0f0;
            padding: 0;
            margin: 0;
            font-family: sans-serif; /* Nên có font cụ thể hơn */
        }
        #main-container {
            max-width: 1200px; /* Hoặc kích thước phù hợp */
            margin: 30px auto;
            border: 1px solid rgb(236, 206, 227);
            background-color: rgb(255, 231, 236); /* Màu hồng nhạt */
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-radius: 8px;
            overflow: hidden;
        }
        /* --- KẾT THÚC CSS KHUNG BAO NGOÀI --- */

        /* --- CSS CHO FORM SỬA TÀI KHOẢN --- */
        .form-container {
            background-color: #ffffff; /* Nền trắng cho form */
            padding: 30px 40px; /* Tăng padding */
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .form-container h2 {
            color: #c85180; /* Màu tiêu đề hồng đậm hơn */
            margin-bottom: 30px;
            font-weight: bold;
        }
        .form-label {
             font-weight: 600; /* Chữ đậm vừa phải */
             color: #555;
             margin-bottom: .5rem; /* Thêm khoảng cách dưới label */
             /* display: block; */ /* Đảm bảo label chiếm 1 dòng nếu không dùng grid */
        }
         /* Canh lề label trong grid */
         .row > .col-form-label {
             text-align: right; /* Canh phải label */
             padding-right: 0; /* Giảm padding phải */
         }
         @media (max-width: 767px) {
             .row > .col-form-label {
                 text-align: left; /* Canh trái label trên mobile */
             }
         }


        .form-control {
             border-radius: 5px;
             border: 1px solid #ced4da; /* Viền input chuẩn của Bootstrap */
             padding: .5rem .75rem; /* Padding chuẩn */
             font-size: 1rem;
             color: #495057;
        }
        .form-control:focus {
            border-color: #b186a3; /* Màu viền hồng khi focus */
            box-shadow: 0 0 0 0.25rem rgba(217, 171, 197, 0.25); /* Bóng mờ hồng khi focus */
        }
         .form-control[readonly] {
             background-color: #e9ecef; /* Nền xám cho input readonly */
             opacity: 1;
         }

        .current-avatar {
            max-width: 100px; /* Kích thước avatar hiển thị */
            height: auto;
            border-radius: 50%; /* Bo tròn avatar */
            border: 2px solid #eee;
            display: block; /* Để margin-bottom hoạt động */
            object-fit: cover; /* Đảm bảo ảnh không bị méo */
        }

        .form-text { /* Style cho text ghi chú nhỏ */
             font-size: 0.875em;
             color: #6c757d;
        }
        .btn-primary {
             background-color: #d9abc5; /* Màu nút hồng */
             border-color: #d09db7;
        }
         .btn-primary:hover {
             background-color: #c889ac;
             border-color: #c07da1;
         }
         .btn-secondary {
             background-color: #aaa;
             border-color: #999;
         }

    </style>
</head>
<body>

<?php // <<< KHUNG BAO NGOÀI >>> ?>
<div id="main-container">

    <?php include_once("header2.php"); // Menu chính ?>

    <?php // Container chứa form, có khoảng cách dưới lớn với footer ?>
    <div class="container mt-4 mb-5" style="margin-bottom: 6rem !important;">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-10"> <?php // Cột giới hạn chiều rộng form ?>

                <div class="form-container p-4 shadow-sm"> <?php // Khung trắng chứa form ?>
                    <h2 class="text-center mb-4">SỬA THÔNG TIN TÀI KHOẢN</h2>

                    <?php /*
                    // Phần hiển thị lỗi tập trung (nếu không muốn dùng alert JS)
                    if (!empty($thongbao)) {
                        echo '<div class="alert alert-danger">' . htmlspecialchars($thongbao) . '</div>';
                    }
                    // Có thể thêm thông báo thành công ở đây nếu không chuyển hướng
                    */
                    ?>

                    <form action="Sua_TaiKhoan.php" method="post" enctype="multipart/form-data">
                        <?php if (isset($user_data)): ?>
                            <?php // --- Phần Avatar --- ?>
                            <div class="row mb-4 align-items-center">
                                <label class="col-sm-3 col-form-label text-md-end">Ảnh đại diện:</label>
                                <div class="col-sm-9">
                                    <?php
                                    $avatar_display_path = '../images/default-avatar.png'; // Đường dẫn ảnh mặc định
                                    if (!empty($user_data['Avatar']) && file_exists("../avatar/" . $user_data['Avatar'])) {
                                        $avatar_display_path = "../avatar/" . htmlspecialchars($user_data['Avatar']);
                                    }
                                    ?>
                                    <img src="<?php echo $avatar_display_path; ?>?t=<?php echo time(); // Thêm tham số để tránh cache trình duyệt ?>" alt="Ảnh đại diện hiện tại" class="current-avatar mb-2">

                                    <input type="file" name="avatar" id="avatar" class="form-control form-control-sm" accept="image/jpeg, image/png, image/gif">
                                    <div class="form-text">Chọn ảnh mới nếu bạn muốn thay đổi (JPG, PNG, GIF, tối đa 5MB).</div>
                                </div>
                            </div>

                            <?php // --- Thông tin cơ bản --- ?>
                            <div class="row mb-3">
                                <label for="Username" class="col-sm-3 col-form-label text-md-end">Username:</label>
                                <div class="col-sm-9">
                                    <?php // Sử dụng giá trị từ CSDL làm giá trị mặc định ?>
                                    <input type="text" class="form-control" id="Username" name="Username" value="<?php echo htmlspecialchars($user_data['HoTen']); ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="HoTenK" class="col-sm-3 col-form-label text-md-end">Họ và Tên:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="HoTenK" name="HoTenK" value="<?php echo htmlspecialchars($user_data['HoTenK']); ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="Email" class="col-sm-3 col-form-label text-md-end">Email:</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" id="Email" name="Email" value="<?php echo htmlspecialchars($user_data['Email']); ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="DienThoai" class="col-sm-3 col-form-label text-md-end">Điện thoại:</label>
                                <div class="col-sm-9">
                                    <input type="tel" class="form-control" id="DienThoai" name="DienThoai" value="<?php echo htmlspecialchars($user_data['DienThoai']); ?>" required pattern="[0-9]{10,11}" title="Nhập 10 hoặc 11 chữ số">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="DiaChi" class="col-sm-3 col-form-label text-md-end">Địa chỉ:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="DiaChi" name="DiaChi" value="<?php echo htmlspecialchars($user_data['DiaChi']); ?>">
                                </div>
                            </div>

                            <hr class="my-4"> <?php // Đường kẻ ngang phân cách ?>

                            <?php // --- Phần đổi mật khẩu --- ?>
                            <p class="text-center text-muted mb-3"><em>Để trống các trường mật khẩu nếu bạn không muốn thay đổi.</em></p>
                            <div class="row mb-3">
                                <label for="Password_old" class="col-sm-3 col-form-label text-md-end">Mật khẩu cũ:</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control password-field" id="Password_old" name="Password_old" placeholder="Nhập mật khẩu hiện tại nếu muốn đổi">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="Password" class="col-sm-3 col-form-label text-md-end">Mật khẩu mới:</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control password-field" id="Password" name="Password" placeholder="Ít nhất 8 ký tự">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="Password_1" class="col-sm-3 col-form-label text-md-end">Nhập lại MK mới:</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control password-field" id="Password_1" name="Password_1" placeholder="Nhập lại mật khẩu mới">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showPasswordCheck" onclick="togglePasswordVisibility()">
                                        <label class="form-check-label" for="showPasswordCheck">Hiển thị mật khẩu</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <?php // --- Thông tin khác (chỉ hiển thị) --- ?>
                            <div class="row mb-3">
                                <label for="NgayDangKy" class="col-sm-3 col-form-label text-md-end">Ngày đăng ký:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="NgayDangKy" name="NgayDangKy" value="<?php echo htmlspecialchars($user_data['NgayDangKy']); ?>" readonly>
                                </div>
                            </div>

                            <?php // --- Nút bấm --- ?>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="submit" name="Sua" class="btn btn-primary px-4">Lưu thay đổi</button>
                                <a href="../site/index.php" class="btn btn-secondary px-4">Thoát</a> <?php // Hoặc quay lại trang trước đó ?>
                            </div>

                        <?php else: ?>
                            <div class="alert alert-danger text-center">Không thể tải thông tin người dùng để sửa.</div>
                        <?php endif; ?>
                    </form>
                </div> <?php // Kết thúc .form-container ?>

            </div> <?php // Kết thúc .col-lg-9 ?>
        </div> <?php // Kết thúc .row.justify-content-center ?>
    </div> <?php // Kết thúc .container ?>

    <?php include_once("footer.php"); // Footer ?>

</div> <?php // <<< Đóng thẻ #main-container >>> ?>

<?php // ----- JavaScript ----- ?>
<script src="../js/jquery-3.1.1.min.js"></script> <?php // Đảm bảo jQuery được load nếu Bootstrap JS cần ?>
<script src="../js/bootstrap.min.js"></script> <?php // Load Bootstrap JS (nếu cần cho các component khác) ?>

<script type="text/javascript">
    function togglePasswordVisibility() {
        // Lấy tất cả các trường input có class 'password-field'
        const passwordFields = document.querySelectorAll('.password-field');
        // Lấy checkbox
        const checkbox = document.getElementById('showPasswordCheck');

        // Lặp qua từng trường và thay đổi type
        passwordFields.forEach(field => {
            if (checkbox.checked) {
                field.type = "text";
            } else {
                field.type = "password";
            }
        });
    }

     // Có thể thêm các script khác ở đây nếu cần, ví dụ: xem trước ảnh đại diện khi chọn file
     /*
     const avatarInput = document.getElementById('avatar');
     const avatarPreview = document.querySelector('.current-avatar');
     if (avatarInput && avatarPreview) {
         avatarInput.onchange = evt => {
             const [file] = avatarInput.files;
             if (file) {
                 avatarPreview.src = URL.createObjectURL(file);
             }
         }
     }
     */
</script>

</body>
</html>

<?php
ob_end_flush(); // Gửi bộ đệm đầu ra và tắt bộ đệm
?>