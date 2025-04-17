<?php
// ** BẮT BUỘC CÓ session_start() Ở ĐẦU **
if (!isset($_SESSION)) {
    session_start();
    ob_start();
}

// --- Bao gồm file kết nối CSDL ---
include_once('../connection/connect_database.php');

// --- Khởi tạo biến lỗi ---
$error_message = null;

// --- Xử lý logic đăng nhập khi form được gửi đi (GIỮ NGUYÊN LOGIC GỐC) ---
if (isset($_POST['Submit'])) {
    // ... (Toàn bộ logic PHP xử lý đăng nhập giữ nguyên) ...
    if (isset($_POST['Username']) && isset($_POST['Password'])) {
        $username = $_POST['Username'];
        $raw_password = $_POST['Password'];

        if (empty(trim($username)) || empty(trim($raw_password))) {
            $error_message = "Tên đăng nhập và mật khẩu không được để trống!";
        } else {
            $username = strip_tags($username);
            $username = addslashes($username);
            $password_md5 = md5($raw_password); // !!! MD5 không an toàn !!!
            $password_md5 = strip_tags($password_md5);
            $password_md5 = addslashes($password_md5);

            // !!! NÊN DÙNG PREPARED STATEMENTS !!!
            $sql = "SELECT idUser, HoTenK, DienThoai, DiaChi, Email FROM Users WHERE HoTen='" . $username . "' AND Password='" . $password_md5 . "'";
            $query = mysqli_query($conn, $sql);

            if ($query) {
                $num_rows = mysqli_num_rows($query);
                if ($num_rows > 0) {
                    $r_us = mysqli_fetch_array($query);
                    $_SESSION['Username'] = $username;
                    $_SESSION['HoTenK'] = $r_us['HoTenK'];
                    $_SESSION['IDUser'] = $r_us['idUser'];
                    $_SESSION['SDT'] = $r_us['DienThoai'];
                    $_SESSION['DC'] = $r_us['DiaChi'];
                    $_SESSION['Email'] = $r_us['Email'];

                    $welcome_message = "Đăng nhập thành công! Xin chào " . $r_us['HoTenK'];
                    $welcome_message_js = addslashes($welcome_message);

                    ob_end_clean();

                    if ($_SESSION['IDUser'] == 1) { // Admin
                        echo "<script language='javascript'>alert('" . $welcome_message_js . "');";
                        echo "location.href='../admin/index.php';</script>";
                    } else { // User thường
                        echo "<script language='javascript'>alert('" . $welcome_message_js . "');";
                        if (isset($_SESSION['redirect_after_login'])) {
                            $redirect_url = $_SESSION['redirect_after_login'];
                            unset($_SESSION['redirect_after_login']);
                            echo "location.href='" . $redirect_url . "';</script>";
                        } elseif (isset($_SESSION['cart'])) {
                            echo "location.href='GioHang.php';</script>";
                        } else {
                            echo "location.href='index.php';</script>";
                        }
                    }
                    exit();
                } else {
                    $error_message = "Tên đăng nhập hoặc mật khẩu không đúng!";
                }
                mysqli_free_result($query);
            } else {
                error_log("Lỗi truy vấn SQL: " . mysqli_error($conn));
                $error_message = "Có lỗi xảy ra trong quá trình đăng nhập. Vui lòng thử lại.";
            }
        }
    } else if (isset($_POST['Submit'])) {
         $error_message = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
    }
}
// --- Kết thúc xử lý logic đăng nhập ---
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); // Meta, CSS chung ?>
    <title>Đăng Nhập</title>
    <?php // include_once("header2.php"); // Chuyển xuống body ?>
    <link rel="stylesheet" href="../css/bootstrap.min.css">

    <style>
        /* --- CSS CHO KHUNG BAO NGOÀI --- */
        body {
            background-color: #f0f0f0;
            padding: 0;
            margin: 0;
            font-family: sans-serif;
        }
        #main-container {
            max-width: 1200px; /* Giữ chiều rộng khung hồng lớn để menu không bị vỡ */
            margin: 30px auto;
            border: 1px solid rgb(236, 206, 227);
            background-color: rgb(255, 231, 236);
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-radius: 8px;
            overflow: hidden;
        }
        /* --- KẾT THÚC CSS KHUNG BAO NGOÀI --- */

        /* --- CSS CHO KHUNG FORM ĐĂNG NHẬP --- */
        .login-form-wrapper {
            background-color: #ffffff;
            padding: 35px 45px;
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .login-form-wrapper h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            color: #b15e83;
            font-size: 1.8rem;
        }
        /* --- KẾT THÚC CSS KHUNG FORM --- */

        /* --- CSS cho các element bên trong form --- */
        .form-label { font-weight: 600; color: #444; margin-bottom: .5rem; }
        .form-control { border-radius: 5px; border: 1px solid #ced4da; padding: .6rem .9rem; font-size: 1rem; color: #495057; background-color: #fff; }
        .form-control:focus { border-color: #e3a5c7; box-shadow: 0 0 0 0.25rem rgba(217, 171, 197, 0.25); }
        .form-check-label { font-weight: normal; color: #555; }
        .button-container { text-align: center; margin-top: 25px; }
        .button-container .btn { padding: 10px 35px; font-size: 1.1rem; margin: 0 8px; }
        .btn-success { background-color: #8cbf8c; border-color: #7aae7a; }
        .btn-success:hover { background-color: #6f9a6f; border-color: #638863; }
        .btn-secondary { background-color: #aaa; border-color: #999; color: #fff; }
        .btn-secondary:hover { background-color: #888; border-color: #777; color: #fff; }
        .forgot-password-link { text-align: center; margin-top: 20px; }
        .forgot-password-link a { color: #b15e83; text-decoration: none; }
        .forgot-password-link a:hover { color: #8a4a69; text-decoration: underline; }
        .alert { margin-bottom: 20px; }

    </style>
    </head>
<body>

<?php // <<< KHUNG BAO NGOÀI >>> ?>
<div id="main-container">

    <?php include_once("header2.php"); // Menu chính bên trong khung ?>

    <div class="container py-5"> <?php // Container Bootstrap ?>
        <div class="row justify-content-center">
             <?php // <<< THAY ĐỔI: Tăng chiều rộng cột chứa form >>> ?>
             <div class="col-md-8 col-lg-7"> <?php // Trước là col-md-7 col-lg-6 ?>

                <div class="login-form-wrapper"> <?php // Khung trắng chứa form ?>
                    <h2>ĐĂNG NHẬP</h2>

                    <?php
                    // Hiển thị thông báo lỗi (nếu có)
                    if (!empty($error_message)) {
                        echo '<div class="alert alert-danger text-center" role="alert">' . htmlspecialchars($error_message) . '</div>';
                    }
                    ?>

                    <form method="post" action="DangNhap.php">
                        <div class="mb-3">
                            <label for="Username" class="form-label">Tên đăng nhập:</label>
                            <input type="text" class="form-control" name="Username" id="Username"
                                   placeholder="Nhập tên đăng nhập của bạn" required autofocus
                                   value="<?php echo isset($_POST['Username']) ? htmlspecialchars($_POST['Username'], ENT_QUOTES) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="Password" class="form-label">Mật khẩu:</label>
                            <input type="password" class="form-control" name="Password" id="Password"
                                   placeholder="Nhập mật khẩu của bạn" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="showPasswordCheckbox" onclick="togglePassword()">
                            <label class="form-check-label" for="showPasswordCheckbox">Hiển thị mật khẩu</label>
                        </div>

                        <div class="button-container d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <button type="submit" id="Submit" name="Submit" class="btn btn-success btn-lg px-4">Đăng nhập</button>
                            <a href="../site/index.php" class="btn btn-secondary btn-lg px-4">Thoát</a>
                        </div>

                        <div class="forgot-password-link">
                            <a href="QuenMatKhau.php">Quên mật khẩu?</a>
                        </div>
                    </form>
                </div> <?php // end .login-form-wrapper ?>
            </div> <?php // <<< Đóng cột mới >>> ?>
        </div> <?php // end .row ?>
    </div> <?php // end .container ?>

    <?php include_once("footer.php"); // Footer bên trong khung ?>

</div> <?php // <<< Đóng thẻ #main-container >>> ?>

<?php // ----- JavaScript ----- ?>
<script src="../js/jquery-3.1.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script>
    function togglePassword() {
        var passwordInput = document.getElementById("Password");
         if (passwordInput) {
             var checkbox = document.getElementById("showPasswordCheckbox");
             passwordInput.type = checkbox.checked ? "text" : "password";
         }
    }
</script>
</body>
</html>
<?php
// Gửi bộ đệm đầu ra nếu có
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>