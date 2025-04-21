<?php
$tong1 = 0;
// --- Phần tính tổng doanh thu ---
// (Giữ nguyên phần tính toán của bạn, nhưng lưu ý về hiệu suất như đã đề cập)
$conn = mysqli_connect('localhost', 'root', '', 'cuahangmypham', 3306)
or die ('Không thể kết nối tới database');
$conn->set_charset("utf8");
date_default_timezone_set('Asia/Ho_Chi_Minh');

$sl_tk = "SELECT idDH FROM donhang WHERE DaXuLy > 0 AND DaXuLy < 4";
$rs_tk = mysqli_query($conn, $sl_tk);
$tong = 0;
$soluong = 0;

if ($rs_tk) {
    while ($r = $rs_tk->fetch_assoc()) {
        // Tối ưu hơn: Tính tổng tiền trực tiếp từ bảng donhang nếu cột TongTien đã lưu giá trị cuối cùng
        // Hoặc JOIN một lần thay vì loop
        // Giữ nguyên cách tính cũ của bạn để không thay đổi logic:
        $sl_ctdh = "SELECT SUM(SoLuong * Gia) AS TongTien FROM donhangchitiet WHERE idDH = " . $r['idDH']; // Giả sử Gia trong donhangchitiet là giá cuối cùng
        $rs = mysqli_query($conn, $sl_ctdh);
        if ($rs && $d = mysqli_fetch_assoc($rs)) {
            $tong += $d['TongTien'] ?? 0;
        }
         if($rs) mysqli_free_result($rs);

        $sl_sl = "SELECT SUM(SoLuong) AS TongSoLuong FROM donhangchitiet WHERE idDH = " . $r['idDH'];
        $rs_sl = mysqli_query($conn, $sl_sl);
         if ($rs_sl && $c = mysqli_fetch_assoc($rs_sl)) {
            $soluong += $c['TongSoLuong'] ?? 0;
        }
         if($rs_sl) mysqli_free_result($rs_sl);
    }
    mysqli_free_result($rs_tk);
}
$tong1 = number_format($tong);

// --- Kết thúc phần tính doanh thu ---


// --- Bắt đầu tạo HTML ---
// Sử dụng heredoc syntax để dễ nhìn hơn hoặc giữ nguyên echo nếu bạn muốn
echo "
<div class=\"page-container list-menu-view\">

    <div class=\"left-aside\">
        <div class=\"aside-branding\" style='background-color: whitesmoke; '>
            <a href=\"../admin/index.php\" class=\"logo\" title=\"Home\"><img
                    src=\"../images/logoad2.jpg\" alt=\"Home\"
                    class=\"img-responsive hidden-md\" style='margin-bottom: 50px;'></a>
            </div>
        <div id=\"ps_container\" class=\"ps-container ps-theme-default\" data-ps-id=\"\">
            <div class=\"left-navigation\">
                <ul class=\"list-accordion\">
                    <li class=\"mobile-userNav\"></li> <li>
                        <a href=\"../admin/index_ds_sp.php\">
                            <i class=\"fas fa-box-open\"></i> <span class=\"nav-label\"> SẢN PHẨM</span>
                        </a>
                    </li>
                    <li>
                        <a href=\"../admin/index_donhang.php\">
                            <i class=\"fas fa-shopping-cart\"></i>
                            <span class=\"nav-label\"> ĐƠN HÀNG</span>
                        </a>
                    </li>
                     <li class=\"dcjq-parent-li\"> <a href=\"../admin/index_ds_loaisp.php\" class=\"dcjq-parent\">
                            <i class=\"fas fa-tags\"></i>
                            <span class=\"nav-label\">LOẠI SẢN PHẨM</span>
                            </a>
                        </li>
                    <li>
                        <a href=\"../admin/index_nh.php\">
                            <i class=\"fas fa-copyright\"></i>
                            <span class=\"nav-label\"> NHÃN HIỆU</span>
                         </a>
                    </li>
                     <li>
                        <a href=\"../admin/index_sp_comment.php\">
                            <i class=\"fas fa-comments\"></i>
                            <span class=\"nav-label\"> BÌNH LUẬN</span>
                        </a>
                    </li>

                    <li>
                        <a href=\"../admin/ds_nhacungcap.php\">
                            <i class=\"fas fa-truck-loading\"></i> <span class=\"nav-label\"> NHÀ CUNG CẤP</span>
                        </a>
                    </li>
                    <li>
                        <a href=\"../admin/index_phieu_nhap.php\">
                            <i class=\"fas fa-file-import\"></i>
                            <span class=\"nav-label\"> PHIẾU NHẬP</span>
                        </a>
                    </li>
                    <li>
                        <a href=\"../admin/index_phieu_xuat.php\">
                             <i class=\"fas fa-file-export\"></i>
                            <span class=\"nav-label\"> PHIẾU XUẤT</span>
                        </a>
                    </li>
                    <li>
                        <a href=\"../admin/baiviet.php\">
                             <i class=\"fas fa-newspaper\"></i>
                            <span class=\"nav-label\"> BÀI VIẾT</span>
                        </a>
                    </li>
                     <li>
                        <a href=\"../admin/index_user.php\">
                            <i class=\"fas fa-users-cog\"></i>
                            <span class=\"nav-label\"> NGƯỜI DÙNG</span>
                        </a>
                    </li>
                    <li>
                        <a href=\"../admin/index_ptgh.php\">
                            <i class=\"fas fa-shipping-fast\"></i>
                            <span class=\"nav-label\"> PT GIAO HÀNG</span>
                        </a>
                    </li>
                    <li>
                        <a href=\"../admin/KhuyenMai.php\">
                            <i class=\"fas fa-gift\"></i>
                            <span class=\"nav-label\"> KHUYẾN MÃI</span>
                        </a>
                    </li>

                    <li>
                        <a href=\"../admin/quanly_voucher.php\">  <i class=\"fas fa-ticket-alt\"></i>      <span class=\"nav-label\"> MÃ GIẢM GIÁ </span>
                        </a>
                    </li>
                    <li>
                        <a href=\"../admin/index_pttt.php\">
                            <i class=\"fas fa-credit-card\"></i>
                            <span class=\"nav-label\"> PT THANH TOÁN</span>
                        </a>
                    </li>
                    <li>
                        <a href=\"../admin/thongke.php\">
                            <i class=\"fas fa-chart-pie\"></i>
                            <span class=\"nav-label\"> THỐNG KÊ</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class=\"ps-scrollbar-x-rail\" style=\"left: 0px; bottom: 0px;\"><div class=\"ps-scrollbar-x\" tabindex=\"0\" style=\"left: 0px; width: 0px;\"></div></div>
            <div class=\"ps-scrollbar-y-rail\" style=\"top: 0px; right: 0px;\"><div class=\"ps-scrollbar-y\" tabindex=\"0\" style=\"top: 0px; height: 0px;\"></div></div>
        </div>
    </div>

    <div class=\"page-content\">
        <header class=\"top-bar\">
            <div class=\"container-fluid top-nav\">
                <div class=\"row\">
                    <div class=\"col-md-5 col-sm-1 col-xs-2 user-name-main-block\">
                        <div>
                            <h2 style=\"color:orange; font-size: 1.2em; margin-top: 10px;\">
                                <marquee direction=\"left\" scrollamount=\"4\">
                                    TỔNG DOANH THU: <strong> $tong1 VNĐ</strong> &ensp;|&ensp; Đã bán: <strong>$soluong</strong> sản phẩm
                                </marquee>
                            </h2>
                        </div>
                        <div class=\"clearfix top-bar-action hidden-md hidden-lg\">
                            </div>
                    </div>

                    <div class=\"col-sm-3 col-xs-8 hidden-md hidden-lg text-center logo-block\">
                        <a href=\"#\" class=\"logo\" title=\"Home\"><img
                                src=\"../images/logo-mob.png\" width=\"185\"
                                alt=\"Home\" class=\"img-responsive\"></a>
                     </div>
                     <div class=\"col-md-7 col-sm-8 col-xs-12 responsive-fix\">
                         <div class=\"top-aside-right\">
                             <button type=\"button\" class=\"btn-navbar-toggle collapsed visible-xs-block\"
                                     data-toggle=\"collapse\" data-target=\"#navbar\" aria-expanded=\"false\"
                                     aria-controls=\"navbar\" title=\"Toggle navigation\">
                                 <span class=\"icon-bar\"></span> <span class=\"icon-bar\"></span>
                                 <span class=\"icon-bar\"></span>
                             </button>
                             <div id=\"navbar\" class=\"user-nav navbar-collapse collapse\">
                                 <ul>
                                     <li><a href=\"../index.php\" target=\"_blank\">TIẾN TC BEAUTY STORE</a></li> <li class=\"dropdown\">
                                         <a data-toggle=\"dropdown\" href=\"#\" class=\"clearfix dropdown-toggle\">
                                              Tiếng Việt <span class=\"caret\"></span>
                                         </a>
                                         <ul role=\"menu\" class=\"dropdown-menu fadeInUp\">
                                             <li><a href=\"#\">Tiếng Việt</a></li>
                                             <li><a href=\"#\">English</a></li>
                                         </ul>
                                     </li>
                                     <li><a href=\"../site/index.php?index=1\"> Trang chủ </a></li>
                                     <li><a href=\"../site/DangXuat.php\"> Đăng xuất</a></li>
                                 </ul>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </header>

         <div class=\"main-container\">
             <div class=\"container-fluid\">
                 <div class=\"row\">
                     <div class=\"col-md-12\">
                         "; // Giữ nguyên dấu nháy kép đóng ở cuối

?>