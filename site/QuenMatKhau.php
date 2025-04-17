<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
</head>
<body>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Xử lý yêu cầu khi người dùng gửi form
        $email = $_POST["email"];

        // Kiểm tra xem email có tồn tại trong cơ sở dữ liệu hay không
        $user_exists = kiem_tra_ton_tai_nguoi_dung($email);

        if ($user_exists) {
            // Tạo mật khẩu mới và lưu vào cơ sở dữ liệu
            $new_password = tao_mat_khau_moi();
            luu_mat_khau_moi($email, $new_password);

            echo "Mật khẩu mới của bạn là: $new_password";
        } else {
            echo "Email không tồn tại trong hệ thống.";
        }
    }
    ?>

    <h2>Quên Mật Khẩu</h2>
    <form method="post" action="">
        Email: <input type="text" name="email" required>
        <input type="submit" value="Gửi yêu cầu">
    </form>
</body>
</html>

<?php
function kiem_tra_ton_tai_nguoi_dung($email) {
    
    $conn = mysqli_connect("localhost", "root", "", "cuahangmypham", 3306);

    if (!$conn) {
        die("Không thể kết nối đến cơ sở dữ liệu: " . mysqli_connect_error());
    }
    $email = mysqli_real_escape_string($conn, $email);

    $query = "SELECT COUNT(*) AS count FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Lỗi truy vấn: " . mysqli_error($conn));
    }

    $row = mysqli_fetch_assoc($result);
    $count = $row['count'];

    mysqli_close($conn);

    return $count > 0;
}

function tao_mat_khau_moi() {
    return rand(10000000, 99999999);
}

function luu_mat_khau_moi($email, $new_password) {
    $conn = mysqli_connect("localhost", "root", "", "cuahangmypham", 3306);

    if (!$conn) {
        die("Không thể kết nối đến cơ sở dữ liệu: " . mysqli_connect_error());
    }

    $email = mysqli_real_escape_string($conn, $email);
    
    $hashed_password = md5($new_password);

    $query = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Lỗi truy vấn: " . mysqli_error($conn));
    }

    mysqli_close($conn);
}
?>
