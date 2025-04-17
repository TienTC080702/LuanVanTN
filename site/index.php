<?php
// Bắt đầu session ở đầu file nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html" lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="../css/hoa.min.css" type="text/css">
    <link rel="stylesheet" href="../css/layout.min.css" type="text/css">

    <title>Trang chủ</title>

    <?php
        // --- PHẦN PHP INCLUDE GIỮ NGUYÊN ---
        include_once('../connection/connect_database.php'); // Kết nối CSDL ($conn)
        include_once('../libs/lib.php');
    ?>

    <?php /* --- KHỐI STYLE ĐÃ CẬP NHẬT CSS LINK VÀ VOICE SEARCH --- */ ?>
    <style type="text/css">
        /* 1. CSS cho BODY */
        body { background-color: #f0f0f0; padding: 0; margin: 0; font-family: sans-serif; }

        /* 2. CSS cho KHUNG CHUNG - NỀN HỒNG ĐẤT */
        #main-container { max-width: 1200px; margin: 30px auto; border: 1px solid rgb(236, 206, 227); background-color:rgb(255, 231, 236); padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden; color: #2c1e18; }

        /* 3. CSS cho .wrapper (Vẫn giữ nguyên) */
        .wrapper { padding: 0; margin: 0; max-width: 100%; border-radius: 0; border: none; box-shadow: none; }

        /* 4. CSS cho KHUNG SẢN PHẨM (.box) - Nền TRẮNG */
        .box { border-radius: 8px; background-color: #ffffff; padding: 20px; margin-bottom: 25px; border: 1px solid #eee; text-align: center; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .box h4 { font-size: 1.1em; margin-bottom: 10px; font-weight: bold; color: #333; }
        .box h5 { font-size: 1em; margin-bottom: 15px; font-weight: normal; color: #555; }
        .box img { max-width: 100%; height: auto; margin-bottom: 15px; border-radius: 4px; }
        .box:hover { transform: translateY(-4px); box-shadow: 0 8px 16px rgba(0,0,0,0.12); border-color: #ddd; }

        /* 5. CSS cho Thanh tìm kiếm (Giữ nguyên từ lần cập nhật SVG) */
        .search-bar { margin-bottom: 30px; padding: 0; text-align: center; background-color: transparent; border: none; }
        .search-form-wrapper { display: flex; max-width: 500px; margin: 0 auto; border: 1px solid #ccc; border-radius: 25px; overflow: hidden; background-color: #fff; }
        .search-bar input[type="text"]#mainSearchInput { flex-grow: 1; border: none; padding: 12px 15px 12px 20px; font-size: 1em; outline: none; background-color: transparent; border-radius: 0; }
        #voiceSearchBtn { background: none; border: none; cursor: pointer; padding: 5px 10px; outline: none; margin-left: 5px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; }
        #voiceSearchBtn svg { display: block; transition: fill 0.2s ease; }
        #voiceSearchBtn:hover svg { fill: #333; }
        #voiceSearchBtn:disabled { cursor: default; opacity: 0.5; }
        #voiceSearchBtn:disabled svg { fill: #5f6368; }
        .search-bar button[type="submit"] { border: none; background-color:rgb(180, 70, 150); color: white; padding: 0 20px; cursor: pointer; font-size: 1em; transition: background-color 0.2s ease; border-radius: 0 25px 25px 0; flex-shrink: 0; margin-left: 0; }
        .search-bar button[type="submit"]:hover { background-color: #8a5a4a; }
        #voiceStatus { text-align: center; margin-top: 5px; min-height: 1.2em; font-size: 0.9em; color: gray; }

        /* --- CSS styles khác --- */
        div.row { padding-top: 2%; }

        /* --- CSS CẬP NHẬT CHO LINK TRONG .BOX --- */
        #main-container .box a[href*="XemChiTiet"],
        #main-container .box h4 a { color: #A56C5A !important; text-decoration: none; font-weight: normal; }
        #main-container .box a[href*="XemChiTiet"]:hover,
        #main-container .box h4 a:hover { color: #8a5a4a !important; text-decoration: underline; }
        #main-container .box a.buyproduct { color: #ffffff !important; background-color:rgb(165, 90, 90); padding: 10px 18px; border-radius: 20px; cursor: pointer; font-weight: bold; margin-top: 10px; text-decoration: none; display: inline-block; transition: background-color 0.2s ease, transform 0.1s ease; border: none;}
        #main-container .box a.buyproduct:hover { background-color: #8a5a4a; transform: scale(1.03); }
        #main-container .box a.buyproduct:active { transform: scale(0.98); }
        /* --- KẾT THÚC CSS CẬP NHẬT CHO LINK --- */

        /* CSS cho Modal (Giữ nguyên) */
        #myModal .modal-content { background-color: #fff; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
        #myModal .modal-body { padding: 30px; text-align: center; }
        #myModal .modal-body img { max-width: 100%; height: auto; border-radius: 4px; margin-top: 15px; }
        #myModal .close { color: #aaa; opacity: 0.8; font-size: 2em; position: absolute; top: 10px; right: 15px; text-shadow: none; }
        #myModal .close:hover { color: #000; opacity: 1; }
        .modal-backdrop { background-color: rgba(0, 0, 0, 0.5); }

        /* --- Responsive (Giữ nguyên) --- */
        @media (max-width: 768px) { #main-container { margin: 15px auto; padding: 15px; } .search-form-wrapper { max-width: 95%; } .box { padding: 15px; } }
        @media (max-width: 480px) { #main-container { margin: 10px auto; padding: 10px; border: none; box-shadow: none; } .search-bar input[type="text"] { padding: 10px 15px; font-size: 0.9em; } .search-bar button { padding: 0 15px; font-size: 0.9em; } #voiceSearchBtn { padding: 0 8px; } #voiceSearchBtn svg { height: 20px; width: 20px; } .box { padding: 10px; } .box h4 { font-size: 1em; } .box h5 { font-size: 0.9em; } .buyproduct { padding: 8px 12px; font-size: 0.9em;} }

    </style>
    <?php /* --- KẾT THÚC KHỐI STYLE --- */ ?>

</head>

<body>

    <?php // Khung chính bao bọc nội dung ?>
    <div id="main-container">

        <?php
        // --- PHẦN INCLUDE HEADER GIỮ NGUYÊN ---
        include_once("header2_index.php");
        ?>

        <?php /* --- KHỐI HTML THANH TÌM KIẾM VỚI ICON SVG --- */ ?>
        <div class="search-bar">
            <form id="mainSearchForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
                <input type="hidden" name="index" value="9">
                <div class="search-form-wrapper">
                    <input type="text" id="mainSearchInput" name="search" placeholder="Nhấn micro hoặc nhập để tìm..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
                    <button type="button" id="voiceSearchBtn" title="Tìm kiếm bằng giọng nói">
                        <svg xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 0 24 24" width="22px" fill="#5f6368">
                            <path d="M0 0h24v24H0V0z" fill="none"/>
                            <path d="M12 14c1.66 0 2.99-1.34 2.99-3L15 5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.49 6-3.31 6-6.72h-1.7z"/>
                        </svg>
                    </button>
                    <button type="submit">Tìm</button>
                </div>
            </form>
            <p id="voiceStatus" style="margin-top: 5px; min-height: 1.2em; font-size: 0.9em; color: gray;"></p> <?php // Thẻ hiển thị trạng thái ?>
        </div>
        <?php /* --- KẾT THÚC KHỐI HTML THANH TÌM KIẾM --- */ ?>


        <?php // Khu vực hiển thị sản phẩm ?>
        <div class="wrapper">
            <?php
            // --- PHẦN PHP XỬ LÝ HIỂN THỊ SẢN PHẨM GIỮ NGUYÊN ---
            $index = isset($_GET['index']) ? (int)$_GET['index'] : 1;

            if (isset($conn) && $conn) {
                switch ($index) {
                    case 1: include_once("../site/loc_all.php"); break;
                    case 2: include_once("../site/TimKiemNoiBat.php"); break;
                    case 3: include_once("../site/TimKiem_SPBanChay.php"); break;
                    case 4: include_once("../site/TimKiem_SPKhuyenMai.php"); break;
                    case 5: include_once("../site/TimKiem_SPMoiVe.php"); break;
                    case 6: include_once("../site/TimKiem_SPXemNhieu.php"); break;
                    case 7: include_once("../site/TimKiem_GiaCaoThap.php"); break;
                    case 8: include_once("../site/TimKiem_GiaThapCao.php"); break;
                    case 9: include_once("../site/loc_tim_kiem.php"); break;
                    default:
                        echo "<p>Trang bạn yêu cầu không hợp lệ.</p>";
                        break;
                }
            } else {
                error_log("Database connection is not available for main content loading. Check connect_database.php");
                echo "<p style='color: red; text-align: center; padding: 20px;'>Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p>";
            }
            ?>
        </div> <?php // Đóng thẻ .wrapper ?>


        <?php // --- PHẦN INCLUDE FOOTER GIỮ NGUYÊN --- ?>
        <?php include_once("footer.php"); ?>

    <?php // Đóng thẻ #main-container ?>
    </div>

    <?php // --- PHẦN MODAL GIỮ NGUYÊN --- ?>
     <div id="myModal" style="margin-top: 90px;" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
         <div class="modal-dialog"> <div class="modal-content"> <div class="modal-body">
             <button type="button" class="close" data-dismiss="modal">&times;</button>
             <?php
             if (isset($conn) && $conn) {
                 $sl_km = "SELECT urlHinh FROM khuyenmai WHERE AnHien=1 ORDER BY idKM DESC LIMIT 1";
                 $qr_km = mysqli_query($conn, $sl_km);
                 if ($qr_km && mysqli_num_rows($qr_km) > 0) {
                     $d = mysqli_fetch_assoc($qr_km);
                     if (isset($d['urlHinh']) && !empty(trim($d['urlHinh']))) {
                         $imageFilename = trim($d['urlHinh']); $imagePath = "../images/" . $imageFilename;
                         if (file_exists($imagePath)) { echo '<img src="' . htmlspecialchars($imagePath) . '" alt="Khuyến mãi đặc biệt">'; }
                         else { error_log("Promotion image file not found: " . $imagePath); }
                     }
                     mysqli_free_result($qr_km);
                 }
             } else { error_log("DB connection unavailable for promotion modal."); }
             ?>
         </div> </div> </div>
     </div>

    <?php // --- PHẦN SCRIPT VÀ LOGIC HIỂN THỊ MODAL GIỮ NGUYÊN --- ?>
    <script src="../js/jquery-3.1.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <?php
        if (!isset($_SESSION['solan'])) { $_SESSION['solan'] = 0; } else { if (isset($_SESSION['solan'])) { $_SESSION['solan']++; } }
        $shouldShowModal = false;
        if (isset($_SESSION['solan']) && $_SESSION['solan'] == 0) {
             if (isset($conn) && $conn) {
                 $sl_km_check = "SELECT urlHinh FROM khuyenmai WHERE AnHien=1 ORDER BY idKM DESC LIMIT 1"; $qr_km_check = mysqli_query($conn, $sl_km_check);
                 if ($qr_km_check && mysqli_num_rows($qr_km_check) > 0) {
                     $d_check = mysqli_fetch_assoc($qr_km_check);
                     if (isset($d_check['urlHinh']) && !empty(trim($d_check['urlHinh']))) {
                         $imageFilename_check = trim($d_check['urlHinh']); $imagePath_check = "../images/" . $imageFilename_check;
                         if (file_exists($imagePath_check)) { $shouldShowModal = true; }
                     }
                     mysqli_free_result($qr_km_check);
                 }
             }
        }
        if ($shouldShowModal) { echo "<script type=\"text/javascript\"> $(window).on('load', function(){ if ($('#myModal').length) { $('#myModal').modal('show'); } }); </script>"; }
    ?>

    <?php // ----- BẮT ĐẦU CODE JAVASCRIPT (ĐÃ DỌN LOG, ĐẶT LẠI TIẾNG VIỆT, TỰ ĐỘNG SUBMIT) ----- ?>
    <script type="text/javascript">
        // Lấy các phần tử HTML cần thiết
        const searchInput = document.getElementById('mainSearchInput');
        const voiceSearchBtn = document.getElementById('voiceSearchBtn');
        const voiceStatus = document.getElementById('voiceStatus');
        const searchForm = document.getElementById('mainSearchForm');

        // Kiểm tra hỗ trợ Web Speech API
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        let recognition;

        if (SpeechRecognition) {
            console.log("Trình duyệt hỗ trợ Nhận dạng Giọng nói.");
            recognition = new SpeechRecognition();

            // Cấu hình
            recognition.continuous = false;
            recognition.lang = 'vi-VN'; // <<< Đặt lại ngôn ngữ Tiếng Việt
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            // Sự kiện nhấn nút micro
            voiceSearchBtn.addEventListener('click', () => {
                if (!recognition) return;
                try {
                    voiceStatus.textContent = 'Đang lắng nghe...';
                    voiceStatus.style.color = 'blue';
                    recognition.start();
                } catch (error) {
                    console.error("Lỗi khi bắt đầu nhận dạng:", error);
                }
            });

            // Bắt đầu nhận dạng (onstart)
            recognition.onstart = () => {
                console.log('Bắt đầu nhận dạng.');
                voiceStatus.textContent = 'Đang lắng nghe...';
                voiceStatus.style.color = 'blue';
                voiceSearchBtn.disabled = true;
            };

            // Kết thúc nhận dạng (onend)
            recognition.onend = () => {
                console.log('Kết thúc nhận dạng.');
                voiceSearchBtn.disabled = false; // Kích hoạt lại nút
            };

            // Lỗi nhận dạng (onerror)
            recognition.onerror = (event) => {
                console.error('Lỗi nhận dạng:', event.error);
                let errorMessage = 'Lỗi: ';
                if (event.error === 'no-speech') { errorMessage += 'Không nghe thấy bạn nói.'; }
                else if (event.error === 'audio-capture') { errorMessage += 'Không tìm thấy micro.'; }
                else if (event.error === 'not-allowed') { errorMessage += 'Bạn chưa cấp quyền micro.'; }
                else if (event.error === 'network') { errorMessage += 'Lỗi mạng.'; }
                else if (event.error === 'aborted') { errorMessage += 'Nhận dạng bị dừng.'; }
                else if (event.error === 'service-not-allowed') { errorMessage += 'Dịch vụ nhận dạng không được phép.'; }
                else { errorMessage += event.error; }
                voiceStatus.textContent = errorMessage;
                voiceStatus.style.color = 'red';
                voiceSearchBtn.disabled = false; // Đảm bảo nút được kích hoạt lại
            };

            // Có kết quả nhận dạng (onresult)
            recognition.onresult = (event) => {
                console.log('Có kết quả nhận dạng.');
                const transcript = event.results[0][0].transcript;
                console.log('   -> Văn bản:', transcript);

                searchInput.value = transcript;
                voiceStatus.textContent = 'Đã nhận dạng: "' + transcript + '"';
                voiceStatus.style.color = 'green';

                // ----- TỰ ĐỘNG GỬI FORM SAU KHI NHẬN DẠNG -----
                console.log('Tự động gửi form tìm kiếm...');
                searchForm.submit(); // <<< Bỏ comment dòng này để tự động gửi
                // ---------------------------------------------
            };

        } else {
            console.error("Trình duyệt không hỗ trợ Web Speech API.");
            voiceStatus.textContent = 'Tìm kiếm giọng nói không được hỗ trợ trên trình duyệt này.';
            voiceStatus.style.color = 'orange';
            if(voiceSearchBtn) {
               voiceSearchBtn.disabled = true;
               voiceSearchBtn.style.display = 'none';
            }
        }
    </script>
    <?php // ----- KẾT THÚC CODE JAVASCRIPT ----- ?>

</body>
</html>