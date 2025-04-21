<?php
session_start();
// --- BẮT ĐẦU PHẦN INCLUDE HEADER ADMIN ---
// !!! Giữ lại các include header giống trang quản lý voucher của bạn !!!
include_once('header1.php');
include_once('header2.php'); // Đảm bảo có include này giống trang list
// --- KẾT THÚC PHẦN INCLUDE HEADER ADMIN ---

// Không cần include connect_database.php ở đây
?>
<!DOCTYPE html> <?php // Đảm bảo thẻ này nằm trong header1.php hoặc ở đây nếu chưa có ?>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php // Giả định header1.php hoặc header2.php đã include Bootstrap CSS ?>
    <title>Thêm Voucher Mới</title>
    <?php // Giả định header2.php đã include Font Awesome ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <?php // Giữ lại nếu cần ?>
    <style>
        /* Style cơ bản kế thừa từ trang danh sách hoặc file CSS chung */
        /* Style riêng cho form thêm/sửa */
        h2.page-header { color: #007bff; text-align: center; margin: 1.5rem 0; font-weight: bold; }
        .form-container {
            background-color: #fff; /* Nền trắng cho form */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Bóng đổ nhẹ hơn */
            margin-top: 20px; /* Khoảng cách với nút quay lại */
        }
        .control-label { font-weight: bold; } /* In đậm label */
        .form-group label, .mb-3 label { /* Đảm bảo label căn lề phải trên màn hình lớn */
           text-align: right;
        }
         /* Chỉnh label trên mobile nếu cần */
        @media (max-width: 767px) {
            .form-group label, .mb-3 label {
                text-align: left;
                 margin-bottom: 5px;
            }
        }
        .btn-back { margin-bottom: 15px; } /* Khoảng cách nút quay lại */
    </style>
</head>
<body>
<?php include_once('header3.php'); // Giữ lại include header3 như trang list ?>

<div class="container-fluid mt-3"> <?php // Dùng container-fluid giống trang list ?>
    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto"> <?php // Căn giữa cột nội dung form ?>

            <h2 class="page-header">THÊM VOUCHER MỚI</h2>

            <?php // Sửa lại link Quay lại cho đúng trang danh sách voucher ?>
            <a href="quanly_voucher.php" class="btn btn-secondary btn-back"> <?php // Dùng btn-secondary cho màu xám ?>
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>

            <div class="form-container">
                <form action="xuly_them_voucher.php" method="post"> <?php // Bỏ form-horizontal ?>

                    <?php /* --- Sử dụng cấu trúc .mb-3 và .row > .col cho Bootstrap 5 --- */ ?>

                    <div class="row mb-3">
                        <label for="ma_giam_gia" class="col-md-3 col-form-label control-label">Mã Voucher <span style="color:red;">*</span>:</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="ma_giam_gia" name="ma_giam_gia" placeholder="Ví dụ: GIAM10K, KMTHANG5" required pattern="[A-Z0-9]+" title="Chỉ nhập chữ hoa (A-Z) và số (0-9), viết liền.">
                            <small class="form-text text-muted">Viết liền, không dấu, nên dùng chữ hoa và số.</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="loai" class="col-md-3 col-form-label control-label">Loại Giảm Giá <span style="color:red;">*</span>:</label>
                        <div class="col-md-9">
                            <select class="form-select" id="loai" name="loai" required> <?php // Dùng form-select cho Bootstrap 5 ?>
                                <option value="fixed">Số tiền cố định (VNĐ)</option>
                                <option value="percent">Phần trăm (%)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="gia_tri" class="col-md-3 col-form-label control-label">Giá trị <span style="color:red;">*</span>:</label>
                        <div class="col-md-9">
                            <input type="number" step="any" min="0" class="form-control" id="gia_tri" name="gia_tri" placeholder="Nhập số tiền (VND) hoặc phần trăm (%)" required title="Nhập số dương. Nếu loại là %, nhập giá trị từ 0-100.">
                            <small class="form-text text-muted" id="gia_tri_help">Nhập số tiền giảm (ví dụ: 50000) nếu chọn loại cố định.</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="gia_tri_don_toi_thieu" class="col-md-3 col-form-label control-label">Đơn tối thiểu (VNĐ):</label>
                        <div class="col-md-9">
                            <input type="number" step="1" min="0" class="form-control" id="gia_tri_don_toi_thieu" name="gia_tri_don_toi_thieu" placeholder="Để trống hoặc 0 nếu không yêu cầu" value="0">
                            <small class="form-text text-muted">Áp dụng cho đơn hàng có tổng giá trị từ mức này trở lên.</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="ngay_bat_dau" class="col-md-3 col-form-label control-label">Ngày bắt đầu:</label>
                        <div class="col-md-9">
                            <input type="datetime-local" class="form-control" id="ngay_bat_dau" name="ngay_bat_dau" title="Để trống nếu có hiệu lực ngay">
                            <small class="form-text text-muted">Thời điểm voucher bắt đầu có thể sử dụng. Để trống = áp dụng ngay.</small>
                        </div>
                    </div>

                     <div class="row mb-3">
                        <label for="ngay_ket_thuc" class="col-md-3 col-form-label control-label">Ngày kết thúc:</label>
                        <div class="col-md-9">
                            <input type="datetime-local" class="form-control" id="ngay_ket_thuc" name="ngay_ket_thuc" title="Để trống nếu không hết hạn">
                            <small class="form-text text-muted">Voucher sẽ không dùng được sau thời điểm này. Để trống = không hết hạn.</small>
                        </div>
                    </div>

                     <div class="row mb-3">
                        <label for="gioi_han_su_dung" class="col-md-3 col-form-label control-label">Giới hạn lượt dùng:</label>
                        <div class="col-md-9">
                            <input type="number" step="1" min="0" class="form-control" id="gioi_han_su_dung" name="gioi_han_su_dung" placeholder="Để trống nếu không giới hạn">
                            <small class="form-text text-muted">Tổng số lần voucher này có thể được áp dụng. Để trống = không giới hạn.</small>
                        </div>
                    </div>

                     <div class="row mb-3">
                        <label for="trang_thai" class="col-md-3 col-form-label control-label">Trạng thái:</label>
                        <div class="col-md-9">
                             <select class="form-select" id="trang_thai" name="trang_thai"> <?php // Dùng form-select ?>
                                 <option value="1" selected>Hoạt động</option>
                                 <option value="0">Không hoạt động (Khóa)</option>
                             </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-9 offset-md-3"> <?php // Offset cột input để nút thẳng hàng với input ?>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu Voucher</button>
                            <button type="reset" class="btn btn-outline-secondary"><i class="fas fa-undo"></i> Nhập lại</button> <?php // Dùng outline cho nút reset ?>
                        </div>
                    </div>
                </form>
            </div> </div> </div> </div> <?php
// --- BẮT ĐẦU PHẦN INCLUDE FOOTER ADMIN ---
// !!! Giữ lại include footer giống trang quản lý voucher của bạn !!!
include_once('footer.php');
// --- KẾT THÚC PHẦN INCLUDE FOOTER ADMIN ---
?>

<?php /* Giữ lại script nhỏ để thay đổi gợi ý cho ô Giá trị */ ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('loai');
    var valueHelp = document.getElementById('gia_tri_help');
    var valueInput = document.getElementById('gia_tri');

    function updateHelpText() {
        if (typeSelect.value === 'percent') {
            valueHelp.textContent = 'Nhập giá trị phần trăm (ví dụ: 10 cho 10%, 15.5 cho 15.5%). Nên nhập từ 0-100.';
            valueInput.step = 'any'; // Cho phép số thập phân
            valueInput.placeholder = 'Nhập phần trăm (%)';
            // Optional: Thêm validation max=100
            // valueInput.max = '100';
        } else { // fixed
            valueHelp.textContent = 'Nhập số tiền giảm (ví dụ: 50000 cho 50,000 VNĐ).';
            valueInput.step = '1'; // Chỉ cho phép số nguyên (tiền VND)
            valueInput.placeholder = 'Nhập số tiền (VNĐ)';
            // Optional: Remove max validation if it was set for percent
            // valueInput.removeAttribute('max');
        }
    }

    typeSelect.addEventListener('change', updateHelpText);
    // Gọi lần đầu khi tải trang
    updateHelpText();

    // Thêm kiểm tra ngày kết thúc phải sau ngày bắt đầu (nếu cả 2 được nhập)
    var startDateInput = document.getElementById('ngay_bat_dau');
    var endDateInput = document.getElementById('ngay_ket_thuc');
    var form = document.querySelector('form'); // Lấy form

    form.addEventListener('submit', function(event) {
        if (startDateInput.value && endDateInput.value) {
            var startDate = new Date(startDateInput.value);
            var endDate = new Date(endDateInput.value);
            if (endDate < startDate) {
                alert('Lỗi: Ngày kết thúc không được trước ngày bắt đầu.');
                endDateInput.focus(); // Focus vào ô ngày kết thúc
                event.preventDefault(); // Ngăn form submit
            }
        }
    });
});
</script>

</body>
</html>