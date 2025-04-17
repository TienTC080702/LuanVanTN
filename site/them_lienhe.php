<?php
// 1. BẮT BUỘC: Bắt đầu session NGAY LẬP TỨC ở đầu file
session_start();

// 2. Include file kết nối cơ sở dữ liệu
include_once ('../connection/connect_database.php'); // Đường dẫn tương đối đến file kết nối

// 3. Khởi tạo biến thông báo
$message = '';

// 4. Xử lý dữ liệu khi form được gửi đi (phương thức POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Them'])) {

    // Lấy dữ liệu từ form an toàn bằng Null Coalescing Operator
    $hoTen = trim($_POST['HoTen'] ?? '');
    $noiDung = trim($_POST['noidung'] ?? ''); // CKEditor có thể gửi HTML, cân nhắc làm sạch nếu cần hiển thị lại an toàn
    $dienThoai = trim($_POST['DienThoai'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    // Lấy ngày gửi từ input ẩn hoặc dùng ngày hiện tại
    $ngayGui = $_POST['NgayGui'] ?? date("Y-m-d");

    // 5. Kiểm tra các trường bắt buộc
    if (!empty($hoTen) && !empty($noiDung) && !empty($email)) {

        // *** Sử dụng Prepared Statements để chống SQL Injection ***
        $sql = "INSERT INTO gopy_lienhe (HoTen, noidung, DienThoai, Email, NgayGui) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        // Kiểm tra xem prepare có thành công không
        if ($stmt) {
            // 'sssss' nghĩa là 5 biến đều là kiểu string (chuỗi)
            mysqli_stmt_bind_param($stmt, "sssss", $hoTen, $noiDung, $dienThoai, $email, $ngayGui);

            // Thực thi câu lệnh đã chuẩn bị
            if (mysqli_stmt_execute($stmt)) {
                // Gửi thành công: Hiển thị thông báo và chuyển hướng bằng JavaScript
                // Lưu ý: Chuyển hướng JS vẫn hoạt động sau khi session_start() đã được gọi đúng cách
                echo "<script language='javascript'>
                        alert('Gửi liên hệ thành công!');
                        window.location.href='../site/index.php?index=1'; // Chuyển hướng về trang chủ hoặc trang mong muốn
                      </script>";
                // Dừng script ngay sau khi xuất mã JavaScript chuyển hướng
                exit();
            } else {
                // Lỗi khi thực thi câu lệnh
                $message = "<div class='alert alert-danger'>Lỗi khi gửi liên hệ: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</div>";
                // Ghi log lỗi chi tiết cho quản trị viên (không hiển thị cho người dùng)
                // error_log("Lỗi thực thi SQL gopy_lienhe: " . mysqli_stmt_error($stmt));
            }
            // Đóng statement sau khi dùng xong
            mysqli_stmt_close($stmt);
        } else {
            // Lỗi khi chuẩn bị câu lệnh (thường do lỗi cú pháp SQL hoặc kết nối DB)
            $message = "<div class='alert alert-danger'>Lỗi hệ thống khi chuẩn bị dữ liệu. Vui lòng thử lại sau.</div>";
            // Ghi log lỗi chi tiết cho quản trị viên
            // error_log("Lỗi chuẩn bị SQL gopy_lienhe: " . mysqli_error($conn));
        }
    } else {
        // Thiếu thông tin bắt buộc
        $message = "<div class='alert alert-warning'>Vui lòng điền đầy đủ các trường bắt buộc (Họ tên, Nội dung, Email).</div>";
    }

    // Không nên đóng kết nối ở đây nếu HTML bên dưới cần dùng nó (ví dụ: hiển thị dữ liệu khác)
    // mysqli_close($conn); // Sẽ đóng ở cuối file

} // Kết thúc kiểm tra POST

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once ("header.php"); // Include file header chung (nếu có) ?>
    <title>Liên hệ - TIẾN TC BEAUTY STORE</title>
    <?php include_once ("header1.php"); // Include file header khác (nếu có) ?>

    <link rel="stylesheet" href="../css/hoa.min.css" type="text/css">
    <link rel="stylesheet" href="../css/layout.min.css" type="text/css">

    <style>
        /* --- CSS Cho Khung Bao Ngoài --- */
        .page-wrapper {
            max-width: 1200px; /* Chiều rộng tối đa của khung lớn */
            margin: 20px auto; /* Căn giữa và tạo khoảng cách trên dưới */
            padding: 25px;     /* Khoảng đệm bên trong khung */
            border: 1px solid #ddd; /* Đường viền */
            background-color: #ffffff; /* Nền trắng */
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); /* Bóng đổ */
            border-radius: 8px; /* Bo góc */
        }

        /* --- CSS Cho Form Container --- */
        .form-container {
            max-width: 850px; /* Giới hạn chiều rộng form, nhỏ hơn page-wrapper */
            margin: 0 auto 30px auto; /* Căn giữa, bỏ margin top, thêm margin bottom */
            padding: 30px;
            background-color: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 8px;
            /* box-shadow: 0 2px 5px rgba(0,0,0,0.1); */ /* Có thể bỏ nếu page-wrapper đã có shadow */
        }
        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-weight: bold;
        }

        /* --- CSS Cho CKEditor và Các Phần tử Form --- */
        .form-group {
            margin-bottom: 1.2rem; /* Tăng khoảng cách giữa các dòng input */
        }
         /* Đảm bảo label thẳng hàng và rõ ràng */
        .col-form-label {
            text-align: right;
            font-weight: 600; /* Đậm hơn một chút */
        }
        /* Responsive cho label trên màn hình nhỏ */
        @media (max-width: 767px) {
             .col-form-label {
                 text-align: left;
                 margin-bottom: 5px;
             }
             .col-md-9.offset-md-3 {
                 margin-left: 0; /* Bỏ offset trên mobile */
            }
             .button-group {
                 text-align: center; /* Căn giữa nút trên mobile */
             }
        }

        #cke_noidung { /* Áp dụng cho container của CKEditor */
             margin-bottom: 15px;
        }
        .button-group {
             text-align: center; /* Căn giữa các button */
             margin-top: 25px;
        }
        .button-group input[type="submit"],
        .button-group input[type="reset"] {
            margin-left: 10px;
            margin-right: 10px;
            padding: 10px 25px; /* Tăng kích thước nút */
            min-width: 120px; /* Đặt chiều rộng tối thiểu */
        }

        /* --- CSS Cho Thông Báo (Alerts) --- */
        .alert {
             padding: 15px;
             margin-bottom: 20px;
             border: 1px solid transparent;
             border-radius: 4px;
             font-size: 0.95em;
        }
        .alert-success {
             color: #155724;
             background-color: #d4edda;
             border-color: #c3e6cb;
        }
        .alert-danger {
             color: #721c24;
             background-color: #f8d7da;
             border-color: #f5c6cb;
        }
        .alert-warning {
             color: #856404;
             background-color: #fff3cd;
             border-color: #ffeeba;
        }

        /* --- CSS Cho Footer (nếu cần) --- */
        .page-wrapper footer { /* Giả sử footer có thẻ footer */
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
        }

    </style>

    <script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
</head>
<body>

<?php
// Include header chính của trang (nơi chứa logo, menu,...)
// QUAN TRỌNG: File này KHÔNG ĐƯỢC chứa session_start() nữa.
include_once ("header2_index.php");
?>

<div class="page-wrapper">

    <div class="container"> <div class="form-container"> <h3 class="form-title">GỬI LIÊN HỆ/GÓP Ý</h3>

            <?php
                // Hiển thị thông báo lỗi hoặc thành công (nếu có)
                if (!empty($message)) {
                    echo $message;
                }
            ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); // Gửi lại chính trang này ?>" name="index_Lienhe" id="contactForm" enctype="multipart/form-data">

                <div class="form-group row">
                    <label for="HoTen" class="col-md-3 col-form-label"><strong>Họ Tên: <span style="color:red;">*</span></strong></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="HoTen" name="HoTen" placeholder="Ví dụ: Nguyễn Văn An" required value="<?php echo isset($_POST['HoTen']) ? htmlspecialchars($_POST['HoTen']) : ''; // Giữ lại giá trị đã nhập nếu có lỗi ?>">
                    </div>
                </div>

                <div class="form-group row">
                     <label for="noidung" class="col-md-3 col-form-label"><strong>Nội dung: <span style="color:red;">*</span></strong></label>
                    <div class="col-md-9">
                        <textarea class="form-control" id="noidung" name="noidung" rows="6" required><?php echo isset($_POST['noidung']) ? htmlspecialchars($_POST['noidung']) : ''; // Giữ lại giá trị đã nhập ?></textarea>
                         <script type="text/javascript">
                             // Đảm bảo CKEditor được gọi sau khi textarea đã tồn tại trong DOM
                             // Nên đặt trong window.onload hoặc DOMContentLoaded nếu có vấn đề timing
                             document.addEventListener("DOMContentLoaded", function() {
                                CKEDITOR.replace('noidung');
                             });
                         </script>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="DienThoai" class="col-md-3 col-form-label"><strong>Điện thoại:</strong></label>
                    <div class="col-md-9">
                        <input type="tel" class="form-control" id="DienThoai" name="DienThoai" placeholder="Ví dụ: 0912345678" pattern="[0-9]{10,11}" title="Số điện thoại gồm 10 hoặc 11 chữ số" value="<?php echo isset($_POST['DienThoai']) ? htmlspecialchars($_POST['DienThoai']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group row">
                     <label for="Email" class="col-md-3 col-form-label"><strong>Email: <span style="color:red;">*</span></strong></label>
                    <div class="col-md-9">
                        <input type="email" class="form-control" id="Email" name="Email" placeholder="Ví dụ: emailcuaban@example.com" required value="<?php echo isset($_POST['Email']) ? htmlspecialchars($_POST['Email']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group row">
                     <label for="NgayGui" class="col-md-3 col-form-label"><strong>Ngày gửi:</strong></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="NgayGui" name="NgayGui" readonly value="<?php echo date("Y-m-d"); ?>">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-9 offset-md-3 button-group">
                        <input type="submit" name="Them" value="Gửi Liên Hệ" class="btn btn-primary"/>
                        <input type="reset" name="Cancel" value="Nhập Lại" class="btn btn-secondary"/>
                    </div>
                </div>

            </form> </div> </div> <?php
    // Include file footer
    include_once ("footer.php");
    ?>

</div> <?php
// 6. Đóng kết nối cơ sở dữ liệu ở cuối trang, sau khi mọi thứ đã xong
// Chỉ đóng nếu biến $conn tồn tại và là một đối tượng mysqli hợp lệ
if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
?>
</body>
</html>