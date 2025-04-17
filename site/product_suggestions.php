<?php
// Bật hiển thị lỗi để dễ debug (nên tắt trên production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Đảm bảo session đã được khởi tạo (nếu cần dùng session cho giỏ hàng)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    // ob_start(); // Nếu cần output buffering
}


include_once ('../connection/connect_database.php'); // Kết nối CSDL

$san_pham_goi_y = []; // Mảng chứa các sản phẩm gợi ý
$mo_ta_tieu_chi = "Không có tiêu chí nào được chọn."; // Mô tả tiêu chí đã dùng
$error_message = ''; // Lưu thông báo lỗi nếu có

// 1. Nhận dữ liệu từ POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ POST
    $loai_da = isset($_POST['skin_type']) && $_POST['skin_type'] !== '' ? trim($_POST['skin_type']) : null;
    $cac_van_de = isset($_POST['concerns']) && is_array($_POST['concerns']) ? $_POST['concerns'] : [];
    $do_tuoi = isset($_POST['age_range']) && $_POST['age_range'] !== '' ? trim($_POST['age_range']) : null; // Hiện chưa dùng đến

    // Làm sạch mảng các vấn đề da
    $cac_van_de_da_sach = [];
    foreach ($cac_van_de as $van_de) {
        $cac_van_de_da_sach[] = trim($van_de);
    }

    // --- ĐÃ SỬA: PHẦN XÂY DỰNG TRUY VẤN SQL ---
    // 2. Xây dựng câu truy vấn SQL bằng Prepared Statements
    $cau_sql_base = "SELECT idSP, TenSP, GiaBan, GiaKhuyenmai, urlHinh, SoLuongTonKho
                      FROM sanpham
                      WHERE AnHien = 1"; // Chỉ lấy sản phẩm đang hiện

    $dieu_kien_loc = []; // Mảng chứa các nhóm điều kiện chính (kết hợp bằng AND)
    $params = [];        // Mảng chứa các giá trị tham số cho prepared statement
    $types = "";         // Chuỗi chứa kiểu dữ liệu của tham số
    $cac_tieu_chi = [];  // Mảng chứa mô tả các tiêu chí đã chọn

    // --- Xử lý Loại Da (Kết hợp cột loai_da_phu_hop VÀ cột MoTa) ---
    if (!empty($loai_da)) {
        $dieu_kien_loai_da_group = []; // Điều kiện OR trong nhóm loại da

        // Điều kiện 1: Kiểm tra cột loai_da_phu_hop
        $dieu_kien_loai_da_group[] = "(loai_da_phu_hop LIKE ? OR loai_da_phu_hop LIKE ?)";
        $params[] = "%" . $loai_da . "%";
        $params[] = "%Mọi loại da%";
        $types .= "ss";

        // Điều kiện 2: Kiểm tra cột MoTa
        $dieu_kien_loai_da_group[] = "MoTa LIKE ?";
        $params[] = "%" . $loai_da . "%"; // Tìm tên loại da trong mô tả
        $types .= "s";

        // Kết hợp 2 điều kiện trên bằng OR (hoặc cột này khớp, hoặc cột kia khớp)
        $dieu_kien_loc[] = "(" . implode(' OR ', $dieu_kien_loai_da_group) . ")";
        $cac_tieu_chi[] = "Loại da: <strong>" . htmlspecialchars($loai_da) . "</strong>";
    }

    // --- Xử lý Vấn đề Da (Kết hợp cột van_de_da_giai_quyet VÀ cột MoTa) ---
    if (!empty($cac_van_de_da_sach)) {
        $dieu_kien_van_de_group = []; // Điều kiện OR trong nhóm vấn đề da

        // Điều kiện 1: Kiểm tra cột van_de_da_giai_quyet
        $dieu_kien_cot_van_de_placeholder = [];
        $params_cot_van_de = []; // Tham số riêng cho cột này
        $types_cot_van_de = "";  // Kiểu riêng cho cột này
        foreach ($cac_van_de_da_sach as $mot_van_de) {
            $dieu_kien_cot_van_de_placeholder[] = "van_de_da_giai_quyet LIKE ?";
            $params_cot_van_de[] = "%" . $mot_van_de . "%";
            $types_cot_van_de .= "s";
        }
        $dieu_kien_van_de_cot_sql = "";
        if (!empty($dieu_kien_cot_van_de_placeholder)) {
             // Phải khớp ít nhất 1 vấn đề trong cột van_de_da_giai_quyet
            $dieu_kien_van_de_cot_sql = "(" . implode(' OR ', $dieu_kien_cot_van_de_placeholder) . ")";
            $dieu_kien_van_de_group[] = $dieu_kien_van_de_cot_sql; // Thêm vào nhóm OR lớn
            $params = array_merge($params, $params_cot_van_de); // Gộp tham số
            $types .= $types_cot_van_de; // Gộp kiểu
        }

        // Điều kiện 2: Kiểm tra cột MoTa
        $dieu_kien_mota_van_de_placeholder = [];
        $params_mota_van_de = []; // Tham số riêng
        $types_mota_van_de = "";  // Kiểu riêng
         foreach ($cac_van_de_da_sach as $mot_van_de) {
             $keyword_mota = $mot_van_de;
             $dieu_kien_mota_van_de_placeholder[] = "MoTa LIKE ?";
             $params_mota_van_de[] = "%" . $keyword_mota . "%";
             $types_mota_van_de .= "s";
         }
         $dieu_kien_van_de_mota_sql = "";
        if (!empty($dieu_kien_mota_van_de_placeholder)) {
             // Phải khớp ít nhất 1 vấn đề trong cột MoTa
            $dieu_kien_van_de_mota_sql = "(" . implode(' OR ', $dieu_kien_mota_van_de_placeholder) . ")";
             $dieu_kien_van_de_group[] = $dieu_kien_van_de_mota_sql; // Thêm vào nhóm OR lớn
             $params = array_merge($params, $params_mota_van_de); // Gộp tham số
             $types .= $types_mota_van_de; // Gộp kiểu
        }

        // Kết hợp các điều kiện OR trong nhóm vấn đề da
        if (!empty($dieu_kien_van_de_group)) {
             $dieu_kien_loc[] = "(" . implode(' OR ', $dieu_kien_van_de_group) . ")";
             $cac_tieu_chi[] = "Vấn đề: <strong>" . htmlspecialchars(implode(', ', $cac_van_de_da_sach)) . "</strong>";
        }
    }

    // --- Nối các điều kiện ---
    $cau_sql_final = $cau_sql_base;
    if (!empty($dieu_kien_loc)) {
        // Kết hợp các nhóm điều kiện (Loại da AND Vấn đề da) bằng AND
        $cau_sql_final .= " AND " . implode(' AND ', $dieu_kien_loc);
    }

    // Sắp xếp và giới hạn kết quả
    $cau_sql_final .= " ORDER BY NgayCapNhat DESC LIMIT 10"; // Giới hạn 10 sản phẩm

    // Tạo mô tả tiêu chí
    if (!empty($cac_tieu_chi)) {
        $mo_ta_tieu_chi = "Gợi ý dựa trên: " . implode('; ', $cac_tieu_chi) . " (bao gồm tìm kiếm trong mô tả).";
    } else {
        // Nếu không có tiêu chí nào, lấy sản phẩm mới nhất chung
        $cau_sql_final = $cau_sql_base . " ORDER BY NgayCapNhat DESC LIMIT 10";
        $mo_ta_tieu_chi = "Không có tiêu chí cụ thể nào được áp dụng. Hiển thị các sản phẩm mới nhất.";
        $types = ""; // Reset types và params nếu không có điều kiện
        $params = [];
    }
     // --- KẾT THÚC PHẦN SỬA ---

    // 3. Chuẩn bị và thực thi truy vấn
    if (!$conn || mysqli_connect_errno()) {
         $error_message = "Lỗi kết nối CSDL: " . mysqli_connect_error();
         error_log($error_message);
    } else {
        $stmt = mysqli_prepare($conn, $cau_sql_final);
        if ($stmt) {
            // Chỉ bind param nếu có types và params
            if (!empty($types) && !empty($params)) {
                 if (strlen($types) == count($params)) {
                     // Cần PHP 5.6+ để dùng ... (splat operator)
                     if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
                         mysqli_stmt_bind_param($stmt, $types, ...$params);
                     } else {
                          $error_message = "Lỗi: Phiên bản PHP (".PHP_VERSION.") không hỗ trợ bind param động dễ dàng. Cần nâng cấp PHP hoặc dùng cách khác.";
                     }
                 } else {
                      $error_message = "Lỗi: Số lượng kiểu (".strlen($types).") và tham số (".count($params).") không khớp khi bind.";
                      error_log($error_message . " SQL: " . $cau_sql_final . " Params: " . print_r($params, true));
                 }
            }

            // Chỉ thực thi nếu không có lỗi bind param
            if (empty($error_message) && mysqli_stmt_execute($stmt)) {
                $ket_qua = mysqli_stmt_get_result($stmt);
                if ($ket_qua) {
                    while ($dong = mysqli_fetch_assoc($ket_qua)) {
                        $san_pham_goi_y[] = $dong;
                    }
                    mysqli_free_result($ket_qua);
                } else {
                    $error_message = "Lỗi lấy kết quả: " . mysqli_stmt_error($stmt);
                    error_log($error_message . " SQL: " . $cau_sql_final);
                }
            } elseif (empty($error_message)) { // Lỗi execute chỉ xảy ra nếu không có lỗi bind trước đó
                 $error_message = "Lỗi thực thi: " . mysqli_stmt_error($stmt);
                 error_log($error_message . " SQL: " . $cau_sql_final);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Lỗi chuẩn bị truy vấn: " . mysqli_error($conn);
            error_log($error_message . " SQL: " . $cau_sql_final);
        }
    } // end else check connection

} else {
    // Nếu người dùng truy cập trực tiếp (không qua POST)
    $mo_ta_tieu_chi = "Vui lòng điền thông tin vào form tư vấn để nhận gợi ý.";
    // Lấy 10 sản phẩm mới nhất làm mặc định
    $sql_default = "SELECT idSP, TenSP, GiaBan, GiaKhuyenmai, urlHinh, SoLuongTonKho FROM sanpham WHERE AnHien = 1 ORDER BY NgayCapNhat DESC LIMIT 10";
    $rs_default = mysqli_query($conn, $sql_default);
    if ($rs_default) {
        while ($dong_df = mysqli_fetch_assoc($rs_default)) {
            $san_pham_goi_y[] = $dong_df;
        }
        mysqli_free_result($rs_default);
    } else {
         $error_message = "Lỗi lấy sản phẩm mặc định: " . mysqli_error($conn);
         error_log($error_message);
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header.php"); // Meta, link cơ bản ?>
    <title>Sản Phẩm Gợi Ý Cho Bạn</title>
    <?php include_once("header1.php"); // CSS, Font... ?>
    <?php
        // Sử dụng CDN cho Bootstrap CSS và Icons (đảm bảo đã có trong header1.php hoặc thêm ở đây)
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">';
    ?>
    <style>
        /* --- CSS CHO KHUNG BAO --- */
        body {
             background-color: #f0f0f0;
             padding-top: 20px;
             padding-bottom: 20px;
        }
        .page-frame {
            background-color: #fff0f5; /* Màu hồng nhạt */
            padding: 20px;
            border-radius: 8px;
            max-width: 1200px;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        /* --- KẾT THÚC CSS KHUNG BAO --- */

         /* === CSS CHO THẺ SẢN PHẨM === */
         .product-card {
            margin-bottom: 24px;
            transition: transform .2s ease-in-out, box-shadow .2s ease-in-out;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            height: 100%;
         }
         .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
         }

         /* CSS cho thẻ a bao quanh ảnh */
         .product-card > a {
            display: block;
            padding: 15px;
            background-color: #fff;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            flex-shrink: 0;
         }

         .product-card img {
            object-fit: contain;
            aspect-ratio: 1 / 1;
            width: 85%; /* Giữ ảnh nhỏ hơn */
            display: block;
            margin: auto;
            max-height: 200px;
         }
         .card-body {
             padding: 0.8rem 1rem 1rem 1rem;
             display: flex;
             flex-direction: column;
             text-align: center;
             flex-grow: 1;
         }
         .card-title {
             font-weight: bold;
             font-size: 1.2em;
             min-height: 48px;
             margin-bottom: 0.6rem;
             line-height: 1.35;
         }
         .card-title a {
             text-decoration: none;
             color: #212529;
         }
         .card-title a:hover { color: #A75D8B; }

         .price-actions-container {
             /* margin-top: auto; */ /* <<< BỎ ĐI DÒNG NÀY */
             padding-top: 0.3rem; /* <<< GIẢM PADDING TOP */
         }

         .price-stock-block {
             margin-bottom: 0.5rem; /* <<< GIẢM KHOẢNG CÁCH DƯỚI GIÁ */
         }

         .product-price {
             font-weight: bold;
             color: #c82333;
             font-size: 1.45em; /* <<< TĂNG SIZE GIÁ LỚN HƠN */
             margin-bottom: 0 !important;
             display: block;
         }
         .price-original {
             text-decoration: line-through;
             font-size: 0.9em;
             color: #6c757d;
             margin-left: 5px;
             display: inline-block;
         }
         .price-stock-block .badge {
             display: block;
             margin: 5px auto 0 auto;
             width: fit-content;
         }

         /* CSS nút bấm */
         .product-actions .btn {
             font-weight: 500;
             /* Sử dụng size mặc định (lớn hơn sm) */
         }
         .product-actions .btn i {
             vertical-align: middle;
             font-size: 1.1em; /* Tăng nhẹ size icon */
             margin-right: 0.25rem; /* Khoảng cách icon và chữ */
         }
         /* Bỏ CSS size tùy chỉnh */

         /* Màu nút */
         .btn-warning { background-color: #ffc107; border-color: #ffc107; color: #212529; }
         .btn-warning:hover { background-color: #e0a800; border-color: #d39e00; color: #212529; }
         .btn-outline-primary { color: #A75D8B; border-color: #A75D8B; }
         .btn-outline-primary:hover { background-color: #A75D8B; border-color: #A75D8B; color: #fff; }
        /* === KẾT THÚC CSS CHO THẺ SẢN PHẨM === */

        .no-results { font-size: 1.1em; color: #5a6268; }
        .criteria-info { background-color: #ffffff; padding: 12px 18px; border-radius: 6px; margin-bottom: 30px; border: 1px solid #f1c2d5; font-size: 0.95em; color: #581532; }
        .criteria-info i { color: #dc3545; }
        .footer-actions a i { margin-right: 4px; }
        .suggestion-title { color: #A75D8B; font-weight: bold; }
        .footer-actions .btn-outline-secondary { color: #6c757d; border-color: #6c757d; }
        .footer-actions .btn-outline-secondary:hover { background-color: #6c757d; color: #fff; }
        .footer-actions .btn-outline-success { color: #198754; border-color: #198754; }
        .footer-actions .btn-outline-success:hover { background-color: #198754; color: #fff; }
    </style>
</head>
<body>
    <div class="page-frame">

        <?php include_once("header2.php"); // Header chính (logo, menu...) ?>

        <div class="container mt-4 mb-4">
            <h2 class="text-center mb-3 suggestion-title">
                <i class="bi bi-magic"></i> GỢI Ý SẢN PHẨM DÀNH RIÊNG CHO BẠN
            </h2>

            <div class="criteria-info text-center">
                <i class="bi bi-info-circle-fill"></i> <?php echo $mo_ta_tieu_chi; ?>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> Có lỗi xảy ra: <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="row row-cols-1 row-cols-sm-2 g-4">
                <?php if (!empty($san_pham_goi_y)): ?>
                    <?php foreach ($san_pham_goi_y as $san_pham): ?>
                        <div class="col d-flex align-items-stretch">
                            <div class="card product-card w-100">
                                <?php
                                    $link_sp = "MoTa.php?idSP=" . $san_pham['idSP'];
                                    $hinh_anh_path = '../images/' . basename(htmlspecialchars($san_pham['urlHinh'] ?? ''));
                                    $anh_mac_dinh = '../images/placeholder.png';
                                    $hinh_anh_url = (!empty($san_pham['urlHinh']) && file_exists($hinh_anh_path)) ? $hinh_anh_path : $anh_mac_dinh;
                                    $is_out_of_stock = (!isset($san_pham['SoLuongTonKho']) || $san_pham['SoLuongTonKho'] <= 0);
                                ?>
                                <a href="<?php echo $link_sp; ?>">
                                     <img src="<?php echo $hinh_anh_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($san_pham['TenSP']); ?>">
                                </a>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="<?php echo $link_sp; ?>" title="<?php echo htmlspecialchars($san_pham['TenSP']); ?>">
                                             <?php echo htmlspecialchars($san_pham['TenSP']); ?>
                                        </a>
                                    </h5>

                                    <div class="price-actions-container">
                                        <div class="price-stock-block">
                                             <p class="product-price">
                                                 <?php
                                                 $gia_ban = isset($san_pham['GiaBan']) ? (float)$san_pham['GiaBan'] : 0;
                                                 $gia_km = isset($san_pham['GiaKhuyenmai']) ? (float)$san_pham['GiaKhuyenmai'] : 0;
                                                 $gia_hien_thi = $gia_ban;
                                                 $co_km = false;
                                                 if ($gia_km > 0 && $gia_km < $gia_ban) {
                                                     $gia_hien_thi = $gia_km;
                                                     $co_km = true;
                                                 }
                                                 echo number_format($gia_hien_thi, 0, ',', '.'); ?> đ
                                                 <?php if ($co_km): ?>
                                                     <span class="price-original"><?php echo number_format($gia_ban, 0, ',', '.'); ?> đ</span>
                                                 <?php endif; ?>
                                             </p>
                                             <?php if($is_out_of_stock): ?>
                                                 <span class="badge bg-secondary">Hết hàng</span>
                                             <?php endif; ?>
                                        </div>

                                        <div class="product-actions d-flex justify-content-center align-items-center gap-2">
                                             <?php if ($is_out_of_stock): ?>
                                                 <button type="button" class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sản phẩm đã hết hàng">
                                                     <i class="bi bi-cart-x"></i> Hết hàng
                                                 </button>
                                             <?php else: ?>
                                                 <?php $add_to_cart_link = "GioHang.php?idSP=" . $san_pham['idSP']; ?>
                                                 <a href="<?php echo $add_to_cart_link; ?>" class="btn btn-warning" data-bs-toggle="tooltip" title="Thêm vào giỏ hàng">
                                                     <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                                                 </a>
                                             <?php endif; ?>
                                             <a href="<?php echo $link_sp; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Xem chi tiết">
                                                 <i class="bi bi-eye"></i> Chi tiết
                                             </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message)): ?>
                    <div class="col-12 text-center mt-4 p-5 bg-light rounded border">
                        <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                        <p class="no-results mt-3 mb-3">Rất tiếc, chưa có sản phẩm nào khớp hoàn toàn với các tiêu chí bạn chọn.</p>
                        <p class="text-muted small mb-4">Bạn có thể thử lại với các lựa chọn khác hoặc khám phá thêm các sản phẩm khác của chúng tôi.</p>
                        <a href="tu_van_da.php" class="btn btn-info text-white"> <i class="bi bi-arrow-repeat"></i> Thử Lại Tư Vấn</a>
                        <a href="index.php?index=1" class="btn btn-outline-dark ms-2">Xem Tất Cả Sản Phẩm</a>
                    </div>
                <?php elseif ($_SERVER["REQUEST_METHOD"] != "POST"): ?>
                     <div class="col-12 text-center mt-4 p-5 bg-light rounded border">
                         <i class="bi bi-search fs-1 text-info"></i>
                         <p class="no-results mt-3 mb-3">Hãy sử dụng Form Tư Vấn để chúng tôi gợi ý sản phẩm phù hợp nhất!</p>
                         <a href="tu_van_da.php" class="btn btn-primary"> <i class="bi bi-card-checklist"></i> Đi đến Form Tư Vấn</a>
                     </div>
                <?php endif; ?>
            </div> <?php // Kết thúc .row ?>

             <div class="text-center mt-5 footer-actions">
                  <a href="tu_van_da.php" class="btn btn-outline-secondary btn-sm"> <i class="bi bi-chevron-left"></i> Quay lại Form Tư Vấn</a>
                  <a href="index.php?index=1" class="btn btn-outline-success btn-sm ms-2"> <i class="bi bi-house-door"></i> Quay về Trang Chủ</a>
             </div>

        </div> <?php // Kết thúc .container ?>

        <?php include_once("footer.php"); // Include footer ?>

    </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
    <?php // include_once('scripts.php'); // Include các scripts khác nếu có ?>
</body>
</html>