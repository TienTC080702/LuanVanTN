<?php
include_once('../connection/connect_database.php');

$baiviet_data = null;
$error_load = null;

if (!isset($_GET['idBV']) || !is_numeric($_GET['idBV'])) {
    $error_load = "ID Bài viết không hợp lệ.";
} else {
    $idBV_get = intval($_GET['idBV']);
    $sl_baiviet = "SELECT * FROM baiviet WHERE idBV =" . $idBV_get;
    $rs_baiviet = mysqli_query($conn, $sl_baiviet);

    if (!$rs_baiviet) {
        $error_load = "Lỗi truy vấn bài viết: " . mysqli_error($conn);
    } else {
        // Dùng fetch_assoc sẽ tốt hơn fetch_array nếu chỉ dùng key tên cột
        $baiviet_data = mysqli_fetch_assoc($rs_baiviet);
        if (!$baiviet_data) {
            $error_load = "Không tìm thấy bài viết với ID=" . $idBV_get;
        }
        mysqli_free_result($rs_baiviet);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title><?php echo $baiviet_data ? htmlspecialchars($baiviet_data['TieuDe']) : 'Chi tiết bài viết'; ?></title>
    <?php include_once('header2.php'); ?>
    <style>
        /* CSS cho nội dung bài viết */
        .article-container {
            background-color: #ffffff; /* Nền trắng cho dễ đọc */
            padding: 30px 20px; /* Padding xung quanh nội dung */
            border-radius: 5px;
           /* box-shadow: 0 1px 3px rgba(0,0,0,0.1); */ /* Shadow nhẹ nếu muốn */
           margin-bottom: 30px;
        }
        .article-title {
            color: #333; /* Màu tiêu đề đậm */
            text-align: center;
            font-size: 2em; /* Cỡ chữ lớn hơn */
            font-weight: bold;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        .article-image {
            display: block; /* Để căn giữa bằng margin */
            max-width: 80%; /* Giới hạn chiều rộng ảnh */
            height: auto; /* Giữ tỷ lệ ảnh */
            margin: 0 auto 25px auto; /* Căn giữa ảnh và tạo khoảng cách dưới */
            border-radius: 5px; /* Bo góc nhẹ */
             box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .article-description {
            font-size: 1.1em;
            font-weight: bold;
            color: #555;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee; /* Đường kẻ phân cách mô tả */
             line-height: 1.6;
             /* Không cần display: block vì div mặc định là block */
        }
        .article-content {
            font-size: 1em; /* Cỡ chữ nội dung */
            line-height: 1.7; /* Giãn dòng cho dễ đọc */
            color: #444;
            text-align: left; /* Căn lề trái */
            background-color: transparent; /* Bỏ màu nền inline cũ */
            /* Font family có thể kế thừa từ body hoặc đặt lại nếu muốn */
            font-family: Arial, sans-serif; /* Ví dụ */
        }
        /* Style cho các thẻ HTML sinh ra từ CKEditor nếu cần */
        .article-content p { margin-bottom: 1em; }
        .article-content img { max-width: 100%; height: auto; margin: 10px 0; }
        .article-content h1,
        .article-content h2,
        .article-content h3 { margin-top: 1.5em; margin-bottom: 0.8em; }

    </style>
</header>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="article-container">
                <?php if ($error_load): ?>
                    <div class="alert alert-danger text-center">
                        <?php echo htmlspecialchars($error_load); ?>
                    </div>
                <?php elseif ($baiviet_data): ?>
                    <h1 class="article-title"><?php echo htmlspecialchars($baiviet_data['TieuDe']); ?></h1>

                    <?php if (!empty($baiviet_data['img'])): ?>
                        <img src="../images/baiviet/<?php echo htmlspecialchars($baiviet_data['img']); ?>" alt="<?php echo htmlspecialchars($baiviet_data['TieuDe']); ?>" class="article-image">
                    <?php endif; ?>

                    <?php if (!empty($baiviet_data['MoTa'])): ?>
                        <div class="article-description">
                            <?php echo $baiviet_data['MoTa']; // Hiển thị HTML từ CKEditor ?>
                        </div>
                    <?php endif; ?>

                    <div class="article-content">
                        <?php echo $baiviet_data['NoiDung']; // Hiển thị HTML từ CKEditor ?>
                    </div>
                <?php else: ?>
                     <div class="alert alert-warning text-center">Không có dữ liệu bài viết để hiển thị.</div>
                <?php endif; ?>
            </div> </div> </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>