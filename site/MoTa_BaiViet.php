<?php
// Đặt session_start() LÀ DÒNG ĐẦU TIÊN, TRƯỚC MỌI THỨ KHÁC
// Đảm bảo không có ký tự hay khoảng trắng nào trước thẻ <?php này
session_start();

// Sau đó mới include các file khác và thực hiện logic
// Thay thế bằng đường dẫn thực tế đến file kết nối của bạn
include ('../connection/connect_database.php');

// Khởi tạo biến
$post_data = null;
$error_message = '';

// 1. Kiểm tra kết nối CSDL
if (!$conn || mysqli_connect_errno()) {
    // Ghi log lỗi chi tiết cho admin (không hiển thị cho người dùng)
    // Đảm bảo server có quyền ghi vào thư mục log
    error_log("Database Connection Error in MoTa_BaiViet.php: " . mysqli_connect_error());
    // Đặt thông báo lỗi chung cho người dùng
    $error_message = "Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.";
} else {
    // Chỉ tiếp tục nếu kết nối thành công
    if(isset($_GET['idBV'])) {
        // Lấy và ép kiểu ID bài viết từ URL một cách an toàn
        $idBV = filter_input(INPUT_GET, 'idBV', FILTER_VALIDATE_INT);

        if ($idBV === false || $idBV <= 0) {
            $error_message = "ID bài viết không hợp lệ.";
        } else {
            // Sử dụng tên cột NgayCapNhat từ CSDL
            $sql = "SELECT TieuDe, NoiDung, NgayCapNhat FROM baiviet WHERE idBV = ?";
            $stmt = mysqli_prepare($conn, $sql);

            // 2. Kiểm tra kết quả của mysqli_prepare()
            if ($stmt === false) {
                $mysql_error = mysqli_error($conn);
                // Ghi log lỗi SQL prepare
                error_log("MoTa_BaiViet.php - mysqli_prepare failed for query [$sql] with ID [$idBV]: " . $mysql_error);
                // Thông báo lỗi chung
                $error_message = "Đã xảy ra lỗi khi chuẩn bị truy vấn dữ liệu. Vui lòng thử lại sau.";
                $post_data = null;
            } else {
                // Bind ID vào câu lệnh
                mysqli_stmt_bind_param($stmt, "i", $idBV);

                // Thực thi câu lệnh
                if (!mysqli_stmt_execute($stmt)) {
                    // Ghi log lỗi execute
                     error_log("MoTa_BaiViet.php - mysqli_stmt_execute failed for ID [$idBV]: " . mysqli_stmt_error($stmt));
                     $error_message = "Đã xảy ra lỗi khi thực thi truy vấn.";
                     $post_data = null;
                } else {
                    // Lấy kết quả
                    $result = mysqli_stmt_get_result($stmt);

                    if($result && mysqli_num_rows($result) > 0) {
                        // Lấy dữ liệu bài viết
                        $post_data = mysqli_fetch_assoc($result);
                    } else {
                        // Không tìm thấy bài viết hoặc không có kết quả
                        $error_message = "Không tìm thấy bài viết yêu cầu (ID: " . htmlspecialchars($idBV) . ")."; // Dùng htmlspecialchars cho ID khi hiển thị
                    }
                }
                // Luôn đóng statement sau khi dùng xong
                mysqli_stmt_close($stmt);
            }
        }
    } else {
        $error_message = "Không tìm thấy ID bài viết trên URL.";
    }
    // Đóng kết nối CSDL nếu không cần dùng nữa ở cuối trang
    // mysqli_close($conn); // Thường không cần thiết nếu script kết thúc ngay sau đó
}

?>
<!DOCTYPE html> <?php // Output HTML bắt đầu từ đây ?>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($post_data['TieuDe']) ? htmlspecialchars($post_data['TieuDe']) : 'Chi tiết bài viết'; ?></title>
    <?php // Đảm bảo đường dẫn CSS đúng ?>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/hoa.min.css" type="text/css"> <?php // CSS tùy chỉnh ?>
    <?php
        // Giả sử header1.php chỉ chứa các link/meta phụ trợ, không chứa <title> hoặc charset
        // Nếu header1.php có các thẻ đó, bạn cần điều chỉnh để tránh trùng lặp
        // Đảm bảo header1.php KHÔNG chứa session_start()
        include_once ("header1.php");
    ?>
    <style>
        /* CSS để tạo khung hồng và style nội dung */
        body {
            background-color: #f0f0f0; /* Màu nền ngoài khung */
            padding: 0;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Font dễ đọc hơn */
        }
        #main-container {
            max-width: 1140px; /* Có thể dùng 1140px hoặc 1200px */
            margin: 30px auto; /* Căn giữa, khoảng cách trên dưới */
            border: 1px solid rgb(236, 206, 227); /* Viền hồng nhạt */
            background-color: rgb(255, 231, 236); /* Nền hồng nhạt */
            padding: 0; /* Không padding ở đây */
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); /* Đổ bóng */
            border-radius: 8px; /* Bo góc */
            overflow: hidden; /* Giữ nội dung bên trong */
        }
        .content-wrapper {
             padding: 30px 40px; /* Padding cho nội dung trắng */
             background-color: #ffffff; /* Nền trắng */
             margin: 25px; /* Khoảng cách với viền hồng */
             border-radius: 5px; /* Bo góc khung trắng */
             box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* Đổ bóng nhẹ */
             min-height: 400px; /* Chiều cao tối thiểu để footer không bị đẩy lên quá cao */
        }
        .post-title {
            font-size: 2.2em; /* Điều chỉnh kích thước tiêu đề */
            color: #c85180; /* Màu hồng đậm */
            margin-bottom: 15px;
            font-weight: 600; /* Độ đậm vừa phải */
            text-align: center;
            line-height: 1.3;
            word-wrap: break-word; /* Để tiêu đề dài tự xuống dòng */
        }
        .post-meta {
            text-align: center;
            color: #888; /* Màu xám */
            margin-bottom: 30px;
            font-size: 0.9em;
        }
        .post-meta .glyphicon { /* Style cho icon */
            margin-right: 5px;
            color: #aaa; /* Màu icon nhạt hơn */
            vertical-align: middle; /* Căn icon với text */
        }
        .post-content {
            font-size: 1.05em; /* Kích thước chữ nội dung */
            line-height: 1.8; /* Giãn dòng */
            color: #333333; /* Màu chữ chính */
            text-align: justify; /* Căn đều */
            word-wrap: break-word; /* Xuống dòng khi từ quá dài */
        }
        .post-content p {
            margin-bottom: 1.5em; /* Khoảng cách đoạn */
        }
        .post-content img { /* Style cho ảnh trong bài viết */
            max-width: 100%;
            height: auto; /* Giữ tỷ lệ */
            display: block; /* Để căn giữa bằng margin */
            margin: 20px auto; /* Căn giữa và tạo khoảng cách */
            border-radius: 5px; /* Bo góc ảnh */
            box-shadow: 0 3px 8px rgba(0,0,0,0.1); /* Đổ bóng nhẹ cho ảnh */
        }
        /* CSS cho thông báo lỗi/cảnh báo */
        .alert {
            margin-top: 20px;
            word-wrap: break-word; /* Đảm bảo nội dung lỗi dài cũng xuống dòng */
        }
        /* Đảm bảo footer có khoảng cách và nằm trong khung hồng */
        /* Bạn có thể cần thêm style cho footer.php nếu nó chưa phù hợp */

    </style>
</head>
<body>

<?php // ----- KHUNG HỒNG BAO QUANH ----- ?>
<div id="main-container">

    <?php
        // Giả sử header2.php là thanh menu chính
        // Đảm bảo header2.php KHÔNG chứa session_start()
        include_once ("header2.php");
    ?>

    <?php // ----- PHẦN NỘI DUNG CHÍNH ----- ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12"> <?php // Sử dụng full cột ?>

                <div class="content-wrapper"> <?php // Khung trắng chứa bài viết ?>

                    <?php // Hiển thị lỗi HOẶC nội dung bài viết ?>
                    <?php if (!empty($error_message)): // Ưu tiên hiển thị lỗi nếu có ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php elseif ($post_data): // Nếu không có lỗi VÀ có dữ liệu bài viết ?>
                        <?php // Tiêu đề bài viết ?>
                        <h1 class="post-title"><?php echo htmlspecialchars($post_data['TieuDe']); ?></h1>

                        <?php // Thông tin meta: Ngày cập nhật (CHỈ NGÀY) ?>
                        <?php if (isset($post_data['NgayCapNhat']) && !empty($post_data['NgayCapNhat'])): ?>
                        <div class="post-meta">
                            <span class="glyphicon glyphicon-calendar"></span> <?php // Icon lịch ?>
                            <?php
                                try {
                                    // Tạo đối tượng DateTime từ chuỗi ngày tháng trong CSDL
                                    $date = new DateTime($post_data['NgayCapNhat']);
                                    // Định dạng chỉ hiển thị ngày/tháng/năm
                                    echo 'Cập nhật: ' . $date->format('d/m/Y');
                                } catch (Exception $e) {
                                    // Nếu có lỗi khi chuyển đổi ngày tháng, ghi log và hiển thị nguyên gốc
                                    error_log("MoTa_BaiViet.php - DateTime format error for NgayCapNhat [" . $post_data['NgayCapNhat'] . "]: " . $e->getMessage());
                                    echo htmlspecialchars($post_data['NgayCapNhat']);
                                }
                            ?>
                        </div>
                        <?php endif; ?>

                        <?php // Nội dung bài viết ?>
                        <div class="post-content">
                            <?php
                                // Hiển thị nội dung bài viết
                                // QUAN TRỌNG: Phải đảm bảo nội dung đã được lọc XSS trước khi lưu vào CSDL.
                                // Nếu nội dung chứa thẻ HTML bạn muốn hiển thị, echo trực tiếp là cần thiết.
                                echo $post_data['NoiDung'];
                            ?>
                        </div>
                    <?php else: // Trường hợp không có lỗi nhưng $post_data vẫn rỗng/null ?>
                         <div class="alert alert-warning text-center" role="alert">
                            <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                            Không có dữ liệu bài viết để hiển thị.
                        </div>
                    <?php endif; ?>

                </div> <?php // Kết thúc .content-wrapper ?>

            </div> <?php // Kết thúc .col-md-12 ?>
        </div> <?php // Kết thúc .row ?>
    </div> <?php // Kết thúc .container-fluid ?>
    <?php // ----- KẾT THÚC PHẦN NỘI DUNG CHÍNH ----- ?>


    <?php
        // Footer nằm bên trong khung hồng
        // Đảm bảo footer.php KHÔNG chứa session_start()
        include_once ("footer.php");
    ?>

</div> <?php // ----- KẾT THÚC KHUNG HỒNG #main-container ----- ?>


<?php // ----- JavaScript ----- ?>
<?php // Đảm bảo đường dẫn JS đúng ?>
<script src="../js/jquery-3.1.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<?php // Có thể thêm các script JS khác của bạn ở đây ?>

</body>
</html>