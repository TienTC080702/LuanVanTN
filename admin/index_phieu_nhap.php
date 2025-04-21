<?php
// Bắt đầu session nếu cần (ví dụ: để lấy thông báo flash)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Bao gồm file kết nối CSDL ---
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

// --- Lấy và xóa thông báo flash (nếu có từ trang thêm/sửa) ---
$flash_message_pn = '';
if (isset($_SESSION['flash_message_pn'])) {
    $flash_message_pn = "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_SESSION['flash_message_pn']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    unset($_SESSION['flash_message_pn']);
}
// Thêm xử lý cho flash_error_pn nếu cần

// --- Lấy dữ liệu Phiếu Nhập với JOIN để lấy Tên NCC và Tên SP ---
// ***** TỐI ƯU HÓA: JOIN thêm bảng sanpham để lấy TenSP luôn *****
$sl_phieunhap = "SELECT
                    pn.*,          -- Lấy tất cả các cột từ bảng phieunhap (pn)
                    ncc.TenNCC,    -- Lấy cột TenNCC từ bảng nhacungcap (ncc)
                    sp.TenSP       -- Lấy cột TenSP từ bảng sanpham (sp)
                 FROM phieunhap AS pn -- Đặt bí danh pn cho bảng phieunhap
                 LEFT JOIN nhacungcap AS ncc ON pn.idNCC = ncc.idNCC -- Kết nối với nhà cung cấp
                 LEFT JOIN sanpham AS sp ON pn.idSP = sp.idSP       -- Kết nối với sản phẩm
                 ORDER BY pn.idPN DESC"; // Sắp xếp theo idPN giảm dần

$rs_phieunhap = mysqli_query($conn, $sl_phieunhap);

// Kiểm tra lỗi truy vấn cơ bản
$query_error_message = null;
if (!$rs_phieunhap) {
    $query_error_message = "Đã xảy ra lỗi khi tải danh sách phiếu nhập. Vui lòng thử lại sau.";
    error_log("Lỗi truy vấn CSDL (index_phieu_nhap): " . mysqli_error($conn)); // Ghi log lỗi
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Danh Sách Phiếu Nhập</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
          /* Định dạng tiêu đề trang */
          h3.page-title { color: #1F75FE; text-align: center; margin: 1.5rem 0; font-weight: bold; }
          .add-button-container { margin-bottom: 1rem; text-align: left; }
          .text-end { text-align: right !important; }
          .text-center { text-align: center !important; vertical-align: middle !important; }
          .table-responsive-custom { overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 10px; }
          .btn .fas, .btn .fa { vertical-align: middle; margin: 0 2px; }
          .btn-print { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
          .table th, .table td { vertical-align: middle; }
          /* Thêm khoảng cách giữa các nút */
          .add-button-container .btn { margin-right: 10px; }
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container-fluid mt-3">

    <h3 class="page-title">DANH SÁCH PHIẾU NHẬP</h3>

    <?php
        // Hiển thị thông báo flash từ session
        echo $flash_message_pn;
        // Hiển thị lỗi truy vấn nếu có
         if ($query_error_message) {
             echo "<div class='alert alert-danger'>".htmlspecialchars($query_error_message)."</div>";
         }
    ?>

    <div class="add-button-container">
        <?php // ***** THAY ĐỔI: Thêm nút mới và sửa text nút cũ ***** ?>
        <a href="them_phieu_nhap.php" title="Tạo phiếu nhập cho sản phẩm ĐÃ CÓ trong cửa hàng">
            <button type="button" class="btn btn-info">
                <i class="fas fa-plus"></i> NHẬP HÀNG (SP Cũ)
            </button>
        </a>
        <a href="them_phieu_nhap_sp_moi.php" title="Tạo sản phẩm MỚI và nhập lô hàng đầu tiên">
            <button type="button" class="btn btn-success"> <?php // Đổi màu nút ?>
                <i class="fas fa-box-open"></i> NHẬP HÀNG (SP Mới)
            </button>
        </a>
         <?php // ***** Kết thúc thay đổi ***** ?>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-bordered table-hover table-striped table-sm">
            <thead class="text-center table-primary">
                <tr>
                    <th>Mã PN</th>
                    <th>Mã SP</th>
                    <th style="min-width: 200px;">Tên Sản Phẩm</th>
                    <th>Đơn Giá Nhập</th>
                    <th>Số Lượng</th>
                    <th>Ngày Nhập</th>
                    <th style="min-width: 150px;">Tên Nhà Cung Cấp</th>
                    <th>Tổng Tiền (VNĐ)</th>
                    <th>In Phiếu</th>
                    <?php // Thêm cột thao tác khác nếu cần (Xóa, Sửa PN...) ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($rs_phieunhap && mysqli_num_rows($rs_phieunhap) > 0) {
                    while ($r = $rs_phieunhap->fetch_assoc()) {
                        // ***** TỐI ƯU HÓA: Lấy TenSP từ kết quả JOIN *****
                        $tenSP = htmlspecialchars($r['TenSP'] ?? 'N/A'); // Lấy trực tiếp từ $r

                        $ngayNhapFormatted = date('d/m/Y', strtotime($r['NgayNhap']));
                        $tongTienFormatted = number_format($r['TongTien'] ?? 0, 0, ',', '.');
                        $tenNCC = htmlspecialchars($r['TenNCC'] ?? 'N/A');
                        $donGiaNhapFormatted = isset($r['DonGiaNhap']) ? number_format($r['DonGiaNhap'], 0, ',', '.') : 'N/A';
                ?>
                        <tr>
                            <td class="text-center"><?php echo $r['idPN']; ?></td>
                            <td class="text-center"><?php echo $r['idSP']; ?></td>
                            <td><?php echo $tenSP; ?></td>
                            <td class="text-end"><?php echo $donGiaNhapFormatted; ?></td>
                            <td class="text-center"><?php echo $r['SoLuong']; ?></td> <?php // Đổi thành text-center ?>
                            <td class="text-center"><?php echo $ngayNhapFormatted; ?></td>
                            <td><?php echo $tenNCC; ?></td>
                            <td class="text-end"><?php echo $tongTienFormatted; ?></td>
                            <td class="text-center">
                                <a href="in_phieu_nhap.php?idPN=<?php echo $r['idPN']; ?>" target="_blank" title="In phiếu nhập <?php echo $r['idPN']; ?>">
                                    <button class="btn btn-primary btn-sm btn-print">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </a>
                            </td>
                        </tr>
                <?php
                    } // Kết thúc while
                } else {
                    // Sửa colspan thành 9
                    echo '<tr><td colspan="9" class="text-center">Không có dữ liệu phiếu nhập nào để hiển thị.</td></tr>';
                }
                if($rs_phieunhap) mysqli_free_result($rs_phieunhap);
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once('footer.php'); ?>

<?php // Giữ lại các script cần thiết nếu có (Bootstrap JS...) ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php
    if (isset($conn) && $conn) { // Đóng kết nối CSDL
        mysqli_close($conn);
    }
?>
</body>
</html>