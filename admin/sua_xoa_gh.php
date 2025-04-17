<?php
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

$ptgh_data = null; // Biến lưu dữ liệu PTGH
$error_load = null; // Lỗi tải dữ liệu
$error_message = ""; // Thông báo lỗi xử lý form (giữ tên biến gốc của bạn)
$thongbao = "";      // Giữ lại biến thongbao gốc nếu logic JS alert cần

// --- LẤY DỮ LIỆU PTGH CẦN SỬA ---
if (!isset($_GET['idGH']) || !is_numeric($_GET['idGH'])) {
    die("Lỗi: ID Phương thức giao hàng không hợp lệ.");
}
$idGH_get = intval($_GET['idGH']);

$sl = "SELECT * FROM phuongthucgiaohang WHERE idGH=" . $idGH_get;
$kq_select = mysqli_query($conn, $sl); // Đổi tên biến kết quả select

if (!$kq_select) {
    $error_load = "Lỗi truy vấn phương thức giao hàng: " . mysqli_error($conn);
} else {
    // Dùng fetch_assoc
    $ptgh_data = mysqli_fetch_assoc($kq_select);
    if (!$ptgh_data) {
        $error_load = "Không tìm thấy phương thức giao hàng với ID=" . $idGH_get;
    }
    mysqli_free_result($kq_select); // Giải phóng sau khi fetch
}


// --- XỬ LÝ KHI SUBMIT FORM (LOGIC GỐC CỦA BẠN - GIỮ NGUYÊN) ---

// Xử lý khi người dùng nhấn nút "Xóa"
if (isset($_POST['xoa'])) {
    // Thực hiện xóa trực tiếp theo logic gốc
    $sl_delete = "DELETE FROM phuongthucgiaohang WHERE idGH=" . $idGH_get;
    $kq_delete = mysqli_query($conn, $sl_delete);
    if ($kq_delete) {
        echo "<script language='javascript'>alert('Xóa phương thức giao hàng thành công!');";
        echo "location.href='index_ptgh.php';</script>";
        exit(); // Dừng sau khi chuyển hướng
    } else {
        // Gán lỗi vào biến thay vì alert ngay
        $error_message = "Xóa không thành công: " . mysqli_error($conn);
        // echo "<script language='javascript'>alert('Xóa không thành công!');</script>";
    }
}

// Xử lý khi người dùng nhấn nút "Sửa"
if (isset($_POST['sua'])) {
     // Nên kiểm tra isset cho tất cả các trường cần dùng
    if (isset($_POST['TenGH'], $_POST['Phi'], $_POST['AnHien'])) {
        $tenGH_post = trim($_POST['TenGH']); // Lấy tên mới
        $phi_post = $_POST['Phi'];         // Lấy phí mới
        $anHien_post = intval($_POST['AnHien']); // Lấy trạng thái mới

        if (empty($tenGH_post)) {
             $error_message = "Tên phương thức giao hàng không được để trống.";
        } else {
             // --- Logic kiểm tra trùng tên (giữ nguyên) ---
             $check = false;
             // Query hiệu quả hơn: Chỉ cần kiểm tra xem có tồn tại tên đó với ID khác không
             $sql_check_gh = "SELECT idGH FROM phuongthucgiaohang WHERE TenGH = '" . mysqli_real_escape_string($conn, $tenGH_post) . "' AND idGH <> " . $idGH_get;
             $kq_check = mysqli_query($conn, $sql_check_gh);
             if ($kq_check && mysqli_num_rows($kq_check) > 0) {
                 $check = true; // Trùng tên
             }
              if($kq_check) mysqli_free_result($kq_check);


             if ($check == false) { // Không trùng tên
                 // --- Logic UPDATE (giữ nguyên) ---
                 $tenGH_clean = mysqli_real_escape_string($conn, $tenGH_post);
                 $phi_clean = floatval($phi_post); // Đảm bảo là số

                 $query_update = "UPDATE phuongthucgiaohang SET
                                     TenGH ='$tenGH_clean',
                                     Phi = $phi_clean,
                                     AnHien = $anHien_post
                                  WHERE idGH = $idGH_get";

                 $result_update = mysqli_query($conn, $query_update);

                 if ($result_update) {
                     echo "<script language='javascript'>alert('Cập nhật thành công!');";
                     echo "location.href='index_ptgh.php';</script>";
                     exit(); // Dừng sau khi chuyển hướng
                 } else {
                      $error_message = "Cập nhật không thành công: " . mysqli_error($conn);
                      // echo "<script language='javascript'>alert('Cập nhật không thành công!');</script>";
                 }
             } else { // Nếu trùng tên
                  $error_message = "Tên phương thức giao hàng này đã tồn tại!";
                  // echo "<script language='javascript'>alert('Trùng tên giao hàng!');</script>";
             }
        } // Đóng else kiểm tra tên rỗng
    } else {
         $error_message = "Vui lòng nhập đầy đủ thông tin.";
    }
} // Đóng if submit sửa
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Sửa Phương Thức Giao Hàng</title> <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS cho container form */
        .form-container {
            max-width: 700px;
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        /* CSS cho tiêu đề */
        h3.form-title {
            color: #ffc107; /* Màu vàng warning */
            text-align: center;
            margin-bottom: 2rem;
            font-weight: bold;
        }
        /* Căn giữa nút */
        .button-group {
            text-align: center;
            margin-top: 25px;
        }
         .button-group .btn, .button-group input[type=button] { /* Áp dụng cho cả button và input type button */
             margin: 0 8px;
             padding: 10px 20px;
         }
         .required-mark { color: red; margin-left: 2px; font-weight: bold;}
         /* Bỏ CSS nút cũ */
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">SỬA PHƯƠNG THỨC GIAO HÀNG</h3>

        <?php
        // Hiển thị lỗi tải dữ liệu hoặc lỗi xử lý form
        if (!empty($error_load)) {
            echo "<div class='alert alert-danger text-center'><strong>Lỗi tải dữ liệu:</strong> " . htmlspecialchars($error_load) . "</div>";
        } elseif (!empty($error_message)) {
            echo "<div class='alert alert-danger text-center'><strong>Lỗi xử lý:</strong> " . htmlspecialchars($error_message) . "</div>";
        }

        // Chỉ hiển thị form nếu tải dữ liệu thành công
        if ($ptgh_data):
        ?>

        <form method="post" action="sua_xoa_gh.php?idGH=<?php echo $idGH_get; ?>" name="SuaGH">

            <div class="mb-3">
                <label for="TenGH" class="form-label"><strong>Tên phương thức giao hàng:<span class="required-mark">*</span></strong></label>
                <input type="text" class="form-control" id="TenGH" name="TenGH" required
                       value="<?php echo htmlspecialchars($ptgh_data['TenGH']); ?>">
            </div>

            <div class="mb-3">
                <label for="Phi" class="form-label"><strong>Phí giao hàng (VNĐ):<span class="required-mark">*</span></strong></label>
                <input type="number" class="form-control" id="Phi" name="Phi" required min="0" step="any"
                       value="<?php echo htmlspecialchars($ptgh_data['Phi']); ?>">
                <div class="form-text">Nhập số tiền, ví dụ: 30000. Nhập 0 nếu miễn phí.</div>
            </div>

            <div class="mb-3">
                 <label for="AnHien" class="form-label"><strong>Trạng thái:</strong></label>
                 <select class="form-select" name="AnHien" id="AnHien">
                     <option value="1" <?php if($ptgh_data['AnHien'] == 1) echo "selected"; ?>>Hiện</option>
                     <option value="0" <?php if($ptgh_data['AnHien'] == 0) echo "selected"; ?>>Ẩn</option>
                 </select>
                 <div class="form-text">Chọn "Hiện" để áp dụng phương thức này cho khách hàng.</div>
            </div>

             <div class="button-group">
                <button type="submit" name="sua" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
                <button type="submit" name="xoa" class="btn btn-danger btn-lg" onclick="return confirm('Bạn có chắc chắn muốn XÓA phương thức giao hàng này không? Hành động này không thể hoàn tác!');">
                     <i class="fas fa-trash"></i> Xóa
                 </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.href='index_ptgh.php';">
                    <i class="fas fa-arrow-left"></i> Trở về
                </button>
            </div>

        </form>

        <?php
        endif; // Đóng if ($ptgh_data)
        ?>

    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>