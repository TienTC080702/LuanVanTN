<?php
// ** BẮT BUỘC CÓ session_start() Ở ĐẦU **
session_start();
ob_start(); // Bắt đầu bộ đệm

include_once ('../connection/connect_database.php');

// Kiểm tra kết nối DB
if (!isset($conn) || !$conn) {
    die("Không thể kết nối CSDL");
}

// Lấy dữ liệu đơn hàng của user đang đăng nhập
$query_u = null; // Khởi tạo biến
$has_orders = false; // Biến cờ kiểm tra có đơn hàng không

if (isset($_SESSION['IDUser'])) {
    $userID = (int)$_SESSION['IDUser'];
    $sql_u = "SELECT * FROM donhang WHERE idUser = " . $userID . " ORDER BY ThoiDiemDatHang DESC";
    $query_u = mysqli_query($conn, $sql_u);
    if (!$query_u) {
        error_log("Query failed for user orders: " . mysqli_error($conn));
    } else {
        if (mysqli_num_rows($query_u) > 0) {
             $has_orders = true;
        }
    }
} else {
    header('Location: DangNhap.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include_once("header1.php"); ?>
    <title>Quản lý đơn hàng</title>
    <?php // include_once('header2.php'); ?>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../fonts/fontawesome/css/all.min.css">

    <style>
        /* --- CSS CHO KHUNG BAO NGOÀI GIỐNG TRANG CHỦ --- */
        body {
            background-color: #f0f0f0;
            padding: 0;
            margin: 0;
            font-family: sans-serif;
        }
        #main-container {
            max-width: 1200px; /* Giữ chiều rộng khung hồng lớn */
            margin: 30px auto;
            border: 1px solid rgb(236, 206, 227);
            background-color: rgb(255, 231, 236); /* Màu hồng nhạt */
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-radius: 8px;
            overflow: hidden;
            color: #2c1e18;
        }
        /* --- KẾT THÚC CSS KHUNG BAO NGOÀI --- */

        /* --- CSS CHO KHUNG NỘI DUNG TRẮNG --- */
        .content-box {
            background-color: #ffffff; /* Nền trắng */
            padding: 25px 30px; /* Padding bên trong */
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px; /* Khoảng cách với footer */
        }
        .page-title {
            color: #c85180; /* Màu tiêu đề hồng đậm */
            margin-bottom: 25px;
            font-weight: bold;
            text-align: center;
        }
        /* --- KẾT THÚC CSS KHUNG NỘI DUNG --- */

        /* --- CSS CHO BẢNG ĐƠN HÀNG --- */
        .table { margin-bottom: 0; font-size: 0.95em; }
        .table thead th { background-color: #f8f9fa; font-weight: 600; text-align: center; vertical-align: middle; border-color: #dee2e6; white-space: nowrap; }
        .table tbody td { text-align: center; vertical-align: middle; border-color: #dee2e6; }
        .table-hover tbody tr:hover { background-color: #f5f5f5; }
        .table td:nth-child(3) { text-align: right !important; font-weight: bold; white-space: nowrap; }
        .table td:nth-child(1) { font-weight: bold; }
        .action-link { margin: 2px 4px; padding: 4px 8px; font-size: 0.85em; text-decoration: none !important; }
        .btn-xs { padding: .2rem .4rem; font-size: .75rem; border-radius: .2rem; }
        .status-0 { color: #ffc107 !important; font-weight: bold;}
        .status-1 { color: #0dcaf0 !important; font-weight: bold;}
        .status-2 { color: #198754 !important; font-weight: bold;}
        .status-3 { color: #fd7e14 !important; font-weight: bold;}
        .status-4 { color: #dc3545 !important; font-weight: bold; }
        .order-note { margin-top: 20px; text-align: center; font-size: 0.9em; color: #6c757d; }
        .order-note strong { color: #dc3545; }

    </style>
</head>
<body>

<?php // <<< KHUNG BAO NGOÀI >>> ?>
<div id="main-container">

    <?php include_once('header2.php'); // Menu chính ?>

    <?php // <<< THAY ĐỔI container thành container-fluid >>> ?>
    <div class="container-fluid py-4"> <?php // Dùng container-fluid để chiếm hết chiều rộng ?>

        <div class="content-box bg-white p-4 rounded border shadow-sm"> <?php // Khung trắng chứa nội dung ?>

            <h3 class="page-title">ĐƠN HÀNG CỦA TÔI</h3>

            <?php // Hiển thị thông báo session ?>
            <?php if (isset($_SESSION['success_message'])) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])) : ?>
                 <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php // Bảng hiển thị đơn hàng ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped align-middle" style="/* min-width: 800px; Bỏ min-width nếu dùng container-fluid */">
                    <thead class="table-light">
                        <tr>
                            <th>MÃ ĐH</th>
                            <th>THỜI GIAN ĐẶT</th>
                            <th>TỔNG TIỀN (VNĐ)</th>
                            <th>TÌNH TRẠNG</th>
                            <th>THAO TÁC</th>
                            <th>IN HÓA ĐƠN</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Kiểm tra và lặp qua đơn hàng
                    if ($query_u && $has_orders) {
                        mysqli_data_seek($query_u, 0);
                        while ($r = mysqli_fetch_assoc($query_u)) {
                    ?>
                        <tr>
                            <td><?php echo $r['idDH']; ?></td>
                            <td><?php echo date("d/m/Y H:i", strtotime($r['ThoiDiemDatHang'])); ?></td>
                            <td>
                                <?php
                                if (isset($r['TongTien'])) {
                                    echo number_format($r['TongTien']);
                                } else {
                                    // N+1 Query (Không hiệu quả)
                                    $tongTienDH = 0;
                                    $sl_sp_ctdh="select sum(SoLuong*Gia) as TongTienCT from donhangchitiet where idDH=".$r['idDH'];
                                    $rs_tt=mysqli_query($conn,$sl_sp_ctdh);
                                    if($rs_tt && mysqli_num_rows($rs_tt) > 0){
                                         $d_tt=mysqli_fetch_assoc($rs_tt);
                                         $tongTienDH = $d_tt['TongTienCT'] ?? 0;
                                         mysqli_free_result($rs_tt);
                                    }
                                    echo number_format($tongTienDH);
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = 'status-' . $r['DaXuLy'];
                                echo '<strong class="' . $statusClass . '">';
                                switch ($r['DaXuLy']) {
                                    case 0: echo 'Chờ xác nhận'; break;
                                    case 1: echo 'Đang giao hàng'; break;
                                    case 2: echo 'Đã nhận hàng'; break;
                                    case 3: echo 'Yêu cầu hủy'; break;
                                    case 4: echo 'Đã hủy'; break;
                                    default: echo 'Không xác định'; break;
                                }
                                echo '</strong>';
                                if ($r['DaXuLy'] == 1) {
                                     echo '<br><a href="xuly_nhanhang.php?action=confirm&idDH='.$r['idDH'].'" class="btn btn-xs btn-success action-link mt-1" onclick="return confirm(\'Xác nhận bạn đã nhận được đơn hàng này?\');">Đã nhận hàng</a>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($r['DaXuLy'] == 0) {
                                    echo '<a href="yeucau_huydon.php?idDH='.$r['idDH'].'" class="btn btn-xs btn-warning action-link" onclick="return confirm(\'Bạn chắc chắn muốn yêu cầu hủy đơn hàng này?\');">Yêu cầu hủy</a>';
                                } elseif ($r['DaXuLy'] == 3) {
                                    echo '<i>Đang chờ xử lý hủy</i>';
                                } elseif ($r['DaXuLy'] == 4) {
                                    echo '<i>Đơn đã hủy</i>';
                                } else {
                                    echo '---';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="in_hoa_don.php?idDH=<?php echo $r['idDH']; ?>" class="btn btn-xs btn-info action-link" target="_blank">
                                     <i class="fas fa-print"></i> In
                                </a>
                            </td>
                        </tr>
                    <?php
                        } // Kết thúc while
                        if(isset($query_u) && $query_u) mysqli_free_result($query_u);
                    } else {
                        echo '<tr><td colspan="6" class="text-center py-4">Bạn chưa có đơn hàng nào. <a href="index.php">Tiếp tục mua sắm</a>!</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div> <?php // Kết thúc .table-responsive ?>

            <?php // Ghi chú cuối bảng nếu có đơn hàng ?>
            <?php if ($has_orders): ?>
                <p class="order-note">
                    <strong>Lưu ý:</strong> Đơn hàng đã chuyển sang trạng thái "Đang giao hàng" hoặc "Đã nhận hàng" không thể yêu cầu hủy.
                </p>
            <?php endif; ?>

        </div> <?php // Kết thúc .content-box ?>

    </div> <?php // <<< Đóng thẻ container-fluid >>> ?>

    <?php include_once('footer.php'); // Footer ?>

</div> <?php // <<< Đóng thẻ #main-container >>> ?>

<?php // Scripts ?>
<script src="../js/jquery-3.1.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<?php // Có thể thêm JS khác nếu cần ?>
</body>
</html>
<?php
ob_end_flush(); // Gửi bộ đệm đầu ra
?>