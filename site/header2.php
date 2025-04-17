<?php
// Đảm bảo session đã được khởi tạo Ở ĐẦU file chính include file này
// (Ví dụ: đặt session_start() ở đầu file index.php, MoTa_BaiViet.php...)
// Kiểm tra lại để chắc chắn, nhưng không nên gọi session_start() ở đây nếu đã gọi trước đó
if (session_status() == PHP_SESSION_NONE) {
    // Nếu session chưa được bắt đầu ở file chính, mới bắt đầu ở đây
    // Tuy nhiên, điều này có thể gây lỗi "headers already sent" nếu có output trước include file này
    // Tốt nhất là đảm bảo session_start() được gọi một lần duy nhất ở đầu file PHP chính.
    // session_start();
    // ob_start(); // ob_start cũng nên đặt ở đầu file chính nếu cần
}

// Tính toán số lượng giỏ hàng
$SLuong = 0;
// Thêm kiểm tra is_array để tránh lỗi nếu $_SESSION['cart'] không phải là mảng
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $list) {
        // Kiểm tra $list['qty'] tồn tại và là số trước khi cộng
        if (isset($list['qty']) && is_numeric($list['qty'])) {
             $SLuong += $list['qty'];
        }
    }
}
// Không cần else $SLuong = 0; vì đã khởi tạo $SLuong = 0; ở trên

?>
<header class="wrapper clearfix">
    <div id="banner">
        <?php // Link logo về trang chủ ?>
        <div id="logo"><a href="index.php"><img src="../images/logo2.jpg" width="100px" height="100px" alt="logo"></a></div>
    </div>

    <nav id="topnav" role="navigation">
        <div class="menu-toggle">Menu</div>
        <ul class="srt-menu" id="menu-main-navigation">
            <?php
                // Logic để xác định trang hiện tại (ví dụ đơn giản)
                // Bạn có thể cần logic phức tạp hơn tùy thuộc vào cấu trúc URL
                $current_page_script = basename($_SERVER['SCRIPT_NAME']);
                $is_homepage = ($current_page_script == 'index.php');
            ?>
            <li class="<?php echo $is_homepage ? 'current' : ''; ?>">
                <a href="index.php">TRANG CHỦ</a> <?php // Đã sửa ?>
            </li>
            <li class="<?php echo ($current_page_script == 'ds_baiviet.php') ? 'current' : ''; ?>">
                <a href="../site/ds_baiviet.php">TIN TỨC SẢN PHẨM</a>
            </li>
            <li class="<?php echo ($current_page_script == 'tu_van_da.php') ? 'current' : ''; ?>">
                <a href="tu_van_da.php">TƯ VẤN DA</a> <?php // Đã thêm ?>
            </li>

            <?php // Mục Tài khoản hoặc Tên User ?>
            <?php if (isset($_SESSION['HoTenK'])) : ?>
                <?php $nameuser = $_SESSION['HoTenK']; ?>
                <li>
                    <?php // Link tên user có thể trỏ đến trang sửa thông tin ?>
                    <a href="../site/Sua_TaiKhoan.php"><strong><?php echo htmlspecialchars($nameuser); ?></strong></a>
                    <ul>
                        <li><a href="../site/Sua_TaiKhoan.php">Sửa thông tin</a></li>
                        <?php // Chỉ hiển thị link Quản trị cho admin (ví dụ IDUser=1) ?>
                        <?php if (isset($_SESSION['IDUser']) && $_SESSION['IDUser'] == 1) : ?>
                            <li><a href="../admin/index.php">Quản trị</a></li>
                        <?php endif; ?>
                         <li><a href="../site/DangXuat.php">Đăng xuất</a></li>
                    </ul>
                </li>
            <?php else : // Nếu chưa đăng nhập ?>
                <li>
                    <a href="#">TÀI KHOẢN</a>
                    <ul>
                        <li><a href="../site/TaoTaiKhoan.php">Đăng ký</a></li>
                        <li><a href="../site/DangNhap.php">Đăng nhập</a></li>
                        <?php // Không hiển thị Đăng xuất ở đây khi chưa đăng nhập ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php // Mục Lọc sản phẩm - Đã sửa cấu trúc HTML và tên ?>
            <li>
                <a href="#">LỌC SẢN PHẨM</a>
                <ul>
                    <li><a href="../site/index.php?index=1">Tất cả</a></li>
                    <li><a href="../site/index.php?index=2">Sản phẩm nổi bật</a></li>
                    <li><a href="../site/index.php?index=3">Sản phẩm bán chạy</a></li>
                    <li><a href="../site/index.php?index=4">Sản phẩm giảm giá</a></li>
                    <li><a href="../site/index.php?index=5">Sản phẩm mới về</a></li>
                    <li><a href="../site/index.php?index=6">Sản phẩm xem nhiều</a></li>
                    <li><a href="../site/index.php?index=7">Giá giảm dần</a></li>
                    <li><a href="../site/index.php?index=8">Giá tăng dần</a></li>
                </ul>
            </li>

            <?php // Mục Giỏ hàng ?>
            <li>
                 <?php // Lưu ý: Dùng lại id="banner" và id="cart" có thể gây xung đột CSS/JS nếu bạn có style/script dựa vào ID này ?>
                <div style="display: inline-block; vertical-align: middle;"> <?php // Bọc lại để căn chỉnh tốt hơn nếu cần ?>
                     <a href="../site/GioHang.php" style="display: flex; align-items: center; text-decoration: none; color: inherit;"> <?php // Dùng flex để căn icon và số lượng ?>
                        <img src="../images/Cart.png" width="30px" height="30px" alt="cart" style="margin-right: 5px;">
                        <strong>(<?php echo (int)$SLuong; // Ép kiểu int để đảm bảo là số ?>)</strong>
                     </a>
                </div>
            </li>
        </ul>
    </nav></header><?php
// Bỏ phần echo thẻ div#main và section#content vì file header chỉ nên chứa phần header
// Phần này nên nằm trong file chính (index.php, MoTa_BaiViet.php...)
/*
<div id=\"main\" class=\"wrapper\">
    <section id=\"content\" class=\"wide-content\">
        <div class=\"row\">
            <div class=\"col-md-12 col-lg-12 col-lg-offset-0\">
";
*/

// ob_end_flush(); // Nếu dùng ob_start thì nên gọi ở cuối file PHP chính
?>