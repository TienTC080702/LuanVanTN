<?php
if (!isset($_SESSION)) {
    session_start();
    // ob_start(); // Bỏ ob_start() nếu không thực sự cần thiết hoặc gây lỗi
}

// Tính tổng số lượng sản phẩm trong giỏ hàng
$SLuong = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) { // Kiểm tra là mảng
    foreach ($_SESSION['cart'] as $list) {
        // Kiểm tra xem $list có phải là mảng và có 'qty' không
        if (is_array($list) && isset($list['qty'])) {
             $SLuong += (int)$list['qty']; // Ép kiểu int để đảm bảo là số
        }
    }
}

// Bắt đầu echo HTML cho header
echo "
    <header class=\"wrapper clearfix\">

    <div id=\"banner\">
        <div id=\"logo\"><a href=\"index.php\"><img src=\"../images/logo2.jpg\" width=\"100px\" height=\"100px\" alt=\"logo\"></a></div>
        "; // Link logo nên trỏ về trang chủ index.php
echo "
    </div>

    <nav id=\"topnav\" role=\"navigation\">
        <div class=\"menu-toggle\">Menu</div>
        <ul class=\"srt-menu\" id=\"menu-main-navigation\">
            <li class=\"current\"><a href=\"index.php\">TRANG CHỦ</a></li> "; // Đổi HOME PAGE thành TRANG CHỦ, link về index.php gốc
echo "
            <li><a href=\"../site/ds_baiviet.php\">TIN TỨC SẢN PHẨM</a></li>
            
            "; // *** DÒNG MỚI ĐƯỢC THÊM VÀO ***
echo "<li><a href=\"../site/tu_van_da.php\" title=\"Tìm sản phẩm phù hợp với làn da của bạn\">TƯ VẤN DA</a></li>";
echo "
            "; // *** KẾT THÚC DÒNG THÊM VÀO ***

// --- Menu Thành Viên (Conditional) ---
if (isset($_SESSION['HoTenK'])) {
    $nameuser = htmlspecialchars($_SESSION['HoTenK']); // Thêm htmlspecialchars để tránh XSS
    echo "
            <li>
                <a href=\"#\" style=\"color: #e44d26;\"><i class=\"bi bi-person-circle\"></i> <strong>$nameuser</strong></a> "; // Thêm icon và màu khác biệt
echo "
                <ul>
                    <li><a href=\"../site/DonHang.php\">Quản lý đơn hàng</a></li>
                    <li><a href=\"../site/Sua_TaiKhoan.php\">Thông tin tài khoản</a></li> "; // Đổi tên cho rõ
echo "
                    ";
    // Chỉ hiển thị link Quản trị nếu là Admin (ví dụ: IDUser=1)
    if (isset($_SESSION['IDUser']) && $_SESSION['IDUser'] == 1) {
        echo "<li><a href=\"../admin/index.php\" target=\"_blank\"> Quản trị Website</a></li>"; // Mở tab mới
    }
echo "
                    <li><a href=\"../site/DangXuat.php\">Đăng xuất</a></li>
                </ul>
            </li>";
} else {
    // Menu khi chưa đăng nhập
    echo "
            <li>
                <a href=\"#\">TÀI KHOẢN</a> "; // Đổi THÀNH VIÊN thành TÀI KHOẢN
echo "
                <ul>
                    <li><a href=\"../site/TaoTaiKhoan.php\">Đăng ký</a></li>
                    <li><a href=\"../site/DangNhap.php\">Đăng nhập</a></li>
                    "; // Bỏ link Đăng xuất ở đây vì chưa đăng nhập
echo "
                </ul>
            </li>";
}

// --- Menu Lọc Sản Phẩm ---
echo "
            <li>
                <a href=\"#\">LỌC SẢN PHẨM</a>
                <ul>
                    <li><a href=\"../site/index.php?index=1\">Tất cả sản phẩm</a></li> "; // Sửa text
echo "
                    <li><a href=\"../site/index.php?index=2\">Sản phẩm nổi bật</a></li>
                    <li><a href=\"../site/index.php?index=3\">Sản phẩm bán chạy</a></li>
                    <li><a href=\"../site/index.php?index=4\">Sản phẩm giảm giá</a></li>
                    <li><a href=\"../site/index.php?index=5\">Sản phẩm mới về</a></li>
                    <li><a href=\"../site/index.php?index=6\">Sản phẩm xem nhiều</a></li>
                    <li><a href=\"../site/index.php?index=7\">Giá giảm dần</a></li>
                    <li><a href=\"../site/index.php?index=8\">Giá tăng dần</a></li>
                </ul>
            </li>

            "; // --- Giỏ hàng ---
echo "
            <li>
                <div id=\"cart\">
                    "; // Link giỏ hàng không nên có idSP=1 cố định
echo "
                    <a href=\"../site/GioHang.php\" title=\"Xem giỏ hàng\">
                        <img src=\"../images/Cart.png\" width=\"30px\" height=\"30px\" alt=\"cart\">
                        <strong style=\"color: red;\">($SLuong)</strong> "; // Đổi màu số lượng
echo "
                    </a>
                </div>
            </li>
        </ul>
    </nav>
</header>

"; // Đóng thẻ header

// ----- PHẦN SLIDER NÊN ĐƯỢC CHUYỂN RA KHỎI FILE HEADER NÀY -----
// ----- VÀ ĐẶT VÀO FILE index.php HOẶC FILE CHỨA NỘI DUNG CHÍNH -----
echo "
  <div class=\"wrapper\">
      <div class='row' style='height: 20%';>
          <div class='col-md-12 col-lg-12'>
               <div class=\"flexslider\">
                   <ul class=\"slides\">
                       <li><img src=\"../images/bn1.jpg\" alt=\"Banner quảng cáo 1\"/></li> "; // <<< ĐÃ THÊM ALT
echo "                    <li><img src=\"../images/bn2.jpg\" alt=\"Banner quảng cáo 2\"/></li> "; // <<< ĐÃ THÊM ALT
echo "                    <li><img src=\"../images/bn3.jpg\" alt=\"Banner quảng cáo 3\"/></li> "; // <<< ĐÃ THÊM ALT
echo "                    <li><img src=\"../images/bn4.jpg\" alt=\"Banner quảng cáo 4\"/></li> "; // <<< ĐÃ THÊM ALT
echo "                </ul> "; // <<< ĐÃ SỬA LỖI CÚ PHÁP
echo "            </div>
           </div>
       </div>
  </div>
 </section>

 <section id=\"content\" class=\"wide-content\">
  <div class=\"row\">
      <div class=\"col-md-12 col-lg-12 col-lg-offset-0\">
 ";
// ----- KẾT THÚC PHẦN NÊN CHUYỂN RA KHỎI HEADER -----

?>