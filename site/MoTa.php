<?php
// Nên đặt session_start() ở đầu file nếu chưa có trong header
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once('../connection/connect_database.php'); // Include một lần đủ
include_once('../libs/lib.php'); // Include libs

// --- PHẦN LOGIC PHP XỬ LÝ idSP, COMMENT,... ---
$product_details_available = false;
$r = null; // Dữ liệu sản phẩm chính
$result_img = null; // Dữ liệu ảnh phụ
$rs_cm = null; // Dữ liệu comment
$error_load = null;

// --- Lấy và kiểm tra idSP ---
if (isset($_GET['idSP']) && filter_var($_GET['idSP'], FILTER_VALIDATE_INT)) {
    $idSP = (int)$_GET['idSP'];

    // --- Lấy dữ liệu sản phẩm chính ---
    $sql_product = "SELECT * FROM sanpham WHERE idSP=" . $idSP;
    $rs_sp = mysqli_query($conn, $sql_product);
    if ($rs_sp && mysqli_num_rows($rs_sp) > 0) {
        $r = mysqli_fetch_assoc($rs_sp);
        $product_details_available = true;
        mysqli_free_result($rs_sp);

        // --- Cập nhật lượt xem ---
        $update_view_query = "UPDATE sanpham SET SoLanXem = SoLanXem + 1 WHERE idSP = " . $idSP;
        mysqli_query($conn, $update_view_query);

        // --- Lấy ảnh phụ ---
        $sql_img_check = "SELECT urlHinh FROM sanpham_hinh WHERE idSP=" . $idSP;
        $result_img = mysqli_query($conn, $sql_img_check);

        // --- Lấy comment đã duyệt ---
        $sql_comment = "SELECT * FROM sanpham_comment WHERE idSP=" . $idSP . " AND kiem_duyet = 1 ORDER BY ngay_comment DESC";
        $rs_cm = mysqli_query($conn, $sql_comment);

    } else {
        $error_load = "Sản phẩm không tồn tại hoặc đã bị xóa.";
    }
} else {
    $error_load = "ID sản phẩm không hợp lệ.";
}

// --- Xử lý POST comment (ĐÃ CẬP NHẬT ĐỂ LOẠI BỎ HTML KHI LƯU) ---
$comment_error = null;
$comment_success = null;
if (isset($_POST['binhluan'])) {
    if (!$product_details_available) {
         $comment_error = "Sản phẩm không hợp lệ để bình luận.";
    } elseif (!isset($_SESSION['Username'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        echo "<script language='javascript'>alert('Bạn phải đăng nhập để bình luận!');";
        echo "location.href='DangNhap.php';</script>";
        exit;
    } else {
        $comment_content_raw = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        $comment_content_plain = strip_tags($comment_content_raw);
        if ($conn) {
            $comment_content_escaped = mysqli_real_escape_string($conn, $comment_content_plain);
        } else {
             $comment_content_escaped = "";
             $comment_error = "Lỗi kết nối cơ sở dữ liệu.";
        }
        $comment_idSP = $idSP;
        $comment_user = $_SESSION['Username'];
        $comment_date = date("Y-m-d H:i:s");
        $comment_approve = 0;

        if ($comment_idSP > 0 && !empty($comment_content_plain) && empty($comment_error)) {
            $sl_cm = "INSERT INTO sanpham_comment(idSP, hoten, noidung, ngay_comment, kiem_duyet) VALUES(" . $comment_idSP . ", '" . $comment_user . "', '" . $comment_content_escaped . "', '" . $comment_date . "', " . $comment_approve . ") ";
            $rs_themcm = mysqli_query($conn, $sl_cm);
            if ($rs_themcm) {
                $comment_success = "Gửi bình luận thành công, bình luận của bạn đang chờ duyệt!";
                 header("Location: " . $_SERVER['PHP_SELF'] . "?idSP=" . $idSP . "&comment=success#comment-form-anchor");
                 exit;
            } else {
                error_log("Comment insert failed: " . mysqli_error($conn));
                $comment_error = "Gửi bình luận không thành công. Vui lòng thử lại.";
            }
        } elseif (empty($comment_content_plain) && empty($comment_error)) {
             $comment_error = "Vui lòng nhập nội dung bình luận!";
        }
    }
}
// Lấy lại comment nếu cần (trường hợp không redirect hoặc có lỗi)
if ($product_details_available && !($comment_success)) { // Chỉ lấy lại nếu SP tồn tại và không gửi thành công
     $sql_comment = "SELECT * FROM sanpham_comment WHERE idSP=" . $idSP . " AND kiem_duyet = 1 ORDER BY ngay_comment DESC";
     // Kiểm tra rs_cm đã tồn tại và giải phóng nếu cần trước khi query lại
     if (isset($rs_cm) && $rs_cm) {
        mysqli_free_result($rs_cm);
     }
     $rs_cm = mysqli_query($conn, $sql_comment);
}
// --- KẾT THÚC XỬ LÝ COMMENT ---
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include_once ("header1.php");?>
    <title><?php echo $product_details_available ? htmlspecialchars($r['TenSP']) : 'Chi tiết sản phẩm'; ?></title>
    <?php include_once ("header2.php");?>
    <link rel="stylesheet" href="../js/flexslider/flexslider.css" type="text/css">
    <link rel="stylesheet" href="../css/hoa.min.css" type="text/css">
    <link rel="stylesheet" href="../css/layout.min.css" type="text/css">

    <style>
        /* --- CSS CHO KHUNG BAO NGOÀI GIỐNG TRANG CHỦ --- */
        body { background-color: #f0f0f0; padding: 0; margin: 0; font-family: sans-serif; }
        #main-container { max-width: 1200px; margin: 30px auto; border: 1px solid rgb(236, 206, 227); background-color: rgb(255, 231, 236); padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden; color: #2c1e18; }
        /* --- KẾT THÚC CSS KHUNG BAO NGOÀI --- */

        /* CSS tùy chỉnh cho trang chi tiết */
        .product-details-page { /* Không cần margin nữa */ }
        .flexslider { margin: 0 0 20px 0; background: #fff; border: 1px solid #ddd; box-shadow: 0 1px 4px rgba(0,0,0,.1); position: relative; border-radius: 4px; overflow:hidden; }
        .slides img { max-height: 480px; width: auto; max-width: 100%; margin: 0 auto; display: block; border: none; }
        .product-info { background-color: #fff; padding: 25px; border-radius: 5px; border: 1px solid #eee; }
        .product-info h2.product-title { margin-top: 0; margin-bottom: 15px; font-size: 1.8em; font-weight: bold; color: #333; line-height: 1.3; }
        .product-info hr { margin-top: 15px; margin-bottom: 15px; border-top: 1px solid #eee;}
        .info-row { margin-bottom: 12px; display: flex; align-items: center; font-size: 0.95em; }
        .info-label { font-weight: bold; color: #555; width: 120px; flex-shrink: 0;}
        .info-value { color: #333; }
        .product-price .discount-price { font-size: 1.6em; font-weight: bold; color: #dd0017; margin-right: 10px; }
        .product-price .original-price { font-size: 1.1em; color: #888; text-decoration: line-through;}
        .product-price .money { font-size: 1.4em; font-weight: bold; color: #dd0017; }
        .stock-status { font-weight: bold; }
        .stock-status.in-stock { color: #28a745; }
        .stock-status.out-of-stock { color: #dc3545; }
        .product-actions { margin-top: 20px; }
        .product-actions .btn { margin-right: 10px; margin-bottom: 10px; padding: 10px 20px; font-size: 1em;}
        .product-description-wrapper { margin-top: 30px; background-color: #fff; padding: 25px; border-radius: 5px; border: 1px solid #eee; }
        .product-description-wrapper h4 { font-weight: bold; margin-top: 0; margin-bottom: 15px; font-size: 1.3em; color: #333; padding-bottom: 10px; border-bottom: 1px solid #eee;}
        .prod-box { margin-top: 0; padding: 0; border: none; background-color: transparent; word-wrap: break-word; line-height: 1.7; font-size: 1em; color: #444; }
        .prod-box img { max-width: 100%; height: auto; margin: 15px 0; display: block; border-radius: 4px; }
        .comment-section { margin-top: 30px; padding: 25px; border-top: none; background-color: #fff; border-radius: 5px; border: 1px solid #eee; }
        .comment-section h3 { margin-top:0; margin-bottom: 25px; font-size: 1.5em; font-weight: bold; color: #333; padding-bottom: 10px; border-bottom: 1px solid #eee;}
        .comment-item { margin-bottom: 25px; display: flex; }
        .comment-avatar img { border-radius: 50%; margin-right: 15px; width: 45px; height: 45px;}
        .comment-content { flex-grow: 1; }
        .comment-author { font-weight: bold; color: #0d6efd; margin-bottom: 2px;}
        .comment-date { font-size: 0.85em; color: #777; margin-bottom: 5px; }
        .comment-text { background-color: #f5f5f5; padding: 12px 15px; border-radius: 5px; word-wrap: break-word; line-height: 1.6; font-size: 0.95em; }
        .comment-form { margin-top: 30px; padding-top: 20px; border-top: 1px dashed #ccc;}
        .comment-form h4 { margin-bottom: 15px; font-weight: bold; font-size: 1.3em;}
        .comment-form .form-group { margin-bottom: 15px; }
        .comment-form textarea { min-height: 100px; font-size: 0.95em; }
        .comment-form .btn { margin-top: 10px; padding: 8px 20px; }
        @media (max-width: 767px) { .product-info { text-align: center; } .info-row { justify-content: center; } .info-label { width: auto; margin-right: 8px; } .product-actions { text-align: center; } .product-actions .btn { display: inline-block; margin-left: 5px; margin-right: 5px; } }
    </style>
</head>
<body>

<?php // <<< KHUNG BAO NGOÀI >>> ?>
<div id="main-container">

    <?php /* --- HEADER 2 BAO GỒM MENU CHÍNH --- */ ?>
    <?php include_once ("header2.php"); ?>

    <div class="container product-details-page">

        <?php if ($error_load): // Hiển thị lỗi nếu có ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error_load); ?></div>
        <?php elseif ($product_details_available): // Chỉ hiển thị nếu có dữ liệu sản phẩm $r ?>

            <div class="row">
                <?php /* --- CỘT HIỂN THỊ ẢNH SẢN PHẨM --- */ ?>
                <div class="col-md-6">
                    <div class="flexslider">
                        <ul class="slides">
                            <?php
                            $has_image_slide = false;
                            $image_dir = '../images/';
                            // Ảnh chính
                            if (!empty($r['urlHinh']) && file_exists($image_dir . $r['urlHinh'])) {
                                echo '<li><img src="' . $image_dir . htmlspecialchars($r['urlHinh']) . '" alt="'.htmlspecialchars($r['TenSP']).'"></li>';
                                $has_image_slide = true;
                            }
                            // Ảnh phụ
                            if ($result_img && mysqli_num_rows($result_img) > 0) {
                                mysqli_data_seek($result_img, 0);
                                while( $row_img = $result_img->fetch_assoc()) {
                                    if (!empty($row_img['urlHinh']) && file_exists($image_dir . $row_img['urlHinh'])) {
                                        echo '<li><img src="' . $image_dir . htmlspecialchars($row_img['urlHinh']) . '" alt="'.htmlspecialchars($r['TenSP']).' - Ảnh phụ"></li>';
                                        $has_image_slide = true;
                                    }
                                }
                                mysqli_free_result($result_img);
                            }
                            // Ảnh mặc định
                            if (!$has_image_slide) {
                                echo '<li><img src="../images/no_image_available.png" alt="Không có ảnh"></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>

                <?php /* --- CỘT HIỂN THỊ THÔNG TIN SẢN PHẨM --- */ ?>
                <div class="col-md-6 product-info">
                    <h2 class="product-title"><?php echo htmlspecialchars($r['TenSP']);?></h2>
                    <hr>

                    <div class="info-row">
                        <div class="info-label">Ngày cập nhật:</div>
                        <div class="info-value"><?php echo date("d/m/Y", strtotime($r['NgayCapNhat']));?></div>
                    </div>

                    <div class="info-row product-price">
                        <div class="info-label">Giá bán:</div>
                        <div class="info-value">
                            <?php
                            if (isset($r['GiaKhuyenmai']) && is_numeric($r['GiaKhuyenmai']) && $r['GiaKhuyenmai'] > 0 && $r['GiaKhuyenmai'] < $r['GiaBan']) {
                                echo '<span class="discount-price">' . number_format($r['GiaKhuyenmai']) . ' VNĐ</span>';
                                echo '<strike class="original-price">' . number_format($r['GiaBan']) . ' VNĐ</strike>';
                            } else {
                                echo '<span class="money">' . number_format($r['GiaBan']) . ' VNĐ</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Tình trạng:</div>
                        <div class="info-value stock-status <?php echo ($r['SoLuongTonKho'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php echo ($r['SoLuongTonKho'] > 0) ? "Còn hàng" : "Hết hàng"; ?>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Số lượng còn:</div>
                        <div class="info-value"><?php echo (int)$r['SoLuongTonKho']; ?></div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Lượt xem:</div>
                        <div class="info-value"><?php echo (int)$r['SoLanXem']; ?></div>
                    </div>

                    <hr>

                    <div class="product-actions">
                         <?php if ($r['SoLuongTonKho'] <= 0): ?>
                             <button type="button" class="btn btn-danger disabled" data-bs-toggle="modal" data-bs-target="#hetHangModal"> <i class="fas fa-times-circle"></i> Hết hàng </button>
                             <button type="button" class="btn btn-secondary disabled">Đặt hàng ngay</button>
                         <?php else: ?>
                              <a href="../site/GioHang.php?action=add&idSP=<?php echo $idSP; ?>" class="btn btn-primary">
                                  <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                              </a>
                              <a href="../site/ThanhToan.php?buy_now=1&idSP=<?php echo $idSP; ?>" class="btn btn-success">
                                  <i class="fas fa-check"></i> Đặt hàng ngay
                              </a>
                         <?php endif; ?>
                    </div>
                     <hr>
                </div>
            </div> <?php // Đóng thẻ row của ảnh và thông tin ?>

             <?php /* --- PHẦN MÔ TẢ VÀ CHI TIẾT (NẾU CÓ) --- */ ?>
             <div class="row mt-4">
                 <div class="col-xs-12 product-description-wrapper">
                     <?php if (!empty($r['MoTa'])): ?>
                         <div class="product-description">
                             <h4><strong>Mô tả sản phẩm:</strong></h4>
                             <div class="prod-box">
                                 <?php echo $r['MoTa']; ?>
                             </div>
                         </div>
                     <?php endif; ?>

                     <?php if (!empty($r['MoTa']) && !empty($r['NoiDung'])) echo '<hr style="margin: 25px 0;">'; ?>

                     <?php if (!empty($r['NoiDung'])): ?>
                         <div class="product-details">
                             <h4><strong>Chi tiết sản phẩm:</strong></h4>
                             <div class="prod-box">
                                 <?php echo $r['NoiDung']; ?>
                             </div>
                         </div>
                     <?php endif; ?>
                 </div>
             </div>


            <?php /* --- PHẦN BÌNH LUẬN --- */ ?>
            <div class="row">
                <div class="col-xs-12 comment-section">
                    <h3>Bình luận</h3>
                    <?php
                    // Hiển thị các bình luận đã duyệt
                    if ($rs_cm && mysqli_num_rows($rs_cm) > 0) {
                        mysqli_data_seek($rs_cm, 0);
                        while($row_cm = $rs_cm->fetch_assoc()){
                    ?>
                        <div class="comment-item">
                            <div class="comment-avatar">
                                <img src="../images/avatar-icon.png" alt="Avatar">
                            </div>
                            <div class="comment-content">
                                <div class="comment-author"><?php echo htmlspecialchars($row_cm['hoten']);?></div>
                                <div class="comment-date">
                                    <i><?php echo date("d/m/Y", strtotime($row_cm['ngay_comment'])); // <<< CHỈ HIỂN THỊ NGÀY/THÁNG/NĂM ?></i>
                                </div>
                                <div class="comment-text">
                                    <?php echo nl2br(htmlspecialchars($row_cm['noidung'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php
                        } // end while comment
                        if(isset($rs_cm) && $rs_cm) mysqli_free_result($rs_cm);
                    } else {
                        echo "<p>Chưa có bình luận nào cho sản phẩm này.</p>";
                    }
                    ?>

                    <?php /* --- Form bình luận --- */ ?>
                    <div class="comment-form" id="comment-form-anchor">
                        <h4>Viết bình luận của bạn</h4>
                        <?php
                            if(isset($_GET['comment']) && $_GET['comment'] == 'success') {
                                echo '<div class="alert alert-success">Gửi bình luận thành công, bình luận của bạn đang chờ duyệt!</div>';
                            } elseif (!empty($comment_error)) {
                                echo '<div class="alert alert-danger">'.htmlspecialchars($comment_error).'</div>';
                            }
                         ?>
                        <?php if(isset($_SESSION['Username'])): ?>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?idSP=" . $idSP; ?>">
                                <div class="form-group mb-3">
                                    <label for="comment" class="form-label visually-hidden">Nội dung bình luận:</label>
                                    <textarea name="comment" id="comment" rows="4" class="form-control" required placeholder="Nhập bình luận của bạn..."></textarea>
                                </div>
                                <div class="form-group text-end">
                                    <button type="submit" name="binhluan" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Gửi bình luận
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p>Bạn cần <a href="DangNhap.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">đăng nhập</a> để viết bình luận.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php else: // Trường hợp không có $product_details_available ?>
            <div class="alert alert-warning text-center">Không thể tải thông tin sản phẩm.</div>
        <?php endif; ?>

    </div> <?php // Đóng thẻ .container product-details-page ?>

    <?php /* --- FOOTER NẰM BÊN TRONG KHUNG BAO NGOÀI --- */ ?>
    <?php include_once ("footer.php");?>

<?php // <<< ĐÓNG THẺ KHUNG BAO NGOÀI >>> ?>
</div> <?php // Đóng thẻ #main-container ?>


<?php /* --- MODAL HẾT HÀNG (GIỮ NGUYÊN) --- */ ?>
<div class="modal fade" id="hetHangModal" tabindex="-1" aria-labelledby="hetHangModalLabel" aria-hidden="true">
 <div class="modal-dialog modal-sm modal-dialog-centered">
   <div class="modal-content">
     <div class="modal-header"> <h5 class="modal-title" id="hetHangModalLabel">Thông báo</h5> <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> </div>
     <div class="modal-body text-center"> <p><strong>Sản phẩm này tạm hết hàng.<br>Bạn vui lòng chọn sản phẩm khác!</strong></p> </div>
     <div class="modal-footer"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button> </div>
   </div>
 </div>
</div>

<?php /* --- SCRIPT CHO FLEXSLIDER VÀ BOOTSTRAP (GIỮ NGUYÊN) --- */ ?>
<script type="text/javascript" src="../js/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="../js/bootstrap.min.js"></script> <?php // Đảm bảo dùng đúng version Bootstrap ?>
<script type="text/javascript" src="../js/flexslider/jquery.flexslider-min.js"></script>

<script type="text/javascript">
    $(window).on('load', function() {
      if ($('.flexslider').length > 0) {
          try {
              $('.flexslider').flexslider({
                  animation: "slide",
                  smoothHeight: true
              });
          } catch(e) {
              console.error("Lỗi khởi tạo FlexSlider:", e);
          }
      }
       const commentAlert = document.querySelector('.comment-form .alert');
       if(commentAlert) {
           setTimeout(() => {
               $(commentAlert).fadeOut('slow', function() { $(this).remove(); });
           }, 5000);
       }
    });
</script>

</body>
</html>