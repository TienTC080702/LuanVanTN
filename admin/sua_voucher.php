<?php
session_start();
// Include file kết nối và các header admin
include_once('../connection/connect_database.php');
include_once('header1.php');
include_once('header2.php'); // Đảm bảo chứa CSS/JS cần thiết
// include_once('header3.php'); // Nếu có

// --- Kiểm tra ID voucher ---
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['message'] = "ID Voucher không hợp lệ.";
    $_SESSION['message_type'] = "warning";
    header('Location: quanly_voucher.php');
    exit;
}
$voucher_id = (int)$_GET['id'];

// --- Lấy thông tin voucher từ CSDL ---
if (!isset($conn) || !$conn) { die("Lỗi kết nối CSDL."); }
mysqli_set_charset($conn, 'utf8');

$sql_get = "SELECT * FROM vouchers WHERE id = ?";
$stmt_get = mysqli_prepare($conn, $sql_get);
if (!$stmt_get) {
     $_SESSION['message'] = "Lỗi chuẩn bị truy vấn.";
     $_SESSION['message_type'] = "danger";
     header('Location: quanly_voucher.php');
     exit;
}

mysqli_stmt_bind_param($stmt_get, "i", $voucher_id);
mysqli_stmt_execute($stmt_get);
$result_get = mysqli_stmt_get_result($stmt_get);
$voucher_data = mysqli_fetch_assoc($result_get);
mysqli_stmt_close($stmt_get);

// --- Kiểm tra voucher có tồn tại không ---
if (!$voucher_data) {
    $_SESSION['message'] = "Không tìm thấy voucher với ID: " . $voucher_id;
    $_SESSION['message_type'] = "warning";
    header('Location: quanly_voucher.php');
    exit;
}

// --- Lấy lại dữ liệu form nếu có lỗi từ lần submit trước ---
$form_data = $_SESSION['form_data'] ?? $voucher_data; // Ưu tiên dữ liệu lỗi, nếu không thì lấy từ DB
unset($_SESSION['form_data']); // Xóa session sau khi lấy

// --- Định dạng lại ngày tháng cho input datetime-local ---
$ngay_bat_dau_formatted = $form_data['ngay_bat_dau'] ? date('Y-m-d\TH:i', strtotime($form_data['ngay_bat_dau'])) : '';
$ngay_ket_thuc_formatted = $form_data['ngay_ket_thuc'] ? date('Y-m-d\TH:i', strtotime($form_data['ngay_ket_thuc'])) : '';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Voucher</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Copy style từ trang them_voucher.php */
        body { background-color: #f8f9fa; padding-top: 20px; padding-bottom: 20px; }
        .page-header { color: #007bff; text-align: center; margin-bottom: 1.5rem; font-weight: bold; }
        .form-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .control-label { font-weight: bold; }
        .form-horizontal .control-label { text-align: right; padding-top: 7px;}
         @media (max-width: 767px) { .form-horizontal .control-label { text-align: left; margin-bottom: 5px;} }
         .btn-back { margin-bottom: 20px; }
    </style>
</head>
<body>
<?php include_once('header3.php'); // Nếu có ?>

<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">

            <h2 class="page-header">Sửa Voucher (ID: <?php echo $voucher_id; ?>)</h2>

             <a href="quanly_voucher.php" class="btn btn-default btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>

            <?php // Hiển thị thông báo lỗi nếu có từ lần submit trước ?>
             <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade in">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                </div>
            <?php endif; ?>


            <div class="form-container">
                <?php // Form action trỏ đến file xử lý sửa ?>
                <form action="xuly_sua_voucher.php" method="post" class="form-horizontal">
                    <?php // Input ẩn để gửi ID voucher cần sửa ?>
                    <input type="hidden" name="id" value="<?php echo $voucher_id; ?>">

                    <?php /* --- ĐIỀN DỮ LIỆU VÀO FORM --- */ ?>
                    <div class="form-group">
                        <label for="ma_giam_gia" class="col-sm-3 control-label">Mã Voucher <span style="color:red;">*</span>:</label>
                        <div class="col-sm-9">
                             <?php // Hiển thị mã cũ, có thể cho sửa hoặc không tùy yêu cầu ?>
                            <input type="text" class="form-control" id="ma_giam_gia" name="ma_giam_gia" value="<?php echo htmlspecialchars($form_data['ma_giam_gia']); ?>" required pattern="[A-Z0-9_]+" title="Chỉ nhập chữ hoa (A-Z), số (0-9), và gạch dưới (_), viết liền.">
                             <small class="help-block">Viết liền, không dấu, nên dùng chữ hoa, số, gạch dưới.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="loai" class="col-sm-3 control-label">Loại Giảm Giá <span style="color:red;">*</span>:</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="loai" name="loai" required>
                                <?php // Chọn đúng option đã lưu ?>
                                <option value="fixed" <?php echo ($form_data['loai'] == 'fixed') ? 'selected' : ''; ?>>Số tiền cố định (VNĐ)</option>
                                <option value="percent" <?php echo ($form_data['loai'] == 'percent') ? 'selected' : ''; ?>>Phần trăm (%)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="gia_tri" class="col-sm-3 control-label">Giá trị <span style="color:red;">*</span>:</label>
                        <div class="col-sm-9">
                            <input type="number" step="any" min="0" class="form-control" id="gia_tri" name="gia_tri" value="<?php echo htmlspecialchars($form_data['gia_tri']); ?>" required title="Nhập số dương. Nếu loại là %, nhập giá trị từ 0-100.">
                            <small class="help-block" id="gia_tri_help"></small> <?php // JS sẽ cập nhật ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="gia_tri_don_toi_thieu" class="col-sm-3 control-label">Giá trị đơn tối thiểu (VNĐ):</label>
                        <div class="col-sm-9">
                            <input type="number" step="1" min="0" class="form-control" id="gia_tri_don_toi_thieu" name="gia_tri_don_toi_thieu" value="<?php echo htmlspecialchars($form_data['gia_tri_don_toi_thieu'] ?? 0); ?>" placeholder="Để trống hoặc 0 nếu không yêu cầu">
                             <small class="help-block">Áp dụng cho đơn hàng có tổng tiền sản phẩm từ giá trị này trở lên.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ngay_bat_dau" class="col-sm-3 control-label">Ngày bắt đầu:</label>
                        <div class="col-sm-9">
                            <input type="datetime-local" class="form-control" id="ngay_bat_dau" name="ngay_bat_dau" value="<?php echo $ngay_bat_dau_formatted; ?>" title="Để trống nếu có hiệu lực ngay">
                             <small class="help-block">Thời điểm voucher bắt đầu có thể sử dụng.</small>
                        </div>
                    </div>

                     <div class="form-group">
                        <label for="ngay_ket_thuc" class="col-sm-3 control-label">Ngày kết thúc:</label>
                        <div class="col-sm-9">
                            <input type="datetime-local" class="form-control" id="ngay_ket_thuc" name="ngay_ket_thuc" value="<?php echo $ngay_ket_thuc_formatted; ?>" title="Để trống nếu không hết hạn">
                             <small class="help-block">Voucher sẽ không dùng được sau thời điểm này.</small>
                        </div>
                    </div>

                     <div class="form-group">
                        <label for="gioi_han_su_dung" class="col-sm-3 control-label">Giới hạn lượt dùng:</label>
                        <div class="col-sm-9">
                            <input type="number" step="1" min="0" class="form-control" id="gioi_han_su_dung" name="gioi_han_su_dung" value="<?php echo htmlspecialchars($form_data['gioi_han_su_dung'] ?? ''); ?>" placeholder="Để trống nếu không giới hạn">
                             <small class="help-block">Tổng số lần voucher này có thể được áp dụng bởi tất cả người dùng.</small>
                        </div>
                    </div>

                     <div class="form-group">
                        <label for="trang_thai" class="col-sm-3 control-label">Trạng thái:</label>
                        <div class="col-sm-9">
                             <select class="form-control" id="trang_thai" name="trang_thai">
                                <?php // Chọn đúng trạng thái đã lưu ?>
                                <option value="1" <?php echo ($form_data['trang_thai'] == 1) ? 'selected' : ''; ?>>Hoạt động</option>
                                <option value="0" <?php echo ($form_data['trang_thai'] == 0) ? 'selected' : ''; ?>>Không hoạt động (Khóa)</option>
                             </select>
                        </div>
                    </div>

                     <div class="form-group"> <?php // Hiển thị số lần đã dùng (chỉ xem) ?>
                        <label class="col-sm-3 control-label">Đã sử dụng:</label>
                        <div class="col-sm-9">
                            <p class="form-control-static"><?php echo number_format($voucher_data['so_lan_da_dung'] ?? 0); ?> lần</p>
                        </div>
                    </div>


                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập Nhật Voucher</button>
                            <a href="quanly_voucher.php" class="btn btn-default"> Hủy</a> <?php // Nút hủy ?>
                        </div>
                    </div>
                </form>
            </div></div></div></div><?php include_once('footer.php'); // Giả sử footer chứa JS cần thiết ?>

<?php /* Giữ lại script nhỏ để thay đổi gợi ý cho ô Giá trị */ ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('loai');
    var valueHelp = document.getElementById('gia_tri_help');
    var valueInput = document.getElementById('gia_tri');

    function updateHelpText() {
        if (typeSelect.value === 'percent') {
            valueHelp.textContent = 'Nhập giá trị phần trăm (ví dụ: 10 cho 10%, 15.5 cho 15.5%). Nên nhập từ 0-100.';
            valueInput.step = 'any';
            valueInput.placeholder = 'Nhập phần trăm (%)';
        } else { // fixed
            valueHelp.textContent = 'Nhập số tiền giảm (ví dụ: 50000 cho 50,000 VNĐ).';
            valueInput.step = '1';
            valueInput.placeholder = 'Nhập số tiền (VNĐ)';
        }
    }
    if(typeSelect && valueHelp && valueInput) { // Kiểm tra element tồn tại
       typeSelect.addEventListener('change', updateHelpText);
       updateHelpText(); // Gọi lần đầu
    }
});
</script>

</body>
</html>

<?php if(isset($conn)) mysqli_close($conn); ?>