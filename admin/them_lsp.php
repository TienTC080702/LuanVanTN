<?php
include_once('../connection/connect_database.php');

$message = ''; // Biến lưu thông báo (lỗi hoặc thành công)
$error = false; // Cờ báo lỗi

// --- SỬA LẠI ĐIỀU KIỆN KIỂM TRA SUBMIT ---
// Kiểm tra phương thức POST và nút submit có name='ok'
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ok'])) {
    // Lấy và làm sạch dữ liệu đầu vào
    $tenL = isset($_POST['TenL']) ? trim($_POST['TenL']) : ''; // Lấy TenL
    $thuTu = isset($_POST['ThuTu']) ? (int)$_POST['ThuTu'] : 0;
    $anHien = isset($_POST['AnHien']) ? (int)$_POST['AnHien'] : 1;

    // --- Kiểm tra dữ liệu bắt buộc ---
    if (empty($tenL)) {
        $message = "Vui lòng nhập Tên loại sản phẩm.";
        $error = true;
    } else {
        // --- Kiểm tra tên loại trùng (Logic PHP loop gốc - không tối ưu) ---
        $sql_check = "SELECT TenL FROM loaisp";
        $rs_check = mysqli_query($conn, $sql_check);
        $is_duplicate = false;
        if ($rs_check) {
            while ($row_check = mysqli_fetch_assoc($rs_check)) {
                // Nên dùng hàm so sánh không phân biệt hoa thường của DB nếu collation không phải _ci
                if (strcasecmp($row_check['TenL'], $tenL) == 0) {
                    $is_duplicate = true;
                    break;
                }
            }
            mysqli_free_result($rs_check);
        } else {
            $message = "Lỗi truy vấn kiểm tra tên loại: " . mysqli_error($conn);
            $error = true;
        }

        // --- Xử lý kết quả kiểm tra trùng ---
        if ($is_duplicate) {
            $message = "Tên loại sản phẩm này đã tồn tại. Vui lòng chọn tên khác.";
            $error = true;
        } elseif (!$error) { // Chỉ INSERT nếu không có lỗi nào trước đó
            // --- Chuẩn bị INSERT (Logic gốc - Vulnerable SQL Injection) ---
            // *** CẢNH BÁO: Code gốc dùng trực tiếp $_POST, có nguy cơ SQL Injection ***
            // *** Sử dụng Prepared Statement được khuyến nghị mạnh mẽ ***
            $tenL_clean = mysqli_real_escape_string($conn, $tenL); // Escape TenL cơ bản
            $phi_value = isset($_POST['Phi']) ? $_POST['Phi'] : 0; // Code gốc có vẻ nhầm lẫn dùng $_POST['Phi']??

            // *** SỬA LẠI CÂU INSERT CHO ĐÚNG VỚI FORM NÀY ***
            // Dùng TenL, ThuTu, AnHien
            $query_insert = "INSERT INTO loaisp(TenL, ThuTu, AnHien) VALUES ('$tenL_clean', $thuTu, $anHien)";

            $result_insert = mysqli_query($conn, $query_insert);

            if ($result_insert) {
                // Không dùng alert nữa, gán vào biến success
                // $message = "Thêm loại sản phẩm thành công!";
                // Chuyển hướng ngay lập tức
                echo "<script language='javascript'>
                          alert('Thêm loại sản phẩm thành công!');
                          location.href = 'index_ds_loaisp.php';
                      </script>";
                exit;
            } else {
                $message = "Thêm không thành công! Lỗi: " . mysqli_error($conn);
                $error = true;
            }
        }
    }
    // Không hiển thị alert ở đây nữa, sẽ hiển thị trong HTML
    // if ($error && !empty($message)) {
    //     echo "<script language='javascript'>alert('" . addslashes($message) . "');</script>";
    // }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Thêm Loại Sản Phẩm</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS tùy chỉnh nhẹ */
        .page-title {
            color: #0d6efd; /* Màu xanh dương primary */
            font-family: 'Arial', sans-serif;
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .custom-form-container {
            max-width: 700px;
            margin: 30px auto; /* Thêm margin trên dưới */
            background-color: #ffffff; /* Nền trắng */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #dee2e6; /* Màu border nhẹ hơn */
        }
        .button-group {
            text-align: center;
            margin-top: 25px; /* Khoảng cách trên nút */
        }
        .button-group .btn {
            margin: 0 8px; /* Khoảng cách giữa các nút */
            padding: 10px 20px; /* Kích thước nút */
        }
         .required-mark { color: red; margin-left: 2px; font-weight: bold;}
         /* Bỏ CSS nút cũ */
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <h3 class="page-title">THÊM LOẠI SẢN PHẨM MỚI</h3>

    <div class="custom-form-container">

        <?php
        // Hiển thị thông báo lỗi (nếu có) bằng Bootstrap Alert
        if ($error && !empty($message)) {
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($message) . '</div>';
        }
        // Có thể thêm thông báo thành công ở đây nếu không chuyển hướng ngay
        // elseif (!$error && isset($_POST['ok']) && !empty($message)) {
        //     echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($message) . '</div>';
        // }
        ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" name="ThemLSPForm" id="ThemLSPForm">
            <div class="mb-3 row">
                <label for="TenL" class="col-sm-3 col-form-label text-sm-end"><strong>Tên loại <span class="required-mark">*</span></strong></label>
                <div class="col-sm-9">
                    <input type="text" name="TenL" id="TenL" class="form-control" required value="<?php echo isset($_POST['TenL']) ? htmlspecialchars($_POST['TenL']) : ''; ?>">
                </div>
            </div>

            <div class="mb-3 row">
                <label for="ThuTu" class="col-sm-3 col-form-label text-sm-end"><strong>Thứ tự</strong></label>
                <div class="col-sm-9">
                    <input type="number" name="ThuTu" id="ThuTu" class="form-control" value="<?php echo isset($_POST['ThuTu']) ? htmlspecialchars($_POST['ThuTu']) : '0'; ?>">
                    <div class="form-text">Số thứ tự hiển thị (số nhỏ xếp trước).</div>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="AnHien" class="col-sm-3 col-form-label text-sm-end"><strong>Trạng thái</strong></label>
                <div class="col-sm-9">
                    <select name="AnHien" id="AnHien" class="form-select">
                        <option value="1" <?php echo (!isset($_POST['AnHien']) || (isset($_POST['AnHien']) && $_POST['AnHien'] == 1)) ? 'selected' : ''; ?>>Hiện</option>
                        <option value="0" <?php echo (isset($_POST['AnHien']) && $_POST['AnHien'] == 0) ? 'selected' : ''; ?>>Ẩn</option>
                    </select>
                    <div class="form-text">Chọn "Hiện" để loại sản phẩm này hiển thị trên trang web.</div>
                </div>
            </div>

            <div class="button-group">
                 <button name="ok" type="submit" class="btn btn-primary btn-lg"> <i class="fas fa-save me-1"></i> Lưu
                 </button>
                 <button name="reset" type="reset" class="btn btn-warning btn-lg text-white"> <i class="fas fa-undo me-1"></i> Nhập lại
                 </button>
                 <button type="button" name="Huy" class="btn btn-secondary btn-lg" onclick="getConfirmation()"> <i class="fas fa-times me-1"></i> Hủy
                 </button>
            </div>
        </form>
    </div> </div> <script type="text/javascript">
    function getConfirmation() {
        var retVal = confirm("Bạn có muốn hủy và quay lại trang danh sách?");
        if (retVal == true) {
            window.location.href = 'index_ds_loaisp.php'; // Chuyển hướng về trang danh sách loại SP
        }
    }
    // Bỏ JS validation cơ bản vì đã dùng required của HTML5
    /*
    document.getElementById('ThemLSPForm').addEventListener('submit', function(event) {
        // ...
    });
    */
</script>

<?php include_once ('footer.php');?>
</body>
</html>