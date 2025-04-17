<?php
// Khởi tạo session và bộ đệm đầu ra nếu chưa có
if (!isset($_SESSION)) {
    session_start();
    ob_start(); // Giữ lại ob_start nếu cần
}

// !!! Cần đảm bảo đường dẫn này chính xác !!!
include_once('../connection/connect_database.php');

// --- Phần Logic Pagination (GIỮ NGUYÊN) ---
$limit = 6;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$total_records = 0; // Khởi tạo
if ($conn) {
    $sql_count = "SELECT COUNT(idBV) AS total FROM baiviet";
    $query_count = mysqli_query($conn, $sql_count);
    if ($query_count) {
        $row_count = mysqli_fetch_assoc($query_count);
        $total_records = $row_count['total'] ?? 0;
    } else {
        error_log("Count query failed: " . mysqli_error($conn));
    }
} else {
     error_log("Database connection failed.");
}

$total_page = ($limit > 0 && $total_records > 0) ? ceil($total_records / $limit) : 1;
$start = ($current_page - 1) * $limit;
if ($current_page > $total_page && $total_page > 0) {
    $current_page = $total_page;
    $start = ($current_page - 1) * $limit;
}
$start = max(0, $start);

// --- Fetch Paginated Articles (GIỮ NGUYÊN) ---
$articles = [];
if ($conn) {
    $sql_paginated = "SELECT idBV, TieuDe, MoTa, NgayCapNhat, img FROM baiviet ORDER BY NgayCapNhat DESC LIMIT ?, ?";
    if ($stmt = mysqli_prepare($conn, $sql_paginated)) {
        mysqli_stmt_bind_param($stmt, 'ii', $start, $limit);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $articles[] = $row; }
            mysqli_free_result($result);
        } else { error_log("Pagination query execution failed: " . mysqli_stmt_error($stmt)); }
        mysqli_stmt_close($stmt);
    } else { error_log("Pagination query preparation failed: " . mysqli_error($conn)); }
    mysqli_close($conn);
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #fff0f5; /* <<<=== NỀN HỒNG NHẠT CHO TOÀN BỘ BODY */
            margin: 0; /* Xóa margin mặc định của body */
        }

        /* === KHUNG TRẮNG BAO NỘI DUNG CHÍNH, CĂN GIỮA === */
        .content-container {
            max-width: 1400px; /* <<<=== TĂNG CHIỀU RỘNG TỐI ĐA LÊN 1400px */
            margin: 30px auto; /* Căn giữa theo chiều ngang, cách trên dưới 30px */
            background-color:rgb(237, 201, 212); /* <<<=== NỀN TRẮNG CHO KHUNG NỘI DUNG */
            padding: 20px; /* Khoảng đệm bên trong khung trắng */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Shadow nhẹ cho đẹp */
            border-radius: 8px; /* Bo góc nhẹ */
            overflow: hidden; /* Đảm bảo các thành phần con không tràn ra ngoài */
        }


         /* <<<=== BỎ NỀN HỒNG ĐẤT VÀ MÀU CHỮ TRẮNG CỦA KHUNG MAIN CŨ */
         main.framed-content {
             /* Bỏ các thuộc tính cũ để nó ăn theo nền trắng của content-container */
             /* margin-top: 2rem !important; */ /* Có thể giữ hoặc bỏ tùy ý */
             /* margin-bottom: 3rem !important; */ /* Có thể giữ hoặc bỏ tùy ý */
             padding: 2.5rem; /* Giữ padding nếu muốn khoảng cách bên trong phần main */
             /* background-color:rgb(231, 168, 197) !important; */ /* <<< BỎ */
             /* border-radius: 0.5rem; */ /* <<< BỎ */
             /* box-shadow: 0 0.25rem 0.75rem rgba(250, 245, 245, 0.1); */ /* <<< BỎ */
             /* color: #ffffff; */ /* <<< BỎ */
         }

         /* ----- CÁC STYLE KHÁC GIỮ NGUYÊN hoặc CHỈNH SỬA MÀU CHỮ ----- */

        .news-header {
            margin-bottom: 2.5rem !important;
            text-align: center;
        }
        .news-header h3 {
             font-weight: 700;
             color: #333333; /* <<<=== ĐỔI MÀU CHỮ TIÊU ĐỀ SANG ĐEN/XÁM ĐẬM */
             position: relative; display: inline-block; padding-bottom: 0.6rem;
             /* text-shadow: 1px 1px 3px rgba(0,0,0,0.3); */ /* Có thể bỏ shadow nếu muốn */
         }
         .news-header h3::after {
             content: ''; position: absolute; left: 50%; bottom: 0; transform: translateX(-50%);
             width: 80px; height: 4px;
             background-color: #e7a8c5; /* <<<=== ĐỔI MÀU GẠCH CHÂN (ví dụ: hồng đậm hơn nền) */
             border-radius: 2px;
             /* opacity: 0.8; */
         }

        /* Card bài viết giữ nền trắng */
        .article-card {
            border: none;
            border-radius: 0.5rem;
            transition: transform .2s ease-in-out, box-shadow .2s ease-in-out;
            overflow: hidden;
            box-shadow: 0 0.2rem 0.5rem rgba(0, 0, 0, 0.08);
            background-color: #ffffff; /* Nền card vẫn trắng */
            display: flex; flex-direction: column;
            border: 1px solid #eee; /* Thêm viền nhẹ nếu muốn */
        }
        .article-card:hover { transform: translateY(-5px); box-shadow: 0 0.6rem 1.2rem rgba(0, 0, 0, 0.12) !important; }
        .article-card .card-img-top { width: 100%; height: 200px; object-fit: cover; }
        .article-card .card-body { display: flex; flex-direction: column; flex-grow: 1; padding: 1.25rem; padding-bottom: 0; }
        .article-card .card-title { margin-bottom: 0.5rem; font-size: 1.05rem; font-weight: 500;}
        .article-card .card-title a { color: #212529 !important; text-decoration: none; } /* Màu chữ title đậm */
        .article-card .card-title a:hover { color: #0d6efd !important; } /* Hover màu xanh dương */
        .card-text.short-desc {
            overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
            min-height: 63px; color: #5a6268 !important; font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem;
        }
        /* Footer card */
        .card-footer-custom {
            margin-top: auto; padding: 0.8rem 1.25rem;
            border-top: 1px solid #f0f0f0;
            display: flex; justify-content: space-between; align-items: center; background: transparent;
        }
        .card-meta { font-size: 0.8rem; color: #6c757d !important; }
        .card-meta .bi { vertical-align: middle; margin-right: 0.25rem;}
        .article-card .btn-outline-primary {
             color: #0d6efd !important;
             border-color: #0d6efd !important;
         }
         .article-card .btn-outline-primary:hover {
             color: #ffffff !important;
             background-color: #0d6efd !important;
         }


        /* Pagination */
        .pagination-container { margin-top: 3rem; }
        .pagination .page-link {
             color: #0d6efd;
             background-color: #fff;
             border: 1px solid #dee2e6;
             margin: 0 3px; border-radius: 0.25rem;
             transition: all 0.2s ease;
         }
        .pagination .page-link:hover {
             color: #0a58ca; background-color: #e9ecef; border-color: #dee2e6;
         }
        .pagination .page-item.active .page-link {
             z-index: 3; color: #fff; background-color: #0d6efd; border-color: #0d6efd;
         }
        .pagination .page-item.disabled .page-link {
             color: #6c757d; pointer-events: none; background-color: #fff; border-color: #dee2e6;
         }

        img[alt]:not([src]), img[src=""] { background-color: #eee; position: relative; }
        img[alt]:not([src])::after, img[src=""]::after { content: attr(alt); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #aaa; font-size: 0.9rem; text-align: center; max-width: 90%; padding: 10px;}
    </style>
</head>
<body>

<div class="content-container">

    <?php
        // !!! GIỮ NGUYÊN include_once("header1.php") !!!
        // Header 1 nằm trong khung trắng
        include_once("header1.php");
    ?>
    <?php
        // !!! GIỮ NGUYÊN include_once("header2.php") !!!
        // Header 2 nằm trong khung trắng
        include_once("header2.php");
    ?>
    <?php
        // Đã bỏ include_once("header3.php");
    ?>

    <?php // --- Bắt đầu Main Content (Nằm trong khung trắng) --- ?>
    <?php // Class framed-content giờ không còn tạo khung riêng nữa ?>
    <main class="container framed-content">
        <div class="news-header text-center">
            <h3>TIN TỨC</h3>
        </div>

        <?php if (!empty($articles)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($articles as $r_bv): ?>
                    <div class="col d-flex align-items-stretch">
                        <div class="card h-100 article-card">
                             <?php $image_path = '../images/' . ($r_bv['img'] ?: 'placeholder.jpg'); ?>
                            <img src="<?php echo htmlspecialchars($image_path); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($r_bv['TieuDe']); ?>">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="MoTa_BaiViet.php?idBV=<?php echo $r_bv['idBV']; ?>" class="text-decoration-none stretched-link">
                                        <?php echo htmlspecialchars($r_bv['TieuDe']); ?>
                                    </a>
                                </h5>
                                <p class="card-text short-desc">
                                    <?php $description = strip_tags($r_bv['MoTa']); echo htmlspecialchars($description); ?>
                                </p>
                                <div class="card-footer-custom">
                                    <span class="card-meta"><i class="bi bi-clock"></i> <?php echo date("d/m/Y", strtotime($r_bv['NgayCapNhat'])); ?></span>
                                    <a href="MoTa_BaiViet.php?idBV=<?php echo $r_bv['idBV']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Xem thêm</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_page > 1): ?>
            <div class="pagination-container">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php // (Giữ nguyên code pagination logic) ?>
                         <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="ds_baiviet.php?page=<?= ($current_page - 1) ?>" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
                         <?php $range = 2; $start_page = max(1, $current_page - $range); $end_page = min($total_page, $current_page + $range); if ($start_page > 1) { echo '<li class="page-item"><a class="page-link" href="ds_baiviet.php?page=1">1</a></li>'; if ($start_page > 2) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; } } for ($i = $start_page; $i <= $end_page; $i++): ?><li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>" <?= ($i == $current_page) ? 'aria-current="page"' : '' ?>><a class="page-link" href="ds_baiviet.php?page=<?= $i ?>"><?= $i ?></a></li><?php endfor; if ($end_page < $total_page) { if ($end_page < $total_page - 1) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; } echo '<li class="page-item"><a class="page-link" href="ds_baiviet.php?page='.$total_page.'">'.$total_page.'</a></li>'; } ?>
                         <li class="page-item <?= ($current_page >= $total_page) ? 'disabled' : '' ?>"><a class="page-link" href="ds_baiviet.php?page=<?= ($current_page + 1) ?>" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-center mt-5 alert alert-info">Không có bài viết nào để hiển thị.</p>
        <?php endif; ?>

    </main>
    <?php // --- Kết thúc Main Content --- ?>


    <?php
        // !!! GIỮ NGUYÊN include_once("footer.php") !!!
        // Footer nằm trong khung trắng
        include_once("footer.php");
    ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

<?php
    // Gửi output buffer nếu bạn dùng ob_start() ở đầu file
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
?>
</body>
</html>