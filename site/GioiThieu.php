<?php
 // Bất kỳ logic PHP nào cần thiết trước khi xuất HTML
?>
<!DOCTYPE html>
<html lang="vi"> <?php // Đã thêm thuộc tính ngôn ngữ ?>

<head>
    <meta charset="UTF-8"> <?php // Thêm charset UTF-8 ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <?php // Thêm viewport ?>
    <?php // Giả sử header.php chứa các thẻ head chung khác ?>
    <?php include_once ("header.php");?>
    <title>Giới thiệu - TIẾN TC BEAUTY STORE</title> <?php // Tiêu đề cụ thể hơn ?>
    <?php // Giả sử header1.php chứa CSS/meta cụ thể khác (LƯU Ý: nếu header1.php cũng nạp CSS thì có thể gây xung đột) ?>
    <?php include_once ("header1.php");?>
    <?php // Link đến Google Fonts nếu bạn dùng font từ đó ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <?php // ---- BẮT ĐẦU CSS NHÚNG TRỰC TIẾP ---- ?>
    <style>
        /* --- Cài đặt Cơ bản & Font --- */
        body {
            font-family: 'Roboto', Arial, sans-serif; /* Sử dụng font Roboto, dự phòng Arial */
            line-height: 1.7; /* Tăng chiều cao dòng cho dễ đọc */
            background-color: #fff5f7; /* <<< Màu nền HỒNG NHẠT cho toàn bộ trang */
            color: #333a45; /* Màu chữ chính đậm hơn một chút */
            margin: 0;
            font-size: 16px; /* Tăng kích thước font cơ bản */
            -webkit-font-smoothing: antialiased; /* Làm mịn font trên Webkit */
            -moz-osx-font-smoothing: grayscale; /* Làm mịn font trên Firefox */
        }

        .container {
            max-width: 1140px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 15px;
            padding-right: 15px;
        }

        /* --- Khung Nội dung Chính --- */
        .page-frame {
            background-color: #ffffff; /* Nền trắng cho vùng nội dung bên trong */
            max-width: 1200px;
            margin: 40px auto; /* Khoảng cách trên/dưới so với mép trình duyệt */
            padding: 35px 40px; /* Padding bên trong khung */
            border: 1px solid #e8e8e8; /* Viền nhạt */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.07); /* Bóng đổ nhẹ */
            border-radius: 5px; /* Bo góc */
            overflow: hidden; /* Tránh nội dung tràn ra nếu có lỗi */
        }

        /* Đảm bảo container bên trong không dính sát mép khung */
        .content-container {
             padding-bottom: 40px; /* Khoảng cách trên footer */
        }


        /* --- Định dạng Nội dung Trang Giới thiệu --- */
        .about-us-content {
            text-align: left; /* Căn lề trái mặc định cho nội dung */
        }

        .about-us-content .page-title {
            color: rgb(202, 92, 129); /* Màu thương hiệu */
            padding-bottom: 20px;
            text-transform: capitalize;
            text-align: center;
            font-size: 2.2em; /* Kích thước tiêu đề */
            font-weight: 700; /* Đậm */
            margin-top: 10px;
            margin-bottom: 35px;
            border-bottom: 3px solid rgb(202, 92, 129); /* Gạch chân */
        }

        .about-us-content .publish-date {
            text-align: right;
            margin: -25px 0 30px 0; /* Điều chỉnh vị trí */
            font-size: 0.9em;
            color: #88909b;
            font-style: italic;
        }

        .about-us-content p {
            margin-bottom: 1.4em; /* Khoảng cách giữa các đoạn văn */
            text-align: justify; /* Căn đều 2 bên cho đẹp mắt */
            color: #454c57; /* Màu chữ đoạn văn */
            hyphens: auto; /* Tự động ngắt chữ nếu cần khi căn đều */
        }

        .about-us-content p strong {
             font-weight: 700;
             color: #333a45;
        }

        /* Danh sách lợi ích */
        .benefits-list {
            list-style-type: none; /* Bỏ dấu chấm mặc định */
            margin: 30px 0;
            padding-left: 0;
        }

        .benefits-list li {
            margin-bottom: 15px; /* Khoảng cách giữa các mục */
            padding-left: 30px; /* Tạo khoảng trống cho icon */
            position: relative;
            text-align: left;
            font-size: 1.05em; /* Hơi lớn hơn một chút */
        }

        /* Icon tick màu hồng cho danh sách */
        .benefits-list li::before {
            content: '\2713'; /* Mã unicode cho dấu tick */
            color: rgb(202, 92, 129); /* Màu thương hiệu */
            font-weight: bold;
            position: absolute;
            left: 5px;
            top: 1px; /* Điều chỉnh vị trí dọc nếu cần */
            font-size: 1.2em;
        }

        /* --- Slideshow --- */
        .store-slideshow {
            margin: 40px auto; /* Căn giữa và tạo khoảng cách */
            max-width: 90%; /* Giới hạn chiều rộng slideshow */
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }

        /* CSS cho FlexSlider (cần có CSS gốc của FlexSlider nữa) */
        .flexslider {
            border: none !important;
            margin: 0 !important;
            border-radius: 5px; /* Bo góc khớp với .store-slideshow */
            background: none; /* Bỏ nền mặc định nếu có */
        }

        .flexslider .slides img {
            width: 100%;
            display: block;
            border-radius: 0; /* Không cần bo góc ảnh nếu container đã bo */
        }

        /* --- Thông tin liên hệ --- */
        .contact-info {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e8e8e8; /* Đường kẻ phân cách */
            font-size: 1em;
        }
        .contact-info p {
            margin-bottom: 10px;
            text-align: left;
            color: #454c57;
        }
        .contact-info strong {
            display: inline-block;
            min-width: 75px; /* Căn chỉnh các nhãn */
            font-weight: 500;
            color: #333a45;
        }
        /* Định dạng link màu hồng thương hiệu */
        .contact-info a,
        .about-us-content a { /* Áp dụng cho cả link trong nội dung */
            color: rgb(202, 92, 129);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease; /* Hiệu ứng chuyển màu */
        }
        .contact-info a:hover,
        .about-us-content a:hover {
            color: #d65a8d; /* Màu hồng đậm hơn khi hover */
            text-decoration: underline;
        }


        /* --- Footer --- */
        /* Selector này (.site-footer) cần khớp với class/ID của thẻ footer trong file footer.php */
        .site-footer {
            padding: 25px 0 10px 0; /* Padding top */
            text-align: center;
            font-size: 0.95em;
            color: #777;
            border-top: 1px solid #e8e8e8; /* Đường kẻ phân cách */
            margin-top: 30px; /* Khoảng cách trên */
        }

        /* --- Responsive --- */
        @media (max-width: 768px) {
            body {
                font-size: 15px;
            }
            .page-frame {
                margin: 20px 15px; /* Giảm margin */
                padding: 20px; /* Giảm padding */
            }
            .about-us-content .page-title {
                font-size: 1.8em;
                margin-bottom: 25px;
                padding-bottom: 15px;
            }
            .about-us-content .publish-date {
                margin-top: -15px;
                margin-bottom: 20px;
            }
            .benefits-list li {
                padding-left: 25px;
                font-size: 1em;
            }
            .benefits-list li::before {
                left: 0; /* Icon sát lề trái */
            }
            .store-slideshow {
                max-width: 100%; /* Slideshow full width */
                margin: 30px 0;
            }
            .contact-info {
                margin-top: 30px;
                padding-top: 20px;
            }
            /* Bỏ căn đều trên mobile */
            .about-us-content p {
                text-align: left;
                hyphens: none; /* Tắt ngắt chữ tự động */
            }
        }

        @media (max-width: 480px) {
             .page-frame {
                margin: 15px 10px;
                padding: 15px;
            }
            .about-us-content .page-title {
                font-size: 1.6em;
            }
            .contact-info strong {
                min-width: 65px; /* Giảm nhẹ min-width */
            }
        }

        /* CSS cho FlexSlider controls (Ví dụ - cần CSS gốc của FlexSlider) */
        .flex-control-nav {
            bottom: 10px; /* Điều chỉnh vị trí dấu chấm điều hướng */
        }
        .flex-direction-nav a {
            height: 30px; /* Điều chỉnh kích thước mũi tên */
        }
    </style>
    <?php // ---- KẾT THÚC CSS NHÚNG TRỰC TIẾP ---- ?>

</head>

<body>
    <?php // Giả sử header2.php chứa banner/menu điều hướng hiển thị ở đầu trang ?>
    <?php include_once ("header2.php");?>

    <?php // ---- Bắt đầu Khung bao quanh nội dung chính ---- ?>
    <div class="page-frame">
        <div class="container content-container"> <?php // Container chứa nội dung chính ?>
            <div id="main" class="wrapper about-us-content"> <?php // Thêm class để định dạng riêng ?>
                <h1 class='page-title'>GIỚI THIỆU VỀ TIẾN TC BEAUTY STORE</h1>
                <?php // Lấy ngày hiện tại theo định dạng DD/MM/YYYY. Hoặc giữ ngày tĩnh nếu muốn. ?>
                <p class='publish-date'><i><?php echo date('d/m/Y'); ?></i></p>

                <p><strong>Thành lập từ tháng 3/2008, đến nay website <strong>TIẾN TC BEAUTY STORE</strong> là website uy tín chuyên cung cấp các sản phẩm trong lĩnh vực làm đẹp.</strong></p>
                <p><strong>Với mục tiêu tạo ra những trải nghiệm mua sắm trực tuyến tuyệt vời, <strong>TIẾN TC BEAUTY STORE</strong> luôn nỗ lực không ngừng nhằm nâng cao chất lượng dịch vụ. Khi mua hàng qua mạng hay trực tiếp tại showroom TIẾN TC BEAUTY STORE quý khách sẽ được hưởng các tiện ích như sau:</strong></p>

                <?php // Sử dụng danh sách không có thứ tự (ul) cho các lợi ích ?>
                <ul class="benefits-list">
                    <li>Dịch vụ chăm sóc khách hàng tận tình trước-trong-sau khi mua hàng, xuyên suốt 7 ngày/tuần, từ 9:00 đến 21:30</li>
                    <li>Mức giá cạnh tranh</li>
                    <li>Giao hàng miễn phí hoàn toàn đối với mỹ phẩm (đối với đơn hàng từ 300.000đ trong phạm vi TP. Cần Thơ và từ 500.000đ đối với đơn hàng giao đến các tỉnh thành khác thuộc Việt Nam)</li>
                    <li>Dịch vụ gói quà sang trọng hoàn toàn miễn phí</li>
                    <li>Tích lũy điểm tạo thẻ V.I.P ( giảm 10% đối với mỹ phẩm và 5% đối với các sản phẩm khác)</li>
                    <li>Uy tín trong giao dịch là phương châm mang đến sự thành công của TIẾN TC BEAUTY STORE</li>
                    <li>Chính sách bảo hành chu đáo lên đến 90 ngày với tất cả sản phẩm được TIẾN TC BEAUTY STORE bán ra</li>
                </ul>

                <p><strong>Showroom <strong>TIẾN TC BEAUTY STORE</strong> được đặt tại 88 Hẻm 50 - Đường 3/2 - TP. Cần Thơ hy vọng góp phần mang lại những trải nghiệm mua sắm tuyệt vời cho quý khách.</strong></p>

                <?php // --- Phần Slideshow --- ?>
                <section id="hero" class="store-slideshow clearfix">
                    <div class="wrapper">
                        <div class="row">
                            <?php // Giả định dùng class Bootstrap - điều chỉnh nếu cần ?>
                            <div class="col-md-10 col-md-offset-1 col-sm-12">
                                <div class="flexslider">
                                    <ul class="slides">
                                        <li><img src="../images/shop1.jpg" alt="Tiến TC Beauty Store - Hình 1" /></li>
                                        <li><img src="../images/shop2.jpg" alt="Tiến TC Beauty Store - Hình 2" /></li>
                                        <li><img src="../images/shop3.jpg" alt="Tiến TC Beauty Store - Hình 3" /></li>
                                        <li><img src="../images/shop4.jpg" alt="Tiến TC Beauty Store - Hình 4" /></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <?php // --- Phần Thông tin liên hệ --- ?>
                <div class="contact-info">
                    <p>Mọi thắc mắc xin vui lòng liên hệ:</p>
                    <p><strong>Hotline:</strong> <a href="tel:0832623352">0832.623.352</a></p>
                    <p><strong>Email:</strong> <a href="mailto:tychuot01278080008@gmail.com">tychuot01278080008@gmail.com</a></p>
                    <p><strong>Website:</strong> <a href="https://tientcbeautystore.vn" target="_blank" rel="noopener noreferrer">https://tientcbeautystore.vn</a></p>
                    <p><strong>Fanpage:</strong> <a href="https://www.facebook.com/tien.lethi.12764" target="_blank" rel="noopener noreferrer">https://www.facebook.com/tien.lethi.12764</a></p> <?php // Kiểm tra lại link Fanpage ?>
                    <p>Xin chân thành cảm ơn quý khách.</p>
                </div>

            </div></div><?php // ---- Phần Footer ---- ?>
        <?php // Giả sử footer.php tạo ra thẻ footer hoặc div có class .site-footer ?>
        <?php include_once ("footer.php");?>

    </div> <?php // ---- Kết thúc Khung bao quanh nội dung chính ---- ?>


    <?php // ---- Nhúng các tệp JS nếu footer.php không xử lý ---- ?>
    <?php // Ví dụ cần có jQuery và FlexSlider JS để slideshow hoạt động ?>
    <?php // <script src="path/to/jquery.min.js"></script> ?>
    <?php // <script src="path/to/jquery.flexslider.js"></script> ?>
    <?php // <script> ?>
    <?php // // Khởi tạo FlexSlider khi trang tải xong ?>
    <?php // $(window).on('load', function() { ?>
    <?php //   $('.flexslider').flexslider({ ?>
    <?php //     animation: "slide" ?>
    <?php //     // Thêm các tùy chọn khác của FlexSlider tại đây ?>
    <?php //   }); ?>
    <?php // }); ?>
    <?php // </script> ?>
</body>
</html>