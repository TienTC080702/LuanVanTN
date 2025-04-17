<?php
// --- Bao gồm file kết nối CSDL ---
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

// --- Lấy dữ liệu Phiếu Nhập với JOIN để lấy Tên NCC ---
// Sửa câu truy vấn SQL:
$sl_phieunhap = "SELECT
                    pn.*, -- Lấy tất cả các cột từ bảng phieunhap (pn)
                    ncc.TenNCC -- Lấy cột TenNCC từ bảng nhacungcap (ncc)
                 FROM phieunhap AS pn -- Đặt bí danh pn cho bảng phieunhap
                 LEFT JOIN nhacungcap AS ncc ON pn.idNCC = ncc.idNCC -- Kết nối bảng dựa trên idNCC
                 ORDER BY pn.idPN DESC"; // Sắp xếp theo idPN giảm dần

$rs_phieunhap = mysqli_query($conn, $sl_phieunhap);

// Kiểm tra lỗi truy vấn cơ bản
if (!$rs_phieunhap) {
    echo "Đã xảy ra lỗi khi tải danh sách phiếu nhập. Vui lòng thử lại sau.";
    // die("Lỗi truy vấn CSDL: " . mysqli_error($conn)); // Bật dòng này khi cần gỡ lỗi
    exit;
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
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container-fluid mt-3">

    <h3 class="page-title">DANH SÁCH PHIẾU NHẬP</h3>

    <div class="add-button-container">
        <a href="them_phieu_nhap.php">
            <button type="button" class="btn btn-info">
                <i class="fas fa-plus"></i> THÊM PHIẾU NHẬP
            </button>
        </a>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-bordered table-hover table-striped table-sm">
            <thead class="text-center table-primary">
                <tr>
                    <th>Mã PN</th>
                    <th>Mã SP</th>
                    <th style="min-width: 200px;">Tên Sản Phẩm</th>
                    <th>Đơn Giá Nhập</th> <th>Số Lượng Nhập</th>
                    <th>Ngày Nhập</th>
                    <th style="min-width: 150px;">Tên Nhà Cung Cấp</th> <th>Tổng Tiền (VNĐ)</th>
                    <th>In Phiếu</th>
                    </tr>
            </thead>
            <tbody>
                <?php
                if ($rs_phieunhap && mysqli_num_rows($rs_phieunhap) > 0) {
                    while ($r = $rs_phieunhap->fetch_assoc()) {
                        // --- Lấy tên sản phẩm (Nên JOIN luôn ở query chính) ---
                        $tenSP = "N/A";
                        // Tạm thời vẫn query riêng lẻ theo code cũ của bạn
                        $sl_sp = "SELECT TenSP FROM sanpham WHERE idSP=" . intval($r['idSP']);
                        $rs_sp_inner = mysqli_query($conn, $sl_sp);
                        if ($rs_sp_inner && $d = mysqli_fetch_assoc($rs_sp_inner)) {
                            $tenSP = htmlspecialchars($d['TenSP']);
                        }
                        if($rs_sp_inner) mysqli_free_result($rs_sp_inner);
                        // --- Kết thúc lấy tên sản phẩm ---

                        $ngayNhapFormatted = date('d/m/Y', strtotime($r['NgayNhap']));
                        $tongTienFormatted = number_format($r['TongTien'] ?? 0, 0, ',', '.'); // Dùng ?? 0 nếu có thể NULL
                        // *** Sửa cách lấy Tên Nhà Cung Cấp ***
                        $tenNCC = htmlspecialchars($r['TenNCC'] ?? 'N/A'); // Lấy từ kết quả JOIN (cột TenNCC từ bảng ncc)

                        // Lấy và format Đơn giá nhập (nếu cột đó tồn tại trong $r)
                        $donGiaNhapFormatted = isset($r['DonGiaNhap']) ? number_format($r['DonGiaNhap'], 0, ',', '.') : 'N/A';
                ?>
                        <tr>
                            <td class="text-center"><?php echo $r['idPN']; ?></td>
                            <td class="text-center"><?php echo $r['idSP']; ?></td>
                            <td><?php echo $tenSP; ?></td>
                            <td class="text-end"><?php echo $donGiaNhapFormatted; ?></td> <td class="text-end"><?php echo $r['SoLuong']; ?></td>
                            <td class="text-center"><?php echo $ngayNhapFormatted; ?></td>
                            <td><?php echo $tenNCC; ?></td> <td class="text-end"><?php echo $tongTienFormatted; ?></td>
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
                    // Sửa colspan thành 9 vì thêm cột Đơn Giá Nhập
                    echo '<tr><td colspan="9" class="text-center">Không có dữ liệu phiếu nhập nào để hiển thị.</td></tr>';
                }
                if($rs_phieunhap) mysqli_free_result($rs_phieunhap);
                ?>
            </tbody>
        </table>
    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>