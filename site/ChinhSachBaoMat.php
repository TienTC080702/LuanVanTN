<?php
// Bất kỳ mã PHP nào cần chạy trước HTML
?>
<!DOCTYPE html>
<html lang="vi"> <?php // Thêm lang="vi" cho đúng chuẩn ?>

<head> <?php // Đổi <header> thành <head> vì đây là phần đầu của tài liệu HTML ?>
    <meta charset="UTF-8"> <?php // Đảm bảo hiển thị đúng tiếng Việt ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <?php // Cho responsive ?>
    <?php include_once ("header.php");?>
    <title>CHÍNH SÁCH BẢO MẬT - TIẾN TC BEAUTY STORE</title> <?php // Thêm tên cửa hàng vào title ?>
    <?php include_once ("header1.php");?>

    <style>
        /* Reset cơ bản */
        body, h1, h2, h3, p, ul, li { /* Thêm li vào reset nếu chưa có */
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif; /* Hoặc font bạn muốn */
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4; /* Màu nền nhẹ cho body */
        }

        /* Khung lớn bao quanh trang - ĐÃ MỞ RỘNG */
        .page-wrapper {
           /* max-width: 1100px; */ /* Giá trị cũ */
            max-width: 1200px; /* <<-- ĐÃ THAY ĐỔI TỪ 1100px LÊN 1200px */
            margin: 20px auto; /* Căn giữa và tạo khoảng cách trên dưới */
            background-color: #e9e4f0; /* Màu nền cho khung */
            padding: 20px; /* Khoảng đệm bên trong khung */
            box-shadow: 0 0 15px rgba(0,0,0,0.1); /* Đổ bóng nhẹ tạo hiệu ứng khung */
            border-radius: 8px; /* Bo góc nhẹ */
        }

        /* Khung chứa nội dung chính sách */
        .container {
            background-color: #fff; /* Nền trắng cho nội dung */
            padding: 30px 40px; /* Tăng padding cho nội dung */
            border-radius: 5px; /* Bo góc nhẹ */
            margin-bottom: 20px; /* Khoảng cách với footer nếu footer nằm trong page-wrapper */
            text-align: justify; /* Căn đều hai bên cho đẹp */
        }

        #main h1 {
            color: rgb(172, 102, 164);
            padding: 15px 0 25px 0; /* Tăng padding dưới */
            text-transform: capitalize; /* Đã viết hoa trong HTML, có thể bỏ capitalize nếu muốn */
            text-align: center;
            font-size: 2em; /* Tăng kích thước tiêu đề */
            border-bottom: 1px solid #eee; /* Thêm đường kẻ chân */
            margin-bottom: 25px; /* Khoảng cách dưới đường kẻ */
        }

        #main h2 {
            color: #555;
            margin-top: 30px; /* Khoảng cách trên của mục lớn */
            margin-bottom: 15px;
            font-size: 1.5em;
            border-left: 4px solid rgb(172, 102, 164); /* Tạo điểm nhấn bên trái */
            padding-left: 10px;
        }

        #main h3 {
            color: #666;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 1.2em;
            font-style: italic;
        }

        #main p, #main ul {
            margin-bottom: 15px; /* Khoảng cách giữa các đoạn văn / danh sách */
            color: #444; /* Màu chữ dễ đọc hơn */
        }

        #main strong {
             color: rgb(172, 102, 164); /* Làm nổi bật tên cửa hàng */
        }

        #main ul {
            list-style: disc; /* Kiểu danh sách */
            padding-left: 40px; /* Thụt vào cho danh sách */
        }

        #main li {
             margin-bottom: 8px; /* Khoảng cách giữa các mục trong danh sách */
             padding-left: 5px; /* Khoảng cách nhỏ giữa bullet và nội dung */
        }

        .date {
            text-align: right;
            margin: 0 0 20px 0; /* Điều chỉnh khoảng cách */
            padding: 0;
            font-size: 13px;
            color: #888;
            font-style: italic;
        }

        /* Không cần .indent nữa */

    </style>

</head>
<body>

<div class="page-wrapper"> <?php // Khung lớn bắt đầu ?>

    <?php include_once ("header2.php"); // Header nội dung trang (nếu có) ?>

    <div class="container">
        <div id="main" class="wrapper"> <?php // Giữ lại cấu trúc này nếu cần ?>
            <h1>CHÍNH SÁCH BẢO MẬT</h1>
            <p class="date"><i>06/05/2024</i></p>

            <p>Ch&agrave;o mừng qu&yacute; kh&aacute;ch đến với <strong>TIẾN TC BEAUTY STORE</strong>.</p>
            <p>Ch&uacute;ng t&ocirc;i t&ocirc;n trọng v&agrave; cam kết sẽ bảo mật những th&ocirc;ng tin mang t&iacute;nh ri&ecirc;ng tư của qu&yacute; kh&aacute;ch. Xin vui l&ograve;ng đọc bản ch&iacute;nh s&aacute;ch bảo mật dưới đ&acirc;y để hiểu hơn những cam kết bảo mật, nhằm t&ocirc;n trọng v&agrave; bảo vệ quyền lợi của người truy cập trang web.</p>
            <p>Bảo vệ dữ liệu c&aacute; nh&acirc;n v&agrave; g&acirc;y dựng được niềm tin cho qu&yacute; kh&aacute;ch l&agrave; vấn đề rất quan trọng với ch&uacute;ng t&ocirc;i.&nbsp;C&ocirc;ng ty sẽ kh&ocirc;ng chia sẻ th&ocirc;ng tin của kh&aacute;ch h&agrave;ng cho bất kỳ một C&ocirc;ng ty n&agrave;o kh&aacute;c ngoại trừ những C&ocirc;ng ty v&agrave; b&ecirc;n thứ ba c&oacute; li&ecirc;n quan trực tiếp đến việc giao h&agrave;ng m&agrave; kh&aacute;ch h&agrave;ng đ&atilde; mua tại website: https://tientcbeautystore.vn. Trong một v&agrave;i trường hợp đặc biệt, https://tientcbeautystore.vn c&oacute; thể bị y&ecirc;u cầu phải tiết lộ th&ocirc;ng tin c&aacute; nh&acirc;n cho mục địch thực thi ph&aacute;p luật. https://tientcbeautystore.vn cam kết tu&acirc;n thủ quy định bảo mật tại Điều 68 đến Điều 73 Nghị định 52/2013/NĐ-CP.</p>

            <h2>1. Thu thập th&ocirc;ng tin c&aacute; nh&acirc;n</h2>
            <p>https://tientcbeautystore.vn lu&ocirc;n tu&acirc;n thủ c&aacute;c quy định của ph&aacute;p luật về bảo mật th&ocirc;ng tin c&aacute; nh&acirc;n: kh&ocirc;ng chia sẻ hay trao đổi th&ocirc;ng tin c&aacute; nh&acirc;n của kh&aacute;ch h&agrave;ng thu thập tr&ecirc;n website: https://tientcbeautystore.vn cho một b&ecirc;n thứ ba n&agrave;o kh&aacute;c.</p>

            <h3>1.1. Mục đ&iacute;ch thu thập th&ocirc;ng tin c&aacute; nh&acirc;n của kh&aacute;ch h&agrave;ng tr&ecirc;n website https://tientcbeautystore.vn:</h3>
            <p>Để đảm bảo việc thanh to&aacute;n v&agrave; giao nhận h&agrave;ng h&oacute;a đ&uacute;ng th&ocirc;ng tin v&agrave; đảm bảo quyền lợi cho kh&aacute;ch h&agrave;ng như:</p>
            <ul>
                <li>X&aacute;c định địa chỉ th&ocirc;ng tin giao h&agrave;ng m&agrave; kh&aacute;ch h&agrave;ng đ&atilde; mua tại website: https://tientcbeautystore.vn.</li>
                <li>Th&ocirc;ng b&aacute;o về việc giao h&agrave;ng v&agrave; hỗ trợ kh&aacute;ch h&agrave;ng.</li>
                <li>Cung cấp c&aacute;c th&ocirc;ng tin li&ecirc;n quan đến sản phẩm của https://tientcbeautystore.vn.</li>
                <li>Xử l&yacute; đơn đặt h&agrave;ng v&agrave; cung cấp dịch vụ, th&ocirc;ng tin qua trang web của C&ocirc;ng ty theo y&ecirc;u cầu của kh&aacute;ch h&agrave;ng.</li>
                <li>Ngo&agrave;i ra, C&ocirc;ng ty sẽ sử dụng th&ocirc;ng tin c&aacute; nh&acirc;n của kh&aacute;ch h&agrave;ng cung cấp để hỗ trợ quản l&yacute; t&agrave;i khoản kh&aacute;ch h&agrave;ng, x&aacute;c nhận v&agrave; thực hiện c&aacute;c giao dịch t&agrave;i ch&iacute;nh li&ecirc;n quan đến c&aacute;c khoản thanh to&aacute;n trực tuyến của kh&aacute;ch h&agrave;ng.</li>
            </ul>

            <h3>1.2. Phạm vi sử dụng th&ocirc;ng tin:</h3>
            <p>Th&ocirc;ng tin c&aacute; nh&acirc;n của kh&aacute;ch h&agrave;ng chỉ được sử dụng nội bộ C&ocirc;ng ty để x&aacute;c nhận c&aacute;c th&ocirc;ng tin li&ecirc;n quan đến c&aacute;c giao dịch mua b&aacute;n h&agrave;ng h&oacute;a v&agrave; thanh to&aacute;n trực tuyến. Ngo&agrave;i ra, C&ocirc;ng ty c&oacute; thể chia sẻ t&ecirc;n, địa chỉ, số điện thoại, số Chứng minh nh&acirc;n d&acirc;n cho dịch vụ chuyển ph&aacute;t nhanh hoặc nh&agrave; cung cấp của C&ocirc;ng ty để c&oacute; thể giao h&agrave;ng cho kh&aacute;ch h&agrave;ng.</p>

            <h3>1.3. Thời gian lưu trữ th&ocirc;ng tin:</h3>
            <p>C&ocirc;ng ty sẽ lưu trữ Th&ocirc;ng tin của Kh&aacute;ch h&agrave;ng tối đa 60 ng&agrave;y đối với h&agrave;nh vi truy cập web (dữ liệu m&aacute;y t&iacute;nh) tr&ecirc;n website: https://tientcbeautystore.vn.</p>
            <p>Đối với c&aacute;c th&ocirc;ng tin c&aacute; nh&acirc;n C&ocirc;ng ty ch&uacute;ng t&ocirc;i sẽ x&oacute;a dữ liệu khi kh&aacute;ch h&agrave;ng y&ecirc;u cầu hoặc kh&aacute;ch h&agrave;ng c&oacute; thể tự đăng nhập v&agrave;o t&agrave;i khoản của Kh&aacute;ch h&agrave;ng tại website: https://tientcbeautystore.vn để x&oacute;a th&ocirc;ng tin của m&igrave;nh.</p>

            <h3>1.4. Địa chỉ của đơn vị thu thập v&agrave; quản l&yacute; th&ocirc;ng tin c&aacute; nh&acirc;n của kh&aacute;ch h&agrave;ng:</h3>
            <p>
                <strong>DOANH NGHIỆP TƯ NH&Acirc;N – TIẾN TC BEAUTY STORE</strong><br>
                Địa chỉ: 73 Nguyễn Huệ, Phường 2, TP, Vĩnh Long<br>
                Điện thoại: 0848.433.319
            </p>

            <h3>1.5. Phương thức v&agrave; c&ocirc;ng cụ để kh&aacute;ch h&agrave;ng tiếp cận v&agrave; chỉnh sữa dữ liệu c&aacute; nh&acirc;n của m&igrave;nh:</h3>
            <p>Người d&ugrave;ng c&oacute; thể đăng nhập v&agrave;o t&agrave;i khoản của m&igrave;nh tr&ecirc;n <strong>website: https://tientcbeautystore.vn</strong> v&agrave; d&ugrave;ng chức năng &ldquo;Chỉnh sửa&rdquo; để điều chỉnh hoặc x&oacute;a dữ liệu c&aacute; nh&acirc;n của m&igrave;nh.</p>

            <h2>2. Điều lệ kh&aacute;c về th&ocirc;ng tin c&aacute; nh&acirc;n</h2>
            <p>Ch&uacute;ng t&ocirc;i c&oacute; thể d&ugrave;ng th&ocirc;ng tin c&aacute; nh&acirc;n của qu&yacute; kh&aacute;ch để nghi&ecirc;n cứu thị trường, chi tiết sẽ được ẩn v&agrave; chỉ được d&ugrave;ng để thống k&ecirc;. Bất kỳ c&acirc;u trả lời cho khảo s&aacute;t hoặc thăm d&ograve; dư luận m&agrave; ch&uacute;ng t&ocirc;i cần qu&yacute; kh&aacute;ch l&agrave;m sẽ kh&ocirc;ng được chuyển cho b&ecirc;n thứ ba. C&acirc;u trả lời sẽ được lưu t&aacute;ch ri&ecirc;ng với địa chỉ thư điện tử m&agrave; qu&yacute; kh&aacute;ch đ&atilde; đăng k&yacute;.</p>
            <p>Qu&yacute; kh&aacute;ch sẽ nhận được th&ocirc;ng tin về ch&uacute;ng t&ocirc;i, trang web, sản phẩm, bản tin. Khi qu&yacute; kh&aacute;ch kh&ocirc;ng muốn nhận những th&ocirc;ng tin n&agrave;y, vui l&ograve;ng nhấn v&agrave;o li&ecirc;n kết từ chối trong bất kỳ thư điện tử ch&uacute;ng t&ocirc;i gửi cho qu&yacute; kh&aacute;ch, việc ngừng gửi th&ocirc;ng tin sẽ được thực hiện.</p>

            <h2>3. Cuộc thi</h2>
            <p>Trong bất k&igrave; cuộc thi n&agrave;o, ch&uacute;ng t&ocirc;i sẽ sử dụng dữ liệu để th&ocirc;ng b&aacute;o người chiến thắng v&agrave; quảng c&aacute;o ch&agrave;o h&agrave;ng.</p>

            <h2>4. Đối t&aacute;c thứ ba v&agrave; li&ecirc;n kết</h2>
            <p>Ch&uacute;ng t&ocirc;i c&oacute; thể chuyển th&ocirc;ng tin của qu&yacute; kh&aacute;ch cho c&aacute;c c&ocirc;ng ty th&agrave;nh vi&ecirc;n, hoặc cho c&aacute;c đại l&yacute; trong khu&ocirc;n khổ quy định của ch&iacute;nh s&aacute;ch bảo mật. V&iacute; dụ: ch&uacute;ng t&ocirc;i sẽ nhờ b&ecirc;n thứ ba giao h&agrave;ng, nhận tiền thanh to&aacute;n, ph&acirc;n t&iacute;ch dữ liệu, tiếp thị v&agrave; hỗ trợ dịch vụ kh&aacute;ch h&agrave;ng. Ch&uacute;ng t&ocirc;i c&oacute; thể trao đổi th&ocirc;ng tin với b&ecirc;n thứ ba với mục đ&iacute;ch chống gian lận v&agrave; giảm rủi ro t&iacute;n dụng. Trong khu&ocirc;n khổ Ch&iacute;nh s&aacute;ch bảo mật, ch&uacute;ng t&ocirc;i kh&ocirc;ng b&aacute;n hay tiết lộ dữ liệu c&aacute; nh&acirc;n của qu&yacute; kh&aacute;ch cho b&ecirc;n thứ ba m&agrave; kh&ocirc;ng được đồng &yacute; trước, trừ khi điều n&agrave;y l&agrave; cần thiết cho c&aacute;c điều khoản trong Ch&iacute;nh s&aacute;ch bảo mật hoặc ch&uacute;ng t&ocirc;i được y&ecirc;u cầu phải l&agrave;m như vậy theo quy định của Ph&aacute;p luật. Trang web c&oacute; thể bao gồm quảng c&aacute;o của b&ecirc;n thứ ba v&agrave; c&aacute;c li&ecirc;n kết đến c&aacute;c trang web kh&aacute;c hoặc khung của c&aacute;c trang web kh&aacute;c. Xin lưu &yacute; rằng ch&uacute;ng t&ocirc;i kh&ocirc;ng c&oacute; nhiệm vụ bảo mật th&ocirc;ng tin hay nội dung của b&ecirc;n thứ ba hay c&aacute;c trang web kh&aacute;c, hay bất kỳ b&ecirc;n thứ ba n&agrave;o m&agrave; ch&uacute;ng t&ocirc;i chuyển giao dữ liệu cho ph&ugrave; hợp với Ch&iacute;nh s&aacute;ch bảo mật.</p>
            <p>Hệ thống theo d&otilde;i h&agrave;nh vi của kh&aacute;ch h&agrave;ng được ch&uacute;ng t&ocirc;i sử dụng tr&ecirc;n k&ecirc;nh Hiển Thị Quảng C&aacute;o (v&iacute; dụ như Tiếp Thị Lại Kh&aacute;ch H&agrave;ng, hệ thống quản l&yacute; c&aacute;c chiến dịch quảng c&aacute;o DoubleClick, b&aacute;o c&aacute;o về nh&acirc;n khẩu, sở th&iacute;ch của kh&aacute;ch h&agrave;ng với c&ocirc;ng cụ Google Analytics...) c&oacute; thể thu thập được c&aacute;c th&ocirc;ng tin như độ tuổi, giới t&iacute;nh, sở th&iacute;ch v&agrave; số lần tương t&aacute;c với số lần xuất hiện của quảng c&aacute;o.</p>
            <p>Với t&iacute;nh năng c&agrave;i đặt quảng c&aacute;o, người d&ugrave;ng hoặc kh&aacute;ch h&agrave;ng c&oacute; thể lựa chọn tho&aacute;t ra khỏi t&iacute;nh năng theo d&otilde;i h&agrave;nh vi kh&aacute;ch h&agrave;ng của Google Analytics v&agrave; lựa chọn c&aacute;ch xuất hiện của k&ecirc;nh Hiển Thị Quảng C&aacute;o tr&ecirc;n Google.</p>
            <p>Website https://tientcbeautystore.vn v&agrave; c&aacute;c nh&agrave; cung cấp b&ecirc;n thứ ba, bao gồm Google c&oacute; thể sử dụng cookies của Google Analytics hoặc cookies của b&ecirc;n thứ ba (như DoubleClick) để thu thập th&ocirc;ng tin, tối ưu h&oacute;a v&agrave; phục vụ cho mục đ&iacute;ch quảng c&aacute;o dựa tr&ecirc;n lần truy cập trang web của người d&ugrave;ng trong qu&aacute; khứ.</p>

            <h2>5. Sử dụng Cookie</h2>
            <p>Cookie l&agrave; tập tin văn bản nhỏ c&oacute; thể nhận dạng t&ecirc;n truy cập duy nhất từ m&aacute;y t&iacute;nh của qu&yacute; kh&aacute;ch đến m&aacute;y chủ của ch&uacute;ng t&ocirc;i khi qu&yacute; kh&aacute;ch truy cập v&agrave;o c&aacute;c trang nhất định tr&ecirc;n trang web v&agrave; sẽ được lưu bởi tr&igrave;nh duyệt internet l&ecirc;n ổ cứng m&aacute;y t&iacute;nh của qu&yacute; kh&aacute;ch. Cookie được d&ugrave;ng để nhận dạng địa chỉ IP, lưu lại thời gian. Ch&uacute;ng t&ocirc;i d&ugrave;ng cookie để tiện cho qu&yacute; kh&aacute;ch v&agrave;o trang web (v&iacute; dụ: ghi nhớ t&ecirc;n truy cập khi qu&yacute; kh&aacute;ch muốn v&agrave;o thay đổi lại giỏ mua h&agrave;ng m&agrave; kh&ocirc;ng cần phải nhập lại địa chỉ thư điện tử của m&igrave;nh) v&agrave; kh&ocirc;ng đ&ograve;i hỏi bất kỳ th&ocirc;ng tin n&agrave;o về qu&yacute; kh&aacute;ch (v&iacute; dụ: mục ti&ecirc;u quảng c&aacute;o). Tr&igrave;nh duyệt của qu&yacute; kh&aacute;ch c&oacute; thể được thiết lập kh&ocirc;ng sử dụng cookie nhưng điều n&agrave;y sẽ hạn chế quyền sử dụng của qu&yacute; kh&aacute;ch tr&ecirc;n trang web. Ch&uacute;ng t&ocirc;i cam kết l&agrave; cookie kh&ocirc;ng bao gồm bất cứ chi tiết c&aacute; nh&acirc;n ri&ecirc;ng tư n&agrave;o v&agrave; an to&agrave;n với virus.</p>
            <p>Tr&igrave;nh duyệt n&agrave;y sử dụng Google Analytics, một dịch vụ ph&acirc;n t&iacute;ch trang web được cung cấp bởi Google, Inc. (&ldquo;Google&rdquo;). Google Analytics d&ugrave;ng cookie, l&agrave; những tập tin văn bản đặt trong m&aacute;y t&iacute;nh để gi&uacute;p trang web ph&acirc;n t&iacute;ch người d&ugrave;ng v&agrave;o trang web như thế n&agrave;o. Th&ocirc;ng tin được tổng hợp từ cookie sẽ được truyền tới v&agrave; lưu bởi Google tr&ecirc;n c&aacute;c m&aacute;y chủ tại Hoa Kỳ. Google sẽ d&ugrave;ng th&ocirc;ng tin n&agrave;y để đ&aacute;nh gi&aacute; c&aacute;ch d&ugrave;ng trang web của qu&yacute; kh&aacute;ch, lập b&aacute;o c&aacute;o về c&aacute;c hoạt động tr&ecirc;n trang web cho c&aacute;c nh&agrave; khai th&aacute;c trang web v&agrave; cung cấp c&aacute;c dịch vụ kh&aacute;c li&ecirc;n quan đến c&aacute;c hoạt động internet v&agrave; c&aacute;ch d&ugrave;ng internet. Google cũng c&oacute; thể chuyển giao th&ocirc;ng tin n&agrave;y cho b&ecirc;n thứ ba theo y&ecirc;u cầu của ph&aacute;p luật hoặc c&aacute;c b&ecirc;n thứ ba xử l&yacute; th&ocirc;ng tin tr&ecirc;n danh nghĩa của Google. Google sẽ kh&ocirc;ng kết hợp địa chỉ IP của qu&yacute; kh&aacute;ch với bất kỳ dữ liệu n&agrave;o kh&aacute;c m&agrave; Google đang giữ. Qu&yacute; kh&aacute;ch c&oacute; thể từ chối d&ugrave;ng cookie bằng c&aacute;ch chọn c&aacute;c thiết lập th&iacute;ch hợp tr&ecirc;n tr&igrave;nh duyệt của m&igrave;nh, tuy nhi&ecirc;n lưu &yacute; rằng điều n&agrave;y sẽ ngăn qu&yacute; kh&aacute;ch sử dụng triệt để chức năng của trang web. Bằng c&aacute;ch sử dụng trang web n&agrave;y, qu&yacute; kh&aacute;ch đ&atilde; đồng &yacute; cho Google xử l&yacute; dữ liệu về qu&yacute; kh&aacute;ch theo c&aacute;ch thức v&agrave; c&aacute;c mục đ&iacute;ch n&ecirc;u tr&ecirc;n.&nbsp;</p>

            <h2>6. Bảo mật</h2>
            <p>Ch&uacute;ng t&ocirc;i c&oacute; biện ph&aacute;p th&iacute;ch hợp về kỹ thuật v&agrave; an ninh để ngăn chặn truy cập tr&aacute;i ph&eacute;p; hoặc tr&aacute;i ph&aacute;p luật; hoặc mất m&aacute;t; hoặc ti&ecirc;u hủy hoặc thiệt hại cho th&ocirc;ng tin của qu&yacute; kh&aacute;ch. Khi thu thập dữ liệu tr&ecirc;n trang web, ch&uacute;ng t&ocirc;i thu thập chi tiết c&aacute; nh&acirc;n của qu&yacute; kh&aacute;ch tr&ecirc;n m&aacute;y chủ an to&agrave;n. Ch&uacute;ng t&ocirc;i d&ugrave;ng tường lửa cho m&aacute;y chủ. Khi thu thập chi tiết c&aacute;c thẻ thanh to&aacute;n điện tử, ch&uacute;ng t&ocirc;i d&ugrave;ng m&atilde; h&oacute;a để ngăn chặn hacker muốn giải m&atilde; th&ocirc;ng tin của qu&yacute; kh&aacute;ch.</p>
            <p>Ch&uacute;ng t&ocirc;i khuy&ecirc;n rằng qu&yacute; kh&aacute;ch kh&ocirc;ng n&ecirc;n đưa th&ocirc;ng tin chi tiết về việc thanh to&aacute;n với bất kỳ ai bằng e-mail, ch&uacute;ng t&ocirc;i kh&ocirc;ng chịu tr&aacute;ch nhiệm về những mất m&aacute;t qu&yacute; kh&aacute;ch c&oacute; thể g&aacute;nh chịu trong việc trao đổi th&ocirc;ng tin của qu&yacute; kh&aacute;ch qua internet hoặc thư điện tử.</p>
            <p>Qu&yacute; kh&aacute;ch tuyệt đối kh&ocirc;ng sử dụng bất kỳ chương tr&igrave;nh, c&ocirc;ng cụ hay h&igrave;nh thức n&agrave;o kh&aacute;c để can thiệp v&agrave;o hệ thống hay l&agrave;m thay đổi cấu tr&uacute;c dữ liệu. Nghi&ecirc;m cấm việc ph&aacute;t t&aacute;n, truyền b&aacute; hay cổ vũ cho bất kỳ hoạt động n&agrave;o nhằm can thiệp, ph&aacute; hoại hay x&acirc;m nhập v&agrave;o dữ liệu của hệ thống trang web. C&aacute;c vi phạm sẽ bị tước bỏ mọi quyền lợi cũng như sẽ bị truy tố trước ph&aacute;p luật nếu cần thiết.</p>
            <p>Mọi th&ocirc;ng tin giao dịch sẽ được bảo mật nhưng trong trường hợp cơ quan ph&aacute;p luật y&ecirc;u cầu, ch&uacute;ng t&ocirc;i sẽ buộc phải cung cấp những th&ocirc;ng tin n&agrave;y cho c&aacute;c cơ quan ph&aacute;p luật.</p>
            <p>C&aacute;c điều kiện, điều khoản v&agrave; nội dung của trang web n&agrave;y được điều chỉnh bởi luật ph&aacute;p Việt Nam v&agrave; t&ograve;a &aacute;n Việt Nam c&oacute; thẩm quyền xem x&eacute;t.</p>

            <h2>7. Quyền lợi kh&aacute;ch h&agrave;ng</h2>
            <p>Qu&yacute; kh&aacute;ch c&oacute; quyền y&ecirc;u cầu truy cập v&agrave;o dữ liệu c&aacute; nh&acirc;n của m&igrave;nh, c&oacute; quyền y&ecirc;u cầu ch&uacute;ng t&ocirc;i sửa lại những sai s&oacute;t trong dữ liệu của qu&yacute; kh&aacute;ch m&agrave; kh&ocirc;ng mất ph&iacute;. Bất cứ l&uacute;c n&agrave;o qu&yacute; kh&aacute;ch cũng c&oacute; quyền y&ecirc;u cầu ch&uacute;ng t&ocirc;i ngưng sử dụng dữ liệu c&aacute; nh&acirc;n của qu&yacute; kh&aacute;ch cho mục đ&iacute;ch tiếp thị.</p>

        </div></div><?php include_once ("footer.php"); // Footer nằm trong page-wrapper ?>

</div> <?php // Khung lớn kết thúc ?>

</body>
</html>