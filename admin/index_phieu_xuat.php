<?php
// --- Bao gồm file kết nối CSDL ---
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

// --- Lấy dữ liệu Phiếu Xuất (Logic gốc) ---
$sl_phieuxuat = "SELECT * FROM phieuxuat ORDER BY idPX DESC"; // Sắp xếp theo ID giảm dần
$rs_phieuxuat = mysqli_query($conn, $sl_phieuxuat);

// Kiểm tra lỗi truy vấn cơ bản
if (!$rs_phieuxuat) {
    // Hiển thị lỗi thân thiện hơn
    echo "Đã xảy ra lỗi khi tải danh sách phiếu xuất. Vui lòng thử lại sau.";
    // die("Không thể truy vấn CSDL: " . mysqli_error($conn)); // Bật khi cần debug
    exit; // Dừng script nếu lỗi
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Danh Sách Phiếu Xuất</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Định dạng tiêu đề trang */
        h3.page-title {
            color: #1F75FE; /* Màu xanh dương */
            text-align: center;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }
        /* Khoảng cách cho nút thêm mới */
        .add-button-container {
            margin-bottom: 1rem;
            text-align: left;
        }
        /* Căn lề phải */
        .text-end {
             text-align: right !important;
        }
         /* Căn giữa */
        .text-center {
            text-align: center !important;
            vertical-align: middle !important; /* Căn giữa chiều dọc */
        }
        /* Div cuộn ngang */
        .table-responsive-custom {
             overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 10px;
        }
        /* Style cho icon trong nút */
        .btn .fas, .btn .fa {
             vertical-align: middle;
             margin: 0 2px;
        }
        /* Nút in nhỏ hơn */
         .btn-print {
             padding: 0.25rem 0.5rem;
             font-size: 0.875rem;
         }
         /* Căn giữa dọc cho ô */
         .table th, .table td {
             vertical-align: middle;
         }
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container-fluid mt-3">

    <h3 class="page-title">DANH SÁCH PHIẾU XUẤT</h3>

    <div class="add-button-container">
        <a href="them_phieu_xuat.php">
            <button type="button" class="btn btn-info">
                <i class="fas fa-plus"></i> THÊM PHIẾU XUẤT
            </button>
        </a>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-bordered table-hover table-striped table-sm">
            <thead class="text-center table-primary"> <tr>
                    <th style="width: 5%;">Mã PX</th>
                    <th style="width: 5%;">Mã SP</th>
                    <th style="width: 25%;">Tên Sản Phẩm</th>
                    <th style="width: 10%;">Số Lượng Xuất</th>
                    <th style="width: 10%;">Ngày Xuất</th>
                    <th style="width: 20%;">Tên Khách Hàng</th>
                    <th style="width: 15%;">Tổng Tiền (VNĐ)</th>
                    <th style="width: 10%;">In Phiếu</th>
                </tr>
            </thead>
            <tbody> <?php
                if ($rs_phieuxuat && mysqli_num_rows($rs_phieuxuat) > 0) {
                    while ($r = $rs_phieuxuat->fetch_assoc()) {
                        // --- Lấy tên sản phẩm (Logic gốc - N+1 Query) ---
                        // Khuyến nghị JOIN ở query chính
                        $tenSP = "N/A";
                        $sl_sp = "SELECT TenSP FROM sanpham WHERE idSP=" . intval($r['idSP']);
                        $rs_sp_inner = mysqli_query($conn, $sl_sp);
                        if ($rs_sp_inner && $d = mysqli_fetch_assoc($rs_sp_inner)) {
                            $tenSP = htmlspecialchars($d['TenSP']);
                        }
                        if($rs_sp_inner) mysqli_free_result($rs_sp_inner);
                        // --- Kết thúc lấy tên sản phẩm ---

                        // Format dữ liệu
                        $ngayXuatFormatted = date('d/m/Y', strtotime($r['NgayXuat']));
                        $tongTienFormatted = number_format($r['TongTien'] ?? 0, 0, ',', '.');
                        $tenKH = htmlspecialchars($r['TenKhachHang']); // Lấy tên KH từ cột cũ
                ?>
                        <tr> <td class="text-center"><?php echo $r['idPX']; ?></td>
                            <td class="text-center"><?php echo $r['idSP']; ?></td>
                            <td><?php echo $tenSP; ?></td>
                            <td class="text-end"><?php echo $r['SoLuong']; ?></td>
                            <td class="text-center"><?php echo $ngayXuatFormatted; ?></td>
                            <td><?php echo $tenKH; ?></td>
                            <td class="text-end"><?php echo $tongTienFormatted; ?></td>
                            <td class="text-center">
                                <a href="in_phieu_xuat.php?idPX=<?php echo $r['idPX']; ?>" target="_blank" title="In phiếu xuất <?php echo $r['idPX']; ?>">
                                    <button class="btn btn-primary btn-sm btn-print">
                                        <i class="fas fa-print"></i> </button>
                                </a>
                            </td>
                        </tr>
                <?php
                    } // Kết thúc while
                } else {
                    // Thông báo không có dữ liệu
                    echo '<tr><td colspan="8" class="text-center">Không có dữ liệu phiếu xuất nào để hiển thị.</td></tr>';
                }
                // Giải phóng bộ nhớ
                if($rs_phieuxuat) mysqli_free_result($rs_phieuxuat);
                ?>
            </tbody> </table>
    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>