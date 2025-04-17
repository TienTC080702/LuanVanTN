<?php
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

$error_message = null; // Biến lưu lỗi

// Phần xử lý PHP khi submit form (GIỮ NGUYÊN LOGIC GỐC)
if (isset($_POST['them'])) {
    // Nên kiểm tra isset cho tất cả các trường cần dùng
    if (isset($_POST['TenGH'], $_POST['Phi'], $_POST['AnHien'])) {
        $tenGH_post = trim($_POST['TenGH']); // Lấy tên và bỏ khoảng trắng thừa
        $phi_post = $_POST['Phi']; // Lấy phí
        $anHien_post = intval($_POST['AnHien']); // Lấy trạng thái

        if (empty($tenGH_post)) {
            $error_message = "Tên phương thức giao hàng không được để trống.";
        } else {
            // --- Logic kiểm tra trùng tên (giữ nguyên) ---
            $sl_gh = "SELECT TenGH FROM phuongthucgiaohang WHERE TenGH = '" . mysqli_real_escape_string($conn, $tenGH_post) . "'"; // Query hiệu quả hơn
            $rs_gh = mysqli_query($conn, $sl_gh);
            $check = false;
            if ($rs_gh && mysqli_num_rows($rs_gh) > 0) {
                $check = true; // Đã tồn tại
            }
             if($rs_gh) mysqli_free_result($rs_gh);


            if ($check == false) { // Nếu không trùng tên
                // --- Logic INSERT (giữ nguyên) ---
                // Nên dùng mysqli_real_escape_string cho TenGH và kiểm tra Phi là số
                $tenGH_clean = mysqli_real_escape_string($conn, $tenGH_post);
                $phi_clean = floatval($phi_post); // Đảm bảo là số

                $query = "INSERT INTO phuongthucgiaohang(TenGH, Phi, AnHien) VALUES ('$tenGH_clean', $phi_clean, $anHien_post)";
                $result = mysqli_query($conn, $query);

                if ($result) {
                    echo "<script language='javascript'>alert('Thêm phương thức giao hàng thành công!');";
                    echo "location.href = 'index_ptgh.php'</script>";
                    exit(); // Dừng script sau khi chuyển hướng
                } else {
                     $error_message = "Lỗi khi thêm vào CSDL: " . mysqli_error($conn);
                     // echo "<script language='javascript'>alert('Thêm không thành công');</script>"; // Bỏ alert
                }
            } else { // Nếu trùng tên
                 $error_message = "Tên phương thức giao hàng này đã tồn tại!";
                 // echo "<script language='JavaScript'> alert('Tên giao hàng đã tồn tại!');</script>"; // Bỏ alert
            }
        } // Đóng else kiểm tra tên rỗng
    } else {
         $error_message = "Vui lòng nhập đầy đủ thông tin.";
         // echo "<script language='javascript'>alert('Vui lòng nhập đầy đủ thông tin!');</script>"; // Bỏ alert
    }
} // Đóng if submit
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Thêm Phương Thức Giao Hàng</title>
    <?php include_once('header2.php'); ?>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS cho container form */
        .form-container {
            max-width: 700px; /* Giữ nguyên hoặc điều chỉnh */
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        /* CSS cho tiêu đề */
        h3.form-title {
            color: #198754; /* Màu xanh lá success */
            text-align: center;
            margin-bottom: 2rem;
            font-weight: bold;
        }
        /* Căn giữa nút */
        .button-group {
            text-align: center;
            margin-top: 25px;
        }
         .button-group .btn {
             margin: 0 10px;
             padding: 10px 25px;
         }
          .required-mark { color: red; margin-left: 2px; font-weight: bold;}
         /* Bỏ CSS nút cũ */
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">THÊM PHƯƠNG THỨC GIAO HÀNG MỚI</h3>

        <?php
        // Hiển thị lỗi nếu có
        if (!empty($error_message)) {
            echo "<div class='alert alert-danger text-center' role='alert'><strong>Lỗi:</strong> " . htmlspecialchars($error_message) . "</div>";
        }
        ?>

        <form method="post" action="them_ph_giaohang.php" name="ThemPTGH"> <div class="mb-3">
                <label for="TenGH" class="form-label"><strong>Tên phương thức giao hàng:<span class="required-mark">*</span></strong></label>
                <input type="text" class="form-control" id="TenGH" name="TenGH" required
                       value="<?php echo isset($_POST['TenGH']) ? htmlspecialchars($_POST['TenGH']) : ''; ?>" placeholder="Ví dụ: Giao hàng nhanh, Giao hàng tiết kiệm...">
            </div>

            <div class="mb-3">
                <label for="Phi" class="form-label"><strong>Phí giao hàng (VNĐ):<span class="required-mark">*</span></strong></label>
                <input type="number" class="form-control" id="Phi" name="Phi" required min="0" step="any"
                       value="<?php echo isset($_POST['Phi']) ? htmlspecialchars($_POST['Phi']) : '0'; ?>" placeholder="Nhập phí vận chuyển">
                 <div class="form-text">Nhập số tiền, ví dụ: 30000. Nhập 0 nếu miễn phí.</div>
            </div>

            <div class="mb-3">
                 <label for="AnHien" class="form-label"><strong>Trạng thái:</strong></label>
                 <select class="form-select" name="AnHien" id="AnHien">
                     <option value="1" <?php echo (isset($_POST['AnHien']) && $_POST['AnHien'] == 1) ? 'selected' : ''; ?>>Hiện</option>
                     <option value="0" <?php echo (isset($_POST['AnHien']) && $_POST['AnHien'] == 0) ? 'selected' : ''; ?>>Ẩn</option>
                 </select>
                 <div class="form-text">Chọn "Hiện" để áp dụng phương thức này cho khách hàng.</div>
            </div>


             <div class="button-group">
                <button type="submit" name="them" class="btn btn-success btn-lg">
                    <i class="fas fa-plus-circle"></i> Thêm Phương Thức
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="getConfirmation();"> <i class="fas fa-times"></i> Hủy
                </button>
            </div>

        </form>
    </div> </div> <script type="text/javascript">
    function getConfirmation() {
        var retVal = confirm("Bạn có muốn hủy và quay lại danh sách?");
        if (retVal == true) {
            window.location.href = 'index_ptgh.php'; // Chuyển về trang danh sách
        }
    }
</script>

<?php include_once('footer.php'); ?>
</body>
</html>