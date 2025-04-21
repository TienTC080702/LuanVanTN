<?php
session_start();
// Include file kết nối CSDL (điều chỉnh đường dẫn nếu cần)
include_once('../connection/connect_database.php');

// --- BẮT ĐẦU PHẦN INCLUDE HEADER ADMIN ---
// !!! Giữ lại các include header giống trang khuyến mãi của bạn !!!
include_once('header1.php');
include_once('header2.php'); // Giả sử header2 chứa CSS cần thiết
// --- KẾT THÚC PHẦN INCLUDE HEADER ADMIN ---


// Kiểm tra kết nối
if (!isset($conn) || !$conn) {
    die("Lỗi kết nối CSDL.");
}
mysqli_set_charset($conn, 'utf8');

// Lấy danh sách voucher từ CSDL, sắp xếp theo ID mới nhất
$sql_select = "SELECT * FROM vouchers ORDER BY id DESC";
$result = mysqli_query($conn, $sql_select);
if (!$result) {
    // Có thể hiển thị lỗi thân thiện hơn thay vì die()
     echo "Lỗi truy vấn CSDL: " . mysqli_error($conn);
     // Hoặc ghi log lỗi
     error_log("Lỗi truy vấn danh sách voucher: " . mysqli_error($conn));
     $result = null; // Đặt $result thành null để phần dưới xử lý
}

?>
<!DOCTYPE html> <?php // Đảm bảo thẻ này nằm trong header1.php hoặc ở đây nếu chưa có ?>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php // Giả định header1.php hoặc header2.php đã include Bootstrap CSS ?>
    <title>Quản Lý Voucher</title> <?php // Đổi title ?>
    <?php // Giả định header2.php đã include Font Awesome ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <?php // Giữ lại nếu cần ?>
    <link href="../css/hieuung.css" type="text/css" rel="stylesheet"> <?php // Giữ lại nếu cần ?>

    <?php /* --- SAO CHÉP CSS TỪ TRANG KHUYẾN MÃI --- */ ?>
    <style>
        h3.page-title { color: #007bff; text-align: center; margin: 1.5rem 0; font-weight: bold; } /* Sử dụng màu xanh dương Bootstrap */
        .add-button-container { margin-bottom: 1rem; }
        .text-center { text-align: center !important; vertical-align: middle !important; }
        .text-start { text-align: left !important; vertical-align: middle !important; }
        .text-end { text-align: right !important; vertical-align: middle !important; }
        .table-responsive-custom { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-actions a { margin: 0 3px; }
        .badge { font-size: 0.9em; padding: 0.4em 0.6em;}
        .table th, .table td { vertical-align: middle; padding: 0.6rem; /*word-break: break-word;*/ } /* Tạm bỏ word-break xem có cần không */
        .table thead th { /* Định dạng header giống trang KM */
             background-color: #e9ecef; /* Màu nền header */
             border-color: #dee2e6;
             font-weight: bold;
        }
        /* Thêm màu nền xen kẽ nếu muốn */
        .table-striped tbody tr:nth-of-type(odd) {
           background-color: rgba(0, 0, 0, 0.04);
        }
        /* Định nghĩa lại màu badge nếu cần */
         .badge.bg-success { background-color: #28a745 !important; color: white;}
         .badge.bg-secondary { background-color: #6c757d !important; color: white;}
         .badge.bg-danger { background-color: #dc3545 !important; color: white;} /* Thêm màu đỏ nếu cần */

         /* Định dạng nút thao tác nhỏ hơn */
         .table-actions .btn-sm {
             padding: 0.2rem 0.4rem;
             font-size: 0.8rem;
         }
         /* Thêm class để giới hạn chiều rộng cột nếu cần */
        .col-id { width: 5%; }
        .col-code { width: 15%; }
        .col-type { width: 10%; }
        .col-value { width: 10%; }
        .col-min-order { width: 12%; }
        .col-date { width: 10%; }
        .col-limit { width: 8%; }
        .col-used { width: 5%; }
        .col-status { width: 10%; }
        /* Cột ngày tạo và hành động tự động chia sẻ phần còn lại */

    </style>
</head>
<body>
<?php include_once('header3.php'); // Giữ lại include header3 như trang KM ?>

<div class="container-fluid mt-3">
    <h3 class="page-title">DANH SÁCH VOUCHER KHUYẾN MÃI</h3> <?php // Đổi tiêu đề ?>

    <div class="add-button-container">
        <a href="them_voucher.php"> <?php // Đổi link thêm ?>
            <button type="button" class="btn btn-success">
                <i class="fas fa-plus"></i> THÊM VOUCHER
            </button>
        </a>
    </div>

    <?php // Hiển thị thông báo (nếu có) - Giữ nguyên ?>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade in">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        </div>
    <?php endif; ?>


    <div class="table-responsive-custom">
        <table class="table table-bordered table-hover table-striped table-sm">
            <?php // Sử dụng thead class của trang KM ?>
            <thead class="text-center table-primary"> <?php // Hoặc dùng class thead-light nếu muốn nền xám nhạt như style CSS ở trên ?>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-code text-start">Mã Code</th> <?php // Căn trái ?>
                    <th class="col-type">Loại</th>
                    <th class="col-value text-end">Giá trị</th> <?php // Căn phải ?>
                    <th class="col-min-order text-end">Đơn tối thiểu</th> <?php // Căn phải ?>
                    <th class="col-date">Bắt đầu</th>
                    <th class="col-date">Kết thúc</th>
                    <th class="col-limit">Giới hạn</th>
                    <th class="col-used">Đã dùng</th>
                    <th class="col-status">Trạng thái</th>
                    <th class="col-date">Ngày tạo</th>
                    <th>Hành động</th> <?php // Tự động co giãn ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $stt = 0; // Biến đếm STT nếu cần, nhưng bảng này có ID rồi
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = $result->fetch_assoc()) { // Dùng fetch_assoc trên $result
                        $stt++;
                        // Xác định trạng thái badge
                        $trangThaiBadge = '';
                        if ($row['trang_thai'] == 1) {
                            // Kiểm tra thêm ngày hết hạn nếu cần hiển thị khác
                            $now = time();
                            $ngay_ket_thuc_ts = $row['ngay_ket_thuc'] ? strtotime($row['ngay_ket_thuc']) : null;
                            if ($ngay_ket_thuc_ts !== null && $now > $ngay_ket_thuc_ts) {
                                $trangThaiBadge = '<span class="badge bg-secondary">Hết hạn</span>';
                            } else {
                                $trangThaiBadge = '<span class="badge bg-success">Hoạt động</span>';
                            }
                        } else {
                             $trangThaiBadge = '<span class="badge bg-danger">Đã khóa</span>'; // Dùng màu đỏ cho khóa
                        }

                ?>
                        <tr>
                            <?php /* --- SỬ DỤNG TÊN CỘT ĐÚNG TỪ CSDL VOUCHERS --- */ ?>
                            <td class="text-center"><?php echo $row['id']; ?></td>
                            <td class="text-start"><b><?php echo htmlspecialchars($row['ma_giam_gia']); ?></b></td>
                            <td class="text-center"><?php echo ($row['loai'] == 'fixed') ? 'Cố định' : 'Phần trăm'; ?></td>
                            <td class="text-end">
                                <?php
                                if ($row['loai'] == 'fixed') { echo number_format($row['gia_tri'], 0) . ' VNĐ'; }
                                else { echo rtrim(rtrim(number_format($row['gia_tri'], 2, '.', ''), '0'), '.') . ' %'; }
                                ?>
                            </td>
                            <td class="text-end"><?php echo ($row['gia_tri_don_toi_thieu'] > 0) ? number_format($row['gia_tri_don_toi_thieu'], 0) . ' VNĐ' : 'Không'; ?></td>
                            <td class="text-center"><?php echo $row['ngay_bat_dau'] ? date('d/m/Y', strtotime($row['ngay_bat_dau'])) : 'N/A'; ?></td> <?php // Bỏ giờ phút cho gọn ?>
                            <td class="text-center"><?php echo $row['ngay_ket_thuc'] ? date('d/m/Y', strtotime($row['ngay_ket_thuc'])) : 'Không giới hạn'; ?></td> <?php // Bỏ giờ phút cho gọn ?>
                            <td class="text-center"><?php echo $row['gioi_han_su_dung'] !== null ? number_format($row['gioi_han_su_dung'], 0) : '∞'; ?></td> <?php // Ký hiệu vô cực ?>
                            <td class="text-center"><?php echo number_format($row['so_lan_da_dung'], 0); ?></td>
                            <td class="text-center"><?php echo $trangThaiBadge; ?></td>
                            <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($row['ngay_tao'])); ?></td> <?php // Giữ giờ phút ?>
                            <td class="text-center table-actions">
                                <a href="sua_voucher.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm" title="Sửa voucher"> <?php // Đổi link sửa ?>
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="xuly_xoa_voucher.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" title="Xóa voucher" onclick="return confirm('Bạn có chắc chắn muốn xóa voucher [<?php echo htmlspecialchars($row['ma_giam_gia']); ?>] này?');"> <?php // Đổi link xóa ?>
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                <?php
                    } // Kết thúc while
                } else { // Nếu không có voucher nào
                    echo '<tr><td colspan="12" class="text-center">Chưa có voucher nào.</td></tr>'; // Colspan = 12 cột
                }
                if($result) mysqli_free_result($result); // Giải phóng kết quả nếu có
                ?>
            </tbody>
        </table>
    </div></div></div></div><?php
// --- BẮT ĐẦU PHẦN INCLUDE FOOTER ADMIN ---
// !!! Giữ lại include footer giống trang khuyến mãi của bạn !!!
include_once('footer.php');
// --- KẾT THÚC PHẦN INCLUDE FOOTER ADMIN ---

mysqli_close($conn); // Đóng kết nối CSDL ở cuối cùng
?>
<?php // Thêm thẻ đóng PHP nếu footer.php không có ?>

</body> <?php // Thẻ đóng body nên nằm trong footer.php ?>
</html> <?php // Thẻ đóng html nên nằm trong footer.php ?>