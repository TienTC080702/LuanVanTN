<?php
// Bất kỳ mã PHP nào cần chạy trước HTML
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once ("header.php");?>
    <title>Chính Sách và Điều Khoản Sử Dụng - TIẾN TC BEAUTY STORE</title>
    <?php include_once ("header1.php");?>

    <style>
        /* Reset cơ bản */
        body, h1, h2, h3, p, ol, ul, li {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }

        /* Khung lớn bao quanh trang - ĐÃ MỞ RỘNG */
        .page-wrapper {
            max-width: 1200px; /* <<-- ĐÃ THAY ĐỔI TỪ 1100px LÊN 1200px */
            margin: 20px auto;
            background-color: #f0e4e9; /* Màu ví dụ cho trang Điều khoản */
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        /* Khung chứa nội dung chính sách */
        .container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: justify;
        }

        #main h1 {
            color: #dd0017;
            padding: 15px 0 25px 0;
            text-transform: capitalize;
            text-align: center;
            font-size: 2em;
            border-bottom: 1px solid #eee;
            margin-bottom: 25px;
        }

        #main h2 {
            color: #555;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.5em;
            border-left: 4px solid #dd0017;
            padding-left: 10px;
        }

        #main h3 {
            color: #666;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 1.2em;
            font-style: italic;
        }

        #main p, #main ol, #main ul {
            margin-bottom: 15px;
            color: #444;
        }

        #main strong {
             color: #c00;
             font-weight: bold;
        }

        #main ol {
            list-style-type: decimal;
            padding-left: 40px;
        }
        #main ul {
            list-style: disc;
            padding-left: 40px;
        }

        #main li {
             margin-bottom: 10px;
             padding-left: 5px;
        }

        .date {
            text-align: right;
            margin: 0 0 20px 0;
            padding: 0;
            font-size: 13px;
            color: #888;
            font-style: italic;
        }

    </style>

</head>
<body>

<div class="page-wrapper"> <?php // Khung lớn bắt đầu ?>

    <?php include_once ("header2.php"); ?>

    <div class="container">
        <div id="main" class="wrapper">
            <h1>CHÍNH SÁCH VÀ ĐIỀU KHOẢN SỬ DỤNG</h1>
            <p class="date"><i>05/05/2021</i></p>

            <h2>1. Điều khoản v&agrave; điều kiện</h2>
            <p>Ch&agrave;o mừng qu&yacute; kh&aacute;ch đến với <strong>TIẾN TC BEAUTY STORE</strong>.</p>
            <p>Xin vui l&ograve;ng lưu &yacute; một số điều khoản dưới đ&acirc;y trước khi sử dụng. C&aacute;c Điều Khoản v&agrave; Điều Kiện n&agrave;y &aacute;p dụng cho c&aacute;c sản phẩm của trang web cung cấp theo y&ecirc;u cầu giao dịch của qu&yacute; kh&aacute;ch. Khi đồng &yacute; giao dịch, quý kh&aacute;ch đ&atilde; chấp nhận sự r&agrave;ng buộc bởi c&aacute;c Điều Khoản v&agrave; Điều Kiện n&agrave;y với c&aacute;c kh&aacute;i niệm sau:</p>
            <ol>
                <li>"T&agrave;i khoản" nghĩa l&agrave; t&agrave;i khoản m&agrave; quý kh&aacute;ch cần c&oacute; để đăng nhập v&agrave;o trang web của ch&uacute;ng t&ocirc;i nếu quý kh&aacute;ch muốn mua h&agrave;ng.</li>
                <li>&ldquo;Kh&aacute;ch h&agrave;ng Th&agrave;nh vi&ecirc;n&rdquo; c&oacute; nghĩa l&agrave; người đại diện cho một tổ chức/ đơn vị c&oacute; giấy ph&eacute;p đăng k&yacute; kinh doanh. Cụ thể ở đ&acirc;y l&agrave; cửa h&agrave;ng t&uacute;i x&aacute;ch, c&aacute;c đại l&yacute;, cửa h&agrave;ng b&aacute;n lẻ h&agrave;ng ti&ecirc;u d&ugrave;ng.</li>
                <li>"Th&agrave;nh vi&ecirc;n" nghĩa l&agrave; c&aacute; nh&acirc;n mua h&agrave;ng tại trang web của ch&uacute;ng t&ocirc;i.</li>
                <li>"Th&ocirc;ng b&aacute;o" nghĩa l&agrave; thư điện tử của trang web để th&ocirc;ng b&aacute;o ch&uacute;ng t&ocirc;i đ&atilde; nhận được Đơn đặt h&agrave;ng của qu&yacute; kh&aacute;ch.</li>
                <li>&ldquo;Kh&aacute;ch h&agrave;ng&rdquo; nghĩa l&agrave; người mua h&agrave;ng tại trang web của ch&uacute;ng t&ocirc;i.</li>
                <li>"Ng&agrave;y l&agrave;m việc" nghĩa l&agrave; c&aacute;c ng&agrave;y trong tuần ngoại trừ Chủ Nhật hoặc bất k&igrave; ng&agrave;y lễ n&agrave;o ở Việt Nam.</li>
                <li>"X&aacute;c nhận đơn đặt h&agrave;ng" nghĩa l&agrave; ch&uacute;ng t&ocirc;i sẽ gửi thư điện tử cho quý kh&aacute;ch để x&aacute;c nhận về đơn đặt h&agrave;ng của quý kh&aacute;ch.</li>
                <li>"Đơn h&agrave;ng" nghĩa l&agrave; đơn h&agrave;ng do quý kh&aacute;ch đặt tr&ecirc;n trang web để mua sản phẩm từ ch&uacute;ng t&ocirc;i.</li>
                <li>"Quý kh&aacute;ch" nghĩa l&agrave; kh&aacute;ch h&agrave;ng đang sử dụng trang web.</li>
            </ol>

            <h2>2. Quy định về người sử dụng</h2>
            <ol>
                <li>Khi qu&yacute; kh&aacute;ch truy cập v&agrave;o trang web https://tientcbeautystore.vn c&oacute; nghĩa l&agrave; đ&atilde; đồng &yacute; với c&aacute;c Điều khoản v&agrave; Điều kiện của ch&uacute;ng t&ocirc;i. Những quy định v&agrave; điều kiện sử dụng, ch&uacute;ng t&ocirc;i c&oacute; quyền thay đổi, chỉnh sửa, th&ecirc;m hoặc lược bỏ bất kỳ phần n&agrave;o v&agrave;o bất cứ l&uacute;c n&agrave;o. C&aacute;c thay đổi c&oacute; hiệu lực ngay khi được đăng tr&ecirc;n trang web m&agrave; kh&ocirc;ng cần th&ocirc;ng b&aacute;o trước. V&agrave; khi qu&yacute; kh&aacute;ch tiếp tục sử dụng trang web, sau khi c&aacute;c thay đổi về Quy định v&agrave; Điều kiện được đăng tải, c&oacute; nghĩa l&agrave; qu&yacute; kh&aacute;ch chấp nhận với những thay đổi đ&oacute;. V&igrave; vậy, qu&yacute; kh&aacute;ch vui l&ograve;ng kiểm tra để cập nhật những thay đổi đ&oacute;.</li>
                <li>Th&agrave;nh vi&ecirc;n tham gia giao dịch tr&ecirc;n trang TMĐT https://tientcbeautystore.vn l&agrave; c&aacute;c c&aacute; nh&acirc;n hoặc người đại diện cho một đơn vị, tổ chức c&oacute; đầy đủ năng lực h&agrave;nh vi d&acirc;n sự hoặc truy cập dưới sự gi&aacute;m s&aacute;t của cha mẹ hay người gi&aacute;m hộ hợp ph&aacute;p.</li>
                <li>Ch&uacute;ng t&ocirc;i đưa ra c&aacute;c điều khoản sử dụng để qu&yacute; kh&aacute;ch c&oacute; thể mua sắm tr&ecirc;n web trong khu&ocirc;n khổ đ&atilde; đề ra.</li>
                <li>Qu&yacute; kh&aacute;ch kh&ocirc;ng được sử dụng bất kỳ phần n&agrave;o của trang web với mục đ&iacute;ch thương mại hoặc nh&acirc;n danh đối t&aacute;c thứ ba nếu kh&ocirc;ng c&oacute; văn bản thỏa thuận giữa ch&uacute;ng t&ocirc;i v&agrave; qu&yacute; kh&aacute;ch. Khi vi phạm những điều n&agrave;o trong đ&acirc;y, ch&uacute;ng t&ocirc;i sẽ hủy, hoặc tước c&aacute;c quyền lợi về th&agrave;nh vi&ecirc;n của qu&yacute; kh&aacute;ch m&agrave; kh&ocirc;ng cần b&aacute;o trước.</li>
                <li>Khi muốn sử dụng c&aacute;c dịch vụ tr&ecirc;n trang web, qu&yacute; kh&aacute;ch phải cung cấp th&ocirc;ng tin x&aacute;c thực cho ch&uacute;ng t&ocirc;i, v&agrave; phải cập nhật khi c&oacute; những thay đổi. Qu&yacute; kh&aacute;ch tự chịu tr&aacute;ch nhiệm với mật khẩu, t&agrave;i khoản v&agrave; hoạt động của m&igrave;nh tr&ecirc;n trang web. Ngo&agrave;i ra, qu&yacute; kh&aacute;ch phải th&ocirc;ng b&aacute;o cho ch&uacute;ng t&ocirc;i biết khi t&agrave;i khoản bị truy cập tr&aacute;i ph&eacute;p. Ch&uacute;ng t&ocirc;i kh&ocirc;ng chịu bất kỳ tr&aacute;ch nhiệm n&agrave;o đối với những thiệt hại hoặc mất m&aacute;t xảy ra, do qu&yacute; kh&aacute;ch kh&ocirc;ng tu&acirc;n thủ c&aacute;c quy định v&agrave; c&aacute;c điều khoản.</li>
                <li>Trong suốt qu&aacute; tr&igrave;nh đăng k&yacute;, ch&uacute;ng t&ocirc;i sẽ gửi bản tin v&agrave; những th&ocirc;ng tin quảng c&aacute;o qua thư điện tử. Sau đ&oacute;, nếu kh&ocirc;ng muốn tiếp tục nhận những th&ocirc;ng tin n&agrave;y, qu&yacute; kh&aacute;ch c&oacute; thể từ chối bằng c&aacute;ch nhấp v&agrave;o đường li&ecirc;n kết ở dưới c&ugrave;ng trong mọi thư điện tử m&agrave; ch&uacute;ng t&ocirc;i gửi tới.</li>
            </ol>

            <h2>3. Điều khoản về đơn h&agrave;ng v&agrave; gi&aacute; cả</h2>
            <ol>
                <li>Ch&uacute;ng t&ocirc;i c&oacute; quyền từ chối, hoặc hủy đơn h&agrave;ng của qu&yacute; kh&aacute;ch v&igrave; những l&yacute; do ph&aacute;t sinh. Ch&uacute;ng t&ocirc;i c&oacute; thể hỏi th&ecirc;m về số điện thoại v&agrave; địa chỉ trước khi nhận hoặc hủy đơn h&agrave;ng.</li>
                <li>Ch&uacute;ng t&ocirc;i cam kết đưa đ&uacute;ng th&ocirc;ng tin v&agrave; gi&aacute; cả về c&aacute;c sản phẩm tr&ecirc;n trang web. Tuy nhi&ecirc;n, đ&ocirc;i l&uacute;c vẫn c&oacute; sai s&oacute;t xảy ra, v&iacute; dụ như trường hợp gi&aacute; sản phẩm kh&ocirc;ng hiển thị ch&iacute;nh x&aacute;c hoặc sai gi&aacute; tr&ecirc;n trang web. T&ugrave;y theo từng trường hợp ch&uacute;ng t&ocirc;i sẽ li&ecirc;n hệ hướng dẫn hoặc th&ocirc;ng b&aacute;o hủy đơn h&agrave;ng đ&oacute; cho qu&yacute; kh&aacute;ch.</li>
            </ol>

            <h2>4. C&aacute;ch h&igrave;nh th&agrave;nh Hợp Đồng</h2>
             <ol>
                 <li>Khi đặt h&agrave;ng, quý kh&aacute;ch phải cung cấp cho ch&uacute;ng t&ocirc;i những th&ocirc;ng tin ch&iacute;nh x&aacute;c, v&agrave; quý kh&aacute;ch phải theo hướng dẫn tr&ecirc;n trang web về c&aacute;ch đặt h&agrave;ng.</li>
                 <li>Khi đ&atilde; chọn sản phẩm v&agrave; đặt h&agrave;ng, quý kh&aacute;ch sẽ được th&ocirc;ng b&aacute;o những chi ph&iacute; phải trả, bao gồm thuế gi&aacute; trị h&agrave;ng h&oacute;a v&agrave; dịch vụ nếu c&oacute;. Trừ khi c&oacute; quy định kh&aacute;c tr&ecirc;n trang web, mọi chi ph&iacute; sẽ được trả bằng tiền Việt Nam Đồng, đang c&oacute; hiệu lực.</li>
                 <li>Quý kh&aacute;ch sẽ phải thanh to&aacute;n đủ số tiền tại thời điểm đặt h&agrave;ng bằng một trong c&aacute;c phương thức thanh to&aacute;n được ch&uacute;ng t&ocirc;i y&ecirc;u cầu để tiến h&agrave;nh đơn h&agrave;ng của quý kh&aacute;ch. V&agrave; trong bất cứ trường hợp n&agrave;o ch&uacute;ng t&ocirc;i sẽ kh&ocirc;ng giao h&agrave;ng trước khi quý kh&aacute;ch đ&atilde; ho&agrave;n tất việc thanh to&aacute;n. Thời gian hợp đồng c&oacute; hiệu lực được t&iacute;nh từ l&uacute;c qu&yacute; kh&aacute;ch thanh to&aacute;n đầy đủ cho ch&uacute;ng t&ocirc;i.</li>
                 <li>Nếu quý kh&aacute;ch được hỏi th&ocirc;ng tin về thẻ thanh to&aacute;n, quý kh&aacute;ch phải l&agrave; người c&oacute; đầy đủ quyền sử dụng thẻ hoặc t&agrave;i khoản thanh to&aacute;n đ&oacute;. Thẻ hoặc t&agrave;i khoản đ&oacute; phải c&oacute; đủ tiền để trả c&aacute;c khoản thanh to&aacute;n đề xuất cho ch&uacute;ng t&ocirc;i. Ch&uacute;ng t&ocirc;i c&oacute; quyền x&aacute;c nhận th&ocirc;ng tin thanh to&aacute;n của quý kh&aacute;ch trước khi cung cấp sản phẩm.</li>
                 <li>Khi quý kh&aacute;ch gửi đơn h&agrave;ng tr&ecirc;n trang web, quý kh&aacute;ch đ&atilde; đồng ý với những Điều Khoản v&agrave; Điều Kiện n&agrave;y tại thời điểm đặt h&agrave;ng. Quý kh&aacute;ch c&oacute; tr&aacute;ch nhiệm xem lại những Điều Khoản v&agrave; Điều Kiện mới nhất v&agrave;o mỗi lần đặt h&agrave;ng.</li>
                 <li>Hợp Đồng sẽ được h&igrave;nh th&agrave;nh v&agrave; ch&uacute;ng t&ocirc;i sẽ bị r&agrave;ng buộc ph&aacute;p lý phải cung cấp sản phẩm cho quý kh&aacute;ch khi ch&uacute;ng t&ocirc;i đ&atilde; chấp nhận đơn h&agrave;ng của quý kh&aacute;ch. Sự chấp nhận sẽ được th&ocirc;ng b&aacute;o r&otilde; r&agrave;ng cho quý kh&aacute;ch qua thư điện tử, dưới h&igrave;nh thức văn bản t&ecirc;n l&agrave; &ldquo;X&aacute;c Nhận Đơn H&agrave;ng&rdquo; n&oacute;i r&otilde; l&agrave; ch&uacute;ng t&ocirc;i chấp nhận đơn h&agrave;ng của quý kh&aacute;ch. Thư X&aacute;c Nhận Đơn H&agrave;ng của ch&uacute;ng t&ocirc;i sẽ c&oacute; hiệu lực khi n&oacute; được gửi bởi ch&uacute;ng t&ocirc;i. Ch&uacute;ng t&ocirc;i sẽ gửi h&oacute;a đơn đến quý kh&aacute;ch khi ch&uacute;ng t&ocirc;i giao h&agrave;ng. Cho đến l&uacute;c chấp nhận đơn h&agrave;ng của quý kh&aacute;ch, ch&uacute;ng t&ocirc;i c&oacute; quyền từ chối tiến h&agrave;nh đơn h&agrave;ng. Nếu ch&uacute;ng t&ocirc;i hủy đơn h&agrave;ng trước khi ch&uacute;ng t&ocirc;i chấp nhận đơn h&agrave;ng đ&oacute;, ch&uacute;ng t&ocirc;i sẽ nhanh ch&oacute;ng ho&agrave;n trả bất k&igrave; số tiền n&agrave;o đ&atilde; được quý kh&aacute;ch đ&atilde; thanh to&aacute;n bằng thẻ t&iacute;n dụng hoặc thẻ ghi nợ cho đơn h&agrave;ng.</li>
                 <li>Nếu quý kh&aacute;ch muốn thay đổi đơn h&agrave;ng của m&igrave;nh sau khi đ&atilde; gửi tới trang web, xin vui l&ograve;ng li&ecirc;n hệ với ch&uacute;ng t&ocirc;i ngay lập tức. Tuy nhi&ecirc;n, ch&uacute;ng t&ocirc;i kh&ocirc;ng đảm bảo rằng ch&uacute;ng t&ocirc;i c&oacute; thể sửa đổi đơn h&agrave;ng theo y&ecirc;u cầu của quý kh&aacute;ch.</li>
                 <li>Ch&uacute;ng t&ocirc;i cố gắng hết sức để đảm bảo rằng gi&aacute; cả ch&uacute;ng t&ocirc;i đưa ra l&agrave; ch&iacute;nh x&aacute;c, nhưng gi&aacute; trị đơn h&agrave;ng của quý kh&aacute;ch sẽ được ch&uacute;ng t&ocirc;i x&aacute;c nhận lại v&igrave; đ&oacute; l&agrave; một phần của thủ tục chấp nhận đơn h&agrave;ng của ch&uacute;ng t&ocirc;i. Nếu gi&aacute; của đơn h&agrave;ng thay đổi trước khi ch&uacute;ng t&ocirc;i chấp nhận đơn h&agrave;ng của quý kh&ach;, ch&uacute;ng t&ocirc;i sẽ li&ecirc;n lạc với quý kh&aacute;ch để x&aacute;c nhận rằng quý kh&aacute;ch vẫn muốn tiếp tục mua h&agrave;ng ở mức gi&aacute; đ&atilde; sửa đổi.</li>
                 <li>Hợp đồng sẽ chỉ điều chỉnh đến những sản phẩm m&agrave; ch&uacute;ng t&ocirc;i đ&atilde; x&aacute;c nhận trong thư X&aacute;c Nhận Đơn H&agrave;ng. Ch&uacute;ng t&ocirc;i sẽ kh&ocirc;ng cung cấp bất cứ sản phẩm n&agrave;o cho đến khi ch&uacute;ng t&ocirc;i gửi thư X&aacute;c Nhận Đơn H&agrave;ng c&oacute; đề cập đến những sản phẩm đ&oacute;.</li>
             </ol>

            <h2>5. Giao h&agrave;ng</h2>
             <ol>
                 <li>Ch&uacute;ng t&ocirc;i sẽ giao sản phẩm đến địa điểm m&agrave; quý kh&aacute;ch h&agrave;ng y&ecirc;u cầu trong đơn đặt h&agrave;ng.</li>
                 <li>Qu&yacute; kh&aacute;ch c&oacute; thể lựa chọn phương thức giao h&agrave;ng nhanh hoặc giao h&agrave;ng thường. Để biết th&ocirc;ng tin chi tiết về chi ph&iacute; vận chuyển, qu&yacute; kh&aacute;ch vui l&ograve;ng tham khảo ở phần Ch&iacute;nh s&aacute;ch Giao nhận h&agrave;ng.</li>
                 <li>Ch&uacute;ng t&ocirc;i sẽ giao h&agrave;ng trong khoản thời gian m&agrave; ch&uacute;ng t&ocirc;i đ&atilde; định trước cho quý kh&aacute;ch tại thời điểm đặt h&agrave;ng (hoặc sẽ được cập nhật lại trong thư X&aacute;c Nhận Đơn H&agrave;ng) chứ ch&uacute;ng t&ocirc;i sẽ kh&ocirc;ng thể x&aacute;c định được ng&agrave;y giao h&agrave;ng ch&iacute;nh x&aacute;c khi qu&yacute; kh&aacute;ch đặt h&agrave;ng. Ch&uacute;ng t&ocirc;i lu&ocirc;n cố gắng giao h&agrave;ng trong khoảng thời gian ngắn nhất, t&ugrave;y v&agrave;o địa điểm v&agrave; phương thức giao h&agrave;ng m&agrave; qu&yacute; kh&aacute;ch lựa chọn.</li>
                 <li>Qu&yacute; kh&aacute;ch c&oacute; thể chỉ định đối tượng nhận h&agrave;ng. Tuy nhi&ecirc;n, qu&yacute; kh&aacute;ch phải chịu tr&aacute;ch nhiệm khi c&oacute; bất kỳ ph&aacute;t sinh n&agrave;o sau khi h&agrave;ng h&oacute;a trong đơn h&agrave;ng được giao cho người chỉ định đ&oacute;.</li>
                 <li>Ch&uacute;ng t&ocirc;i sẽ cho quý kh&aacute;ch biết nếu như ch&uacute;ng t&ocirc;i c&oacute; sự chậm trễ trong việc giao h&agrave;ng, ch&uacute;ng t&ocirc;i sẽ kh&ocirc;ng chịu tr&aacute;ch nhiệm cho bất cứ tổn thất, c&aacute;c khoản nợ, chi ph&iacute;, thiệt hại, hoặc cước ph&iacute; ph&aacute;t sinh từ việc giao h&agrave;ng trễ.</li>
                 <li>Khi h&agrave;ng được giao, quý kh&aacute;ch sẽ được y&ecirc;u cầu ký nhận h&agrave;ng. Quý kh&aacute;ch sẽ phải kiểm tra sản phẩm cho bất k&igrave; lỗi lầm hoặc hư hại n&agrave;o đ&oacute; trước khi ký nhận h&agrave;ng. Quý kh&aacute;ch n&ecirc;n giữ kỹ bi&ecirc;n lai ph&ograve;ng trường hợp sau n&agrave;y quý kh&aacute;ch cần n&oacute;i chuyện lại với ch&uacute;ng t&ocirc;i về sản phẩm đ&atilde; mua.</li>
                 <li>Xin quý kh&aacute;ch lưu ý rằng c&oacute; một số địa điểm ch&uacute;ng t&ocirc;i sẽ kh&ocirc;ng thể giao h&agrave;ng được. Nếu trường hợp đ&oacute; xảy ra, ch&uacute;ng t&ocirc;i sẽ th&ocirc;ng b&aacute;o cho quý kh&aacute;ch qua th&ocirc;ng tin li&ecirc;n lạc m&agrave; quý kh&aacute;ch đ&atilde; cung cấp khi đặt h&agrave;ng v&agrave; sắp xếp hủy đơn h&agrave;ng hoặc giao h&agrave;ng đến một địa chỉ kh&aacute;c.</li>
                 <li>Ch&uacute;ng t&ocirc;i g&oacute;i h&agrave;ng bằng bao b&igrave; chuẩn của ch&uacute;ng t&ocirc;i.</li>
                 <li>Quý kh&aacute;ch phải cẩn thận khi nhận sản phẩm để tr&aacute;nh l&agrave;m hỏng, đặc biệt l&agrave; khi sử dụng bất k&igrave; vật dụng sắc nhọn để mở bao b&igrave;.</li>
                 <li>Quý kh&aacute;ch phải đảm bảo rằng quý kh&aacute;ch sẵn s&agrave;ng nhận h&agrave;ng m&agrave; kh&ocirc;ng c&oacute; bất cứ sự chậm trễ n&agrave;o v&agrave; v&agrave;o bất kỳ thời gian n&agrave;o hợp lý m&agrave; ch&uacute;ng t&ocirc;i đ&atilde; đưa ra.</li>
                 <li>Nếu quý kh&aacute;ch kh&ocirc;ng thể c&oacute; mặt để nhận h&agrave;ng, ch&uacute;ng t&ocirc;i sẽ để lại ghi ch&uacute; hướng dẫn c&aacute;ch gửi h&agrave;ng lại hoặc đến c&ocirc;ng ty vận chuyển để nhận.</li>
                 <li>Nếu việc giao v&agrave; nhận h&agrave;ng bị tr&igrave; ho&atilde;n v&igrave; sự từ chối nhận h&agrave;ng của quý kh&aacute;ch từ đơn vị giao h&agrave;ng ch&uacute;ng t&ocirc;i sẽ xử lý bằng c&aacute;ch sau (m&agrave; kh&ocirc;ng l&agrave;m ảnh hưởng bất cứ quyền lợi hay biện ph&aacute;p xử lý n&agrave;o c&oacute; sẵn cho ch&uacute;ng t&ocirc;i): Qu&yacute; kh&aacute;ch phải chịu chi ph&iacute; vận chuyển nếu như đơn vị vận chuyển t&iacute;nh v&agrave;o chi ph&iacute; ph&aacute;t sinh, hoặc</li>
                 <li>Quý kh&aacute;ch c&oacute; tr&aacute;ch nhiệm đảm bảo rằng c&aacute;c sản phẩm th&iacute;ch hợp với nhu cầu sử dụng v&agrave; đ&aacute;p ứng c&aacute;c y&ecirc;u cầu c&aacute; nh&acirc;n của quý kh&aacute;ch. Ch&uacute;ng t&ocirc;i kh&ocirc;ng đảm bảo rằng c&aacute;c sản phẩm sẽ đ&aacute;p ứng hết c&aacute;c y&ecirc;u cầu c&aacute; nh&acirc;n của quý kh&aacute;ch. Quý kh&aacute;ch cần biết rằng mọi sản phẩm đều mang chất lượng chuẩn chứ kh&ocirc;ng được sản xuất ri&ecirc;ng v&agrave; đ&aacute;p ứng to&agrave;n bộ y&ecirc;u cầu m&agrave; quý kh&aacute;ch muốn.</li>
                 <li>Qu&yacute; kh&aacute;ch được quyền kiểm tra h&agrave;ng khi đơn h&agrave;ng được giao tới. Qu&yacute; kh&aacute;ch phải k&yacute; x&aacute;c nhận v&agrave;o việc đ&atilde; nhận đầy đủ h&agrave;ng như trong đơn h&agrave;ng với người chịu tr&aacute;ch nhiệm giao h&agrave;ng của ch&uacute;ng t&ocirc;i.</li>
             </ol>

            <h2>6. Thay đổi đơn h&agrave;ng bởi kh&aacute;ch h&agrave;ng</h2>
            <p>Qu&yacute; kh&aacute;ch sẽ kh&ocirc;ng hủy được đơn h&agrave;ng khi đ&atilde; quyết định đặt mua h&agrave;ng h&oacute;a của ch&uacute;ng t&ocirc;i. Để biết th&ocirc;ng tin r&otilde; hơn, qu&yacute; kh&aacute;ch vui l&ograve;ng li&ecirc;n hệ với Bộ Phận Dịch Vụ Kh&aacute;ch H&agrave;ng.</p>

            <h2>7. M&atilde; giảm gi&aacute;</h2>
            <ol>
                <li>Quý kh&aacute;ch c&oacute; thể sử dụng m&atilde; giảm gi&aacute; để thanh to&aacute;n cho c&aacute;c sản phẩm tr&ecirc;n trang web. Mỗi đơn h&agrave;ng chỉ được sử dụng 01 m&atilde; giảm gi&aacute;. Với những đơn h&agrave;ng đ&atilde; được thanh to&aacute;n, qu&yacute; kh&aacute;ch kh&ocirc;ng thể sử dụng m&atilde; giảm gi&aacute;.</li>
                <li>Ch&uacute;ng t&ocirc;i c&oacute; thể gửi qu&agrave; tặng hoặc m&atilde; giảm gi&aacute; cho quý kh&aacute;ch qua thư điện tử v&agrave; c&aacute;c m&atilde; giảm gi&aacute; đ&oacute; chỉ c&oacute; thể sử dụng tr&ecirc;n trang web. Tuy nhi&ecirc;n, ch&uacute;ng t&ocirc;i kh&ocirc;ng chịu tr&aacute;ch nhiệm cho bất cứ lỗi n&agrave;o trong địa chỉ thư điện tử hoặc t&ecirc;n người nhận.</li>
                <li>Nếu quý kh&aacute;ch c&oacute; phiếu mua h&agrave;ng hoặc m&atilde; giảm gi&aacute;, phiếu đ&oacute; c&oacute; thể được sử dụng bởi người kh&aacute;c v&agrave; quý kh&aacute;ch c&oacute; thể trao to&agrave;n quyền sử dụng phiếu mua h&agrave;ng hoặc m&atilde; giảm gi&aacute; cho người đ&oacute;.</li>
                <li>Trong trường hợp gian lận, lừa gạt hoặc c&oacute; những h&agrave;nh vi nghi ngờ phạm ph&aacute;p li&ecirc;n quan đến việc mua m&atilde; giảm gi&aacute; hoặc quy đổi điểm thưởng của m&atilde; giảm gi&aacute; đ&oacute; tr&ecirc;n trang web, ch&uacute;ng t&ocirc;i c&oacute; quyền kh&oacute;a T&agrave;i Khoản c&aacute; nh&acirc;n của quý kh&aacute;ch v&agrave;/hoặc y&ecirc;u cầu phương thức thanh to&aacute;n kh&aacute;c.</li>
                <li>Ch&uacute;ng t&ocirc;i kh&ocirc;ng chịu tr&aacute;ch nhiệm cho bất cứ tổn thất, mất trộm, hoặc chữ bị mờ tr&ecirc;n c&aacute;c m&atilde; giảm gi&aacute;.</li>
                <li>C&aacute;c m&atilde; giảm gi&aacute; chỉ c&oacute; gi&aacute; trị sử dụng trong khoảng thời gian được ghi tr&ecirc;n phiếu, chỉ được sử dụng 1 lần v&agrave; kh&ocirc;ng thể sử dụng k&egrave;m với c&aacute;c chương tr&igrave;nh khuyến m&atilde;i kh&aacute;c. Một số thương hiệu sẽ kh&ocirc;ng được &aacute;p dụng trong c&aacute;c chương tr&igrave;nh giảm gi&aacute;.</li>
                <li>Gi&aacute; trị của m&atilde; giảm gi&aacute; kh&ocirc;ng thể sử dụng để trả cho c&aacute;c sản phẩm của một b&ecirc;n thứ ba n&agrave;o kh&aacute;c ch&uacute;ng t&ocirc;i, gi&aacute; trị của m&atilde; giảm gi&aacute; kh&ocirc;ng thể t&iacute;ch lũy l&atilde;i suất hoặc c&oacute; thể quy đổi ra tiền mặt.</li>
                <li>Nếu quý kh&aacute;ch mua sản phẩm c&oacute; gi&aacute; trị thấp hơn gi&aacute; trị của m&atilde; giảm gi&aacute;, ch&uacute;ng t&ocirc;i sẽ kh&ocirc;ng ho&agrave;n trả tiền hay gi&aacute; trị c&ograve;n lại n&agrave;o cho quý kh&aacute;ch. Nếu gi&aacute; trị của m&atilde; giảm gi&aacute; kh&ocirc;ng đủ để mua sản phẩm m&agrave; quý kh&aacute;ch mong muốn, quý kh&aacute;ch sẽ phải trả th&ecirc;m qua c&aacute;c c&aacute;ch thanh to&aacute;n kh&aacute;c.</li>
            </ol>

            <h2>8. Bản quyền v&agrave; sở hữu tr&iacute; tuệ</h2>
            <ol>
                <li>Ch&uacute;ng t&ocirc;i l&agrave; chủ sở hữu hay c&ograve;n gọi l&agrave; người được cấp ph&eacute;p hoặc người c&oacute; quyền sở hữu đối với trang web v&agrave; c&aacute;c t&agrave;i liệu đăng tải tr&ecirc;n trang web. To&agrave;n bộ nội dung của trang web được bảo vệ bởi Luật bản quyền, Luật sở hữu tr&iacute; tuệ của Việt Nam v&agrave; c&aacute;c c&ocirc;ng ước quốc tế.</li>
                <li>Qu&yacute; kh&aacute;ch kh&ocirc;ng được sử dụng bất kỳ t&agrave;i liệu n&agrave;o tr&ecirc;n trang web cho mục đ&iacute;ch thương mại khi chưa được sự đồng &yacute; từ ch&uacute;ng t&ocirc;i hay những người đ&atilde; cấp ph&eacute;p cho ch&uacute;ng t&ocirc;i.</li>
                <li>Qu&yacute; kh&aacute;ch c&oacute; thể in một bản sao v&agrave; c&oacute; thể tải xuống tr&iacute;ch đoạn của bất kỳ trang n&agrave;o tr&ecirc;n trang web để qu&yacute; kh&aacute;ch tiện tham khảo hoặc cho những người kh&aacute;c trong c&ugrave;ng tổ chức của qu&yacute; kh&aacute;ch biết đến, với mục đ&iacute;ch duy nhất l&agrave; mua h&agrave;ng, nhưng phải ghi r&otilde; nguồn v&agrave; tr&iacute;ch dẫn nguy&ecirc;n bản theo đường li&ecirc;n kết từ trang web của ch&uacute;ng t&ocirc;i theo đ&uacute;ng quy định về luật sở hữu tr&iacute; tuệ v&agrave; bản quyền.</li>
                <li>Qu&yacute; kh&aacute;ch kh&ocirc;ng được ph&eacute;p chỉnh sửa giấy tờ hay bản sao điện tử của bất kỳ t&agrave;i liệu n&agrave;o m&agrave; qu&yacute; kh&aacute;ch đ&atilde; in ra hoặc tải xuống. V&agrave; qu&yacute; kh&aacute;ch kh&ocirc;ng được sử dụng bất kỳ ph&aacute;c họa, h&igrave;nh ảnh, phim, &acirc;m thanh hay đồ họa hoặc bất k&igrave; một dấu hiệu n&agrave;o của ch&uacute;ng t&ocirc;i với mục đ&iacute;ch ri&ecirc;ng của qu&yacute; kh&aacute;ch.</li>
                <li>Nếu qu&yacute; kh&aacute;ch in, sao ch&eacute;p, hoặc tải bất kỳ phần n&agrave;o của trang web v&agrave; vi phạm Quy định về người sử dụng, quyền sử dụng trang web của qu&yacute; kh&aacute;ch sẽ bị v&ocirc; hiệu h&oacute;a ngay lập tức, khi đ&oacute; qu&yacute; kh&aacute;ch phải c&oacute; tr&aacute;ch nhiệm ho&agrave;n trả hoặc hủy bỏ c&aacute;c bản sao của c&aacute;c t&agrave;i liệu m&agrave; qu&yacute; kh&aacute;ch đ&atilde; l&agrave;m ra v&agrave; bồi thường to&agrave;n bộ c&aacute;c thiệt hại ph&aacute;t sinh c&oacute; li&ecirc;n quan.</li>
            </ol>

            <h2>9. Điều khoản về bất khả kh&aacute;ng (C&aacute;c trường hợp nằm ngo&agrave;i kiểm so&aacute;t của ch&uacute;ng t&ocirc;i)</h2>
            <ol>
                <li>Ch&uacute;ng t&ocirc;i sẽ kh&ocirc;ng chịu tr&aacute;ch nhiệm cho bất kỳ h&agrave;nh vi vi phạm, cản trở hoặc chậm trễ trong việc thực hiện hợp đồng do bất kỳ nguy&ecirc;n nh&acirc;n nằm ngo&agrave;i tầm kiểm so&aacute;t của ch&uacute;ng t&ocirc;i như: c&aacute;c hiểm họa thi&ecirc;n nhi&ecirc;n v&agrave; những tai nạn kh&ocirc;ng thể tr&aacute;nh khỏi, c&aacute;c h&agrave;nh động của b&ecirc;n thứ ba (bao gồm v&agrave; kh&ocirc;ng giới hạn trong tin tặc, c&aacute;c nh&agrave; cung cấp, ch&iacute;nh phủ, thuộc ch&iacute;nh phủ, cơ quan đa quốc gia hay c&aacute;c ch&iacute;nh quyền địa phương), khởi nghĩa, bạo loạn, bạo động ch&iacute;nh quyền, chiến tranh, th&ugrave; địch, hoạt động hiếu chiến, c&aacute;c trường hợp quốc gia khẩn cấp, khủng bố, bu&ocirc;n lậu, bắt bớ, hạn chế hay cầm t&ugrave; của cơ quan c&oacute; thẩm quyền, đ&igrave;nh c&ocirc;ng, dịch bệnh, hỏa hoạn, ch&aacute;y nổ, b&atilde;o t&aacute;p, lũ lụt, hạn h&aacute;n, điều kiện thời tiết, động đất, thảm họa thi&ecirc;n nhi&ecirc;n, tai nạn, sự cố m&aacute;y m&oacute;c, phần mềm của 1 b&ecirc;n thứ ba, gi&aacute;n đoạn hoặc vấn đề về việc cung cấp tiện &iacute;ch c&ocirc;ng cộng (bao gồm điện, viễn th&ocirc;ng, hay Internet), thiếu hụt hoặc kh&ocirc;ng c&oacute; khả năng để lấy sản phẩm, vật liệu, thiết bị, hay vận chuyển (&ldquo;Trường hợp bất khả kh&aacute;ng&rdquo;), mặc cho những trường hợp đ&oacute; c&oacute; thể lường trước được. Khi c&oacute; những trường hợp bất khả kh&aacute;ng xảy ra, việc giao h&agrave;ng c&oacute; thể bị chậm trễ cho đến khi ch&uacute;ng t&ocirc;i hoạt động b&igrave;nh thường.</li>
                <li>Hoặc l&agrave; quý kh&aacute;ch hoặc l&agrave; ch&uacute;ng t&ocirc;i c&oacute; thể chấm dứt hợp đồng bằng văn bản th&ocirc;ng b&aacute;o cho b&ecirc;n kia biết khi c&aacute;c &ldquo;Trường hợp bất khả kh&aacute;ng&rdquo; k&eacute;o d&agrave;i 2 ng&agrave;y l&agrave;m việc hoặc hơn. Trong trường hợp n&agrave;y, kh&ocirc;ng b&ecirc;n n&agrave;o sẽ chịu tr&aacute;ch nhiệm cho b&ecirc;n n&agrave;o cả khi hợp đồng được chấm dứt.</li>
                <li>Nếu ch&uacute;ng t&ocirc;i c&oacute; hợp đồng giao c&aacute;c sản phẩm giống nhau hoặc tương tự cho nhiều hơn 1 kh&aacute;ch h&agrave;ng v&agrave; ch&uacute;ng t&ocirc;i kh&ocirc;ng thể thực hiện nghĩa vụ giao h&agrave;ng cho quý kh&aacute;ch v&igrave; c&aacute;c trường hợp bất khả kh&aacute;ng, ch&uacute;ng t&ocirc;i c&oacute; to&agrave;n quyền quyết định sẽ thực hiện hợp đồng n&agrave;o v&agrave; thực hiện đến mức độ n&agrave;o.</li>
            </ol>


        </div></div><?php include_once ("footer.php"); ?>

</div> <?php // Khung lớn kết thúc ?>

</body>
</html>