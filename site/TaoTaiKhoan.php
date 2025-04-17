<?php
// ** BẮT BUỘC CÓ session_start() Ở ĐẦU **
if (!isset($_SESSION)) {
    session_start();
    ob_start(); // Giữ lại ob_start như code gốc của bạn
}

// --- Bao gồm file kết nối CSDL ---
include_once('../connection/connect_database.php');

// --- Khởi tạo biến thông báo lỗi ---
$error_message = null;

// --- Xử lý logic khi form được gửi đi (GIỮ NGUYÊN LOGIC GỐC) ---
if (isset($_POST['TaoTK'])) {
    // ... (TOÀN BỘ LOGIC PHP XỬ LÝ FORM GIỮ NGUYÊN NHƯ CODE BẠN CUNG CẤP) ...
    if (isset($_POST['Username'], $_POST['Password'], $_POST['Password1'], $_POST['HoTenK'], $_POST['DiaChi'], $_POST['DienThoai']) &&
        $_POST['Username'] !== "" && $_POST['Password'] !== "" && $_POST['Password1'] !== "" && $_POST['HoTenK'] !== "" && $_POST['DiaChi'] !== "" && $_POST['DienThoai'] !== "") {

        $username   = $_POST['Username'];
        $hoTenK     = $_POST['HoTenK'];
        $password   = $_POST['Password'];
        $password_confirm = $_POST['Password1'];
        $diaChi     = $_POST['DiaChi'];
        $dienThoai  = $_POST['DienThoai'];
        $email      = isset($_POST['Email']) ? $_POST['Email'] : "";
        $ngayDangKy = date("Y-m-d H:i:s");

        $thongbao = "";

        // Kiểm tra trùng lặp
        $sql_check = "SELECT HoTen, Email, DienThoai FROM users";
        $query_check = mysqli_query($conn, $sql_check);
        if ($query_check) {
            while ($r = $query_check->fetch_assoc()) {
                if (strcasecmp($r['HoTen'], $username) == 0) { $thongbao .= 'Tên đăng nhập đã tồn tại. '; }
                if ($email !== "" && !empty($r['Email']) && strcasecmp($r['Email'], $email) == 0) { $thongbao .= 'Email đã tồn tại. '; }
                if ($r['DienThoai'] == $dienThoai) { $thongbao .= 'Số điện thoại đã tồn tại. '; }
            }
             mysqli_free_result($query_check);
        } else { $thongbao .= 'Lỗi khi kiểm tra dữ liệu người dùng. '; }

        // Kiểm tra mật khẩu
        if (md5($password) !== md5($password_confirm)) { $thongbao .= 'Mật khẩu không trùng khớp. '; }
        if (strlen($password) < 8 || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) { $thongbao .= 'Mật khẩu phải có ít nhất 8 ký tự và chứa ít nhất một ký tự đặc biệt. '; }
        // Validate Email & Phone
         if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $thongbao .= "Địa chỉ email không hợp lệ. "; }
         if (!preg_match('/^[0-9]{10,11}$/', $dienThoai)) { $thongbao .= "Số điện thoại không hợp lệ. "; }


        if (trim($thongbao) !== "") {
            $error_message = trim($thongbao);
        } else {
            // INSERT dữ liệu
            $username_clean   = mysqli_real_escape_string($conn, strip_tags($username));
            $hoTenK_clean     = mysqli_real_escape_string($conn, strip_tags($hoTenK));
            $password_hashed  = md5($password); // Giữ nguyên MD5 như code gốc
            $diaChi_clean     = mysqli_real_escape_string($conn, strip_tags($diaChi));
            $dienThoai_clean  = mysqli_real_escape_string($conn, strip_tags($dienThoai));
            $email_clean      = mysqli_real_escape_string($conn, strip_tags($email));

            $sl = "INSERT INTO users(HoTen, HoTenK, Password, DiaChi, DienThoai, Email, NgayDangKy, idGroup) VALUES ('" . $username_clean . "','" . $hoTenK_clean . "','" . $password_hashed . "','" . $diaChi_clean . "','" . $dienThoai_clean . "','" . $email_clean . "','" . $ngayDangKy . "', 2)";
            $kq = mysqli_query($conn, $sl);

            if ($kq) {
                $new_user_id = mysqli_insert_id($conn);
                $_SESSION['Username'] = $username;
                $_SESSION['HoTenK'] = $hoTenK;
                $_SESSION['IDUser'] = $new_user_id;
                $_SESSION['SDT'] = $dienThoai;
                $_SESSION['DC'] = $diaChi;
                $_SESSION['Email'] = $email;

                ob_end_clean(); // Giữ nguyên ob_end_clean
                echo "<script language='javascript'>alert('Tạo tài khoản thành công!'); location.href='../site/index.php?index=1';</script>";
                exit();
            } else {
                error_log("SQL Insert Error: " . mysqli_error($conn));
                $error_message = "Đã xảy ra lỗi trong quá trình tạo tài khoản. Vui lòng thử lại.";
            }
        }
    } else {
        if(isset($_POST['TaoTK'])){
             $error_message = "Vui lòng nhập đầy đủ các trường thông tin bắt buộc (*).";
        }
    }
}
// --- KẾT THÚC XỬ LÝ LOGIC PHP ---
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once ("header1.php"); // Chứa meta, link CSS chung ?>
    <title>Tạo Tài Khoản</title>
    <?php // include_once ("header2.php"); // Chuyển xuống body ?>
    <link rel="stylesheet" href="../css/bootstrap.min.css">

    <style>
        /* --- CSS CHO KHUNG BAO NGOÀI (ĐÃ MỞ RỘNG, GIỮ NGUYÊN MÀU) --- */
        body {
            background-color: #f0f0f0; /* Giữ nguyên màu nền body */
            padding: 0;
            margin: 0;
            font-family: sans-serif; /* Giữ nguyên font */
        }
        #main-container {
            max-width: 1140px;  /* <<< CHỈ THAY ĐỔI DÒNG NÀY */
            margin: 30px auto; /* Giữ nguyên margin */
            border: 1px solid rgb(236, 206, 227); /* Giữ nguyên border */
            background-color: rgb(255, 231, 236); /* Giữ nguyên màu nền khung hồng */
            padding: 25px; /* Giữ nguyên padding */
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); /* Giữ nguyên shadow */
            border-radius: 8px; /* Giữ nguyên bo góc */
            overflow: hidden; /* Giữ nguyên overflow */
        }
        /* --- KẾT THÚC CSS KHUNG BAO NGOÀI --- */

        /* --- CSS CHO KHUNG FORM TRẮNG BÊN TRONG (GIỮ NGUYÊN) --- */
        .form-wrapper {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-bottom: 30px; /* Giữ nguyên margin-bottom */
        }
        .form-wrapper h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            color: #b15e83; /* Giữ nguyên màu tiêu đề */
            font-size: 1.8rem;
        }
         /* --- KẾT THÚC CSS KHUNG FORM --- */

        /* --- CSS cho các element bên trong form (giữ nguyên) --- */
        .form-label { font-weight: 600; color: #444; margin-bottom: .5rem; }
        .required-mark { color: red; margin-left: 3px; }
        .form-control { border-radius: 5px; border: 1px solid #ced4da; padding: .6rem .9rem; font-size: 1rem; color: #495057; }
        .form-control:focus { border-color: #e3a5c7; box-shadow: 0 0 0 0.25rem rgba(217, 171, 197, 0.25); }
        .form-control[readonly] { background-color: #e9ecef; opacity: 1; cursor: not-allowed; }
        .form-check-label { font-weight: normal; color: #555; }
        .button-container { text-align: center; margin-top: 30px; }
        .button-container .btn { padding: 10px 30px; font-size: 1.1rem; margin: 0 10px; }
        .btn-primary { background-color: #c85180; border-color: #b84170; } /* Giữ nguyên màu nút */
        .btn-primary:hover { background-color: #b84170; border-color: #a83160; }
        .btn-secondary { background-color: #6c757d; border-color: #6c757d; color: #fff; }
        .btn-secondary:hover { background-color: #5a6268; border-color: #545b62; color: #fff; }

         /* CSS cho thông báo lỗi (thêm nếu chưa có hoặc giữ nguyên nếu có) */
         .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
            text-align: center; /* Thêm để căn giữa text lỗi */
        }
    </style>
</head>
<body>

<?php // <<< KHUNG BAO NGOÀI >>> ?>
<div id="main-container">

    <?php include_once ("header2.php"); // Menu chính bên trong khung ?>

    <div class="row justify-content-center"> <?php // Row để căn giữa cột chứa form ?>
        <div class="col-12"> <?php // Cột chiếm toàn bộ chiều rộng của #main-container ?>

            <div class="form-wrapper bg-white p-4 p-md-5 border rounded shadow-sm"> <?php // Khung trắng chứa form ?>
                <h2>TẠO TÀI KHOẢN MỚI</h2>

                <?php
                // Hiển thị thông báo lỗi (nếu có)
                if (!empty($error_message)) {
                    // Sử dụng class alert-danger đã định nghĩa ở trên
                    echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); // Gửi về chính trang này ?>" method="post" id="registerForm">
                    <div class="mb-3">
                        <label for="Username" class="form-label">Tên đăng nhập<span class="required-mark">*</span></label>
                        <input type="text" class="form-control" name="Username" id="Username" placeholder="Tên bạn dùng để đăng nhập" required
                               value="<?php echo isset($_POST['Username']) ? htmlspecialchars($_POST['Username']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="HoTenK" class="form-label">Họ và tên<span class="required-mark">*</span></label>
                        <input type="text" class="form-control" name="HoTenK" id="HoTenK" placeholder="Họ tên đầy đủ của bạn" required
                               value="<?php echo isset($_POST['HoTenK']) ? htmlspecialchars($_POST['HoTenK']) : ''; ?>">
                    </div>

                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="Password" class="form-label">Mật khẩu<span class="required-mark">*</span></label>
                            <input type="password" class="form-control" name="Password" id="Password" placeholder="Ít nhất 8 ký tự, có ký tự đặc biệt" required>
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="Password1" class="form-label">Xác nhận mật khẩu<span class="required-mark">*</span></label>
                            <input type="password" class="form-control" name="Password1" id="Password1" placeholder="Nhập lại mật khẩu" required>
                        </div>
                    </div>

                     <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="showPasswordCheckbox" onclick="togglePassword()">
                        <label class="form-check-label" for="showPasswordCheckbox">Hiển thị mật khẩu</label>
                     </div>

                    <div class="mb-3">
                        <label for="DiaChi" class="form-label">Địa chỉ<span class="required-mark">*</span></label>
                        <input type="text" class="form-control" name="DiaChi" id="DiaChi" placeholder="Địa chỉ nhận hàng của bạn" required
                               value="<?php echo isset($_POST['DiaChi']) ? htmlspecialchars($_POST['DiaChi']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="DienThoai" class="form-label">Điện thoại<span class="required-mark">*</span></label>
                        <input type="tel" class="form-control" name="DienThoai" id="DienThoai" placeholder="Số điện thoại liên lạc" required pattern="[0-9]{10,11}" title="Nhập số điện thoại hợp lệ (10-11 số)"
                               value="<?php echo isset($_POST['DienThoai']) ? htmlspecialchars($_POST['DienThoai']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="Email" class="form-label">Email:</label>
                        <input type="email" class="form-control" name="Email" id="Email" placeholder="Địa chỉ email (không bắt buộc)"
                               value="<?php echo isset($_POST['Email']) ? htmlspecialchars($_POST['Email']) : ''; ?>">
                    </div>

                    <input type="hidden" name="NgayDangKy" value="<?php echo date("Y-m-d H:i:s"); ?>">

                     <div class="button-container">
                        <button type="submit" id="TaoTK" name="TaoTK" class="btn btn-primary btn-lg">Tạo tài khoản</button>
                        <a href="../site/index.php?index=1" class="btn btn-secondary btn-lg">Thoát</a>
                    </div>
                </form>
            </div> <?php // end .form-wrapper ?>
        </div> <?php // end .col ?>
    </div> <?php // end .row ?>

    <?php include_once ("footer.php"); // Footer bên trong khung ?>

</div> <?php // <<< Đóng thẻ #main-container >>> ?>

<?php // ----- JavaScript ----- ?>
<script src="../js/jquery-3.1.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script>
    function togglePassword() {
        var passwordInput = document.getElementById("Password");
        var confirmPasswordInput = document.getElementById("Password1");
        var checkbox = document.getElementById("showPasswordCheckbox");
        var newType = checkbox.checked ? "text" : "password";
        if (passwordInput) { passwordInput.type = newType; }
        if (confirmPasswordInput) { confirmPasswordInput.type = newType; }
    }
</script>

</body>
</html>
<?php
// Gửi bộ đệm đầu ra nếu có
if (ob_get_level() > 0 && !headers_sent()) { // Thêm kiểm tra headers_sent() cho an toàn
    ob_end_flush();
}
?>