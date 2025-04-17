<?php
// Có thể có code PHP khác ở đầu file footer của bạn, hãy giữ lại nếu cần
?>
<style>
    /* --- TOÀN BỘ KHỐI STYLE NÊN ĐƯỢC CHUYỂN RA FILE CSS RIÊNG --- */
    /* Footer tổng thể */
    footer {
        /* Bạn có thể thêm padding ở đây nếu muốn khoảng cách */
        /* ví dụ: padding: 40px 0; */
    }

    /* Định dạng cho div.wrapper BÊN TRONG footer - ĐÃ SỬA */
    footer .wrapper {
        /* background-color: #F1A7B9; */  /* <<< ĐÃ VÔ HIỆU HÓA/XÓA */
        color: rgb(38, 3, 18);           /* Giữ màu chữ */
        /* border: 2px solid rgb(38, 3, 18); */ /* <<< ĐÃ VÔ HIỆU HÓA/XÓA */
        padding: 25px;                   /* Giữ padding nếu cần */
        /* border-radius: 5px; */        /* <<< ĐÃ VÔ HIỆU HÓA/XÓA */

        /* Giữ lại nếu muốn căn giữa và giới hạn chiều rộng nội dung footer */
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;

        overflow: hidden; /* Giữ lại để chứa các float con nếu có */
    }

    /* --- Các style cho phần tử bên trong wrapper (giữ nguyên) --- */
    footer .wrapper a {
        color: #1F75FE;
        text-decoration: none;
    }
    footer .wrapper a:hover {
        color: #0033cc;
        text-decoration: underline;
    }
    footer .wrapper ul {
        margin-top: 0;
        margin-bottom: 10px;
        padding-left: 0;
        list-style: none;
    }
    footer .wrapper ul li {
        font-family: Arial, sans-serif;
        font-size: 14px;
        margin-bottom: 8px;
        line-height: 1.5;
    }
    footer .wrapper ul li strong {
        font-weight: bold;
        display: block;
        margin-bottom: 10px;
        color: #330033;
        text-transform: uppercase;
    }
    footer .wrapper .buttom-email {
        white-space: nowrap;
        margin-top: 10px;
    }
    footer .wrapper .buttom-email input[type="email"] {
        background-color:rgb(255, 255, 255);
        color: #333;
        padding: 10px;
        border: 1px solid #ccc;
        width: calc(100% - 85px);
        display: inline-block;
        vertical-align: middle;
        box-sizing: border-box;
        height: 40px;
        border-radius: 3px 0 0 3px;
    }
    footer .wrapper .buttom-email input[type="button"] {
        background-color: #1F75FE;
        color: #fff;
        padding: 10px 15px;
        border: none;
        cursor: pointer;
        display: inline-block;
        vertical-align: middle;
        box-sizing: border-box;
        height: 40px;
        border-radius: 0 3px 3px 0;
        margin-left: -4px;
    }
    footer .wrapper .social-icons {
        margin-top: 15px;
    }
    footer .wrapper .social-icons img {
        width: 30px;
        height: 30px;
        margin-right: 8px;
        vertical-align: middle;
        opacity: 0.8;
        transition: opacity 0.2s ease-in-out;
    }
    footer .wrapper .social-icons a:hover img {
        opacity: 1;
    }
    footer .wrapper .social-icons span {
        display: inline-block;
    }
    footer .wrapper ul li[style*="margin-left"] {
        margin-left: 0 !important;
    }
    footer .wrapper ul li strong[style*="margin-left"] {
        margin-left: 0 !important;
    }
    /* --- KẾT THÚC KHỐI STYLE --- */
</style>

<?php
// Phần PHP echo HTML - Đã dọn dẹp thẻ thừa
// echo "</div> </div> </section> </div> <div class='row '> "; // <<< ĐÃ XÓA thẻ đóng thừa ở đầu
echo " <footer >
    <div class=\"wrapper clearfix\" >
        <ul class=\" col-lg-2 col-md-2 col-sm-2\">
             <li ><strong class=\"font-logo\"> Mỹ Phẩm</strong></li>
             <li>ĐC liên hệ: 51L3, Đường 3/2, P. Xuân Khánh, Q. Ninh Kiều, TP. Cần Thơ</li>
             <li>Showroom 1: <span style=\"color:#000\">88 Hẻm 50 - Đường 3/2 - TP. Cần Thơ</span></li>
             <li>Showroom 2: <span style=\"color:#000\">24 Hẻm 132 - Đường 3/2 - TP. Cần Thơ</span></li>
             <li><a href=\"\">Phân phối sỉ</a></li>
             <li><a href=\"them_lienhe.php\">Liên hệ</a></li>
         </ul>
         <ul class=\"col-lg-2 col-md-2 col-sm-2\">
             <li ><strong>Thông tin liên hệ</strong></li>
             <li>ĐT: (+84) 832623352  - 972443667 </li>
             <li>Email: <a href=\"mailto:tientc@gmail.com\">  tientc @gmail.com</a></li> ";
echo "       <li>Hotline P4: <span style=\"color:#000\">0832623352</span></li>
             <li>Hotline P3: <span style=\"color:#000\">0972443667</span></li>
         </ul>
         <ul class=\"col-lg-2 col-md-2 col-sm-2\">
             <li ><strong>Về chúng tôi</strong></li>
             <li><a href=\"GioiThieu.php\" title=\"Giới thiệu\">Giới thiệu</a></li>
             <li><a href=\"ChinhSachBaoMat.php\" title=\"Chính sách bảo mật\">Chính sách bảo mật</a></li>
             <li><a href=\"DieuKhoan.php\" title=\"Điều khoản và điều kiện\">Điều khoản và điều kiện</a></li>
             <li><a href=\"\" title=\"Cam kết đảm bảo chất lượng\">Cam kết đảm bảo chất lượng</a></li>
             <li><a href=\"\" title=\"Hỗ trợ\">Thanh toán</a></li>
             <li><a href=\"\" title=\"Hỗ trợ\">Hỗ trợ</a></li>
         </ul>
         <ul class=\"col-lg-2 col-md-2 col-sm-2\">
             <li ><strong>Kết nối</strong></li>
             <li>
                 <div class=\"buttom-email\">
                     <input type=\"email\" autocomplete=\"off\" id=\"email\" class=\"form-control\" value=\"\" placeholder=\"Đăng ký nhận mail\">
                     <input type=\"button\" id=\"btnEmail\" class=\"form-control\" value=\"Gửi\">
                 </div>
             </li>
             <li>
                 <div class=\"social-icons\">
                      "; // --- Giữ lại thay đổi của bạn (.jpg, bỏ G+) ---
echo "                <span><a href=\"#\" target=\"_blank\" rel=\"nofollow noopener noreferrer\" title=\"Facebook\" aria-label=\"Theo dõi trên Facebook\"><img src=\"../images/facebook.jpg\" alt=\"Facebook\"></a></span> ";
// Google Plus đã được bạn xóa
echo "                <span><a href=\"#\" target=\"_blank\" rel=\"nofollow noopener noreferrer\" title=\"Pinterest\" aria-label=\"Theo dõi trên Pinterest\"><img src=\"../images/pinterest.jpg\" alt=\"Pinterest\"></a></span> ";
echo "                <span><a href=\"#\" target=\"_blank\" rel=\"nofollow noopener noreferrer\" title=\"Twitter\" aria-label=\"Theo dõi trên Twitter\"><img src=\"../images/twitter.jpg\" alt=\"Twitter\"></a></span> ";
echo "                <span><a href=\"#\" target=\"_blank\" rel=\"nofollow noopener noreferrer\" title=\"Youtube\" aria-label=\"Theo dõi trên Youtube\"><img src=\"../images/youtube.jpg\" alt=\"Youtube\"></a></span> ";
echo "                <span><a href=\"#\" target=\"_blank\" rel=\"nofollow noopener noreferrer\" title=\"Youtube\" aria-label=\"Theo dõi trên Zalo\"><img src=\"../images/zalo.jpg\" alt=\"Zalo\"></a></span> ";
echo "                <span><a href=\"#\" target=\"_blank\" rel=\"nofollow noopener noreferrer\" title=\"Youtube\" aria-label=\"Theo dõi trên TikTok\"><img src=\"../images/tiktok.jpg\" alt=\"TikTok\"></a></span> ";
echo "             </div>
             </li>
         </ul>
      </div> </footer> "; // <<< ĐÃ XÓA thẻ đóng div.row thừa

// Phần HTML còn lại (nút back-to-top, script...)
echo " <div class=\"footer\"> <a class=\"btn-top\" href=\"javascript:void(0);\" title=\"Top\" style=\"display: inline;\"></a> </div>

"; // <<< ĐÃ XÓA thẻ đóng div div thừa
echo " <script defer src=\"../js/flexslider/jquery.flexslider-min.js\"></script>
<script src=\"../js/main.js\"></script>
<script type=\"text/javascript\">window.\$crisp=[];window.CRISP_WEBSITE_ID=\"bcadcffb-cbc8-4293-a926-5fa9db139fae\";(function(){d=document;s=d.createElement(\"script\");s.src=\"https://client.crisp.chat/l.js\";s.async=1;d.getElementsByTagName(\"head\")[0].appendChild(s);})();</script>
"; // Kết thúc chuỗi echo

?>