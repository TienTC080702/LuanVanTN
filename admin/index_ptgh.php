<?php
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng
$sl_giaohang = "SELECT * FROM phuongthucgiaohang ORDER BY TenGH ASC"; // Sắp xếp theo tên
$rs_giaohang = mysqli_query($conn, $sl_giaohang);
if (!$rs_giaohang) {
    echo "Không thể truy vấn CSDL"; // Nên xử lý lỗi tốt hơn
    // die("Lỗi truy vấn: ".mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Quản Lý Phương Thức Giao Hàng</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Định dạng tiêu đề */
        h3.page-title {
            color: #1F75FE; /* Màu xanh dương */
            text-align: center;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }
        /* Nút thêm */
        .add-button-container {
            margin-bottom: 1rem;
        }
        /* Căn lề */
        .text-center { text-align: center !important; vertical-align: middle !important; }
        .text-start { text-align: left !important; vertical-align: middle !important; }
        .text-end { text-align: right !important; vertical-align: middle !important; }
        /* Div cuộn */
        .table-responsive-custom { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        /* Nút thao tác */
        .table-actions a { margin: 0 3px; }
        /* Badge */
        .badge { font-size: 0.9em; padding: 0.4em 0.6em;} /* Tăng padding cho badge */
        .table th, .table td { vertical-align: middle; padding: 0.6rem; } /* Tăng nhẹ padding ô */
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container-fluid mt-3">
    <h3 class="page-title">DANH SÁCH PHƯƠNG THỨC GIAO HÀNG</h3>

    <div class="add-button-container">
        <a href="them_ph_giaohang.php">
            <button type="button" class="btn btn-success">
                <i class="fas fa-plus"></i> THÊM PHƯƠNG THỨC
            </button>
        </a>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-bordered table-hover table-striped table-sm">
            <thead class="text-center table-primary">
                <tr>
                    <th style="width: 5%;" class="text-center">STT</th>
                    <th style="width: 45%;" class="text-start">Tên Phương Thức Giao Hàng</th> <th style="width: 15%;" class="text-end">Phí (VNĐ)</th>   <th style="width: 15%;" class="text-center">Trạng Thái</th> <th style="width: 20%;" class="text-center">Thao Tác</th>  </tr>
            </thead>
            <tbody>
                <?php
                $stt = 0;
                if ($rs_giaohang && mysqli_num_rows($rs_giaohang) > 0) {
                    while ($r = $rs_giaohang->fetch_assoc()) {
                        $stt++;
                        $phiFormatted = number_format($r['Phi'] ?? 0, 0, ',', '.');
                        $trangThaiBadge = ($r['AnHien'] == 1)
                            ? '<span class="badge bg-success">Hiện</span>'
                            : '<span class="badge bg-secondary">Ẩn</span>';
                ?>
                        <tr>
                            <td class="text-center"><?php echo $stt; ?></td>
                            <td class="text-start"><?php echo htmlspecialchars($r['TenGH']); ?></td>
                            <td class="text-end"><?php echo $phiFormatted; ?></td>
                            <td class="text-center"><?php echo $trangThaiBadge; ?></td>
                            <td class="text-center table-actions">
                                <a href="sua_xoa_gh.php?action=edit&idGH=<?php echo $r['idGH']; ?>" class="btn btn-warning btn-sm" title="Sửa phương thức">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="sua_xoa_gh.php?action=delete&idGH=<?php echo $r['idGH']; ?>" class="btn btn-danger btn-sm" title="Xóa phương thức" onclick="return confirm('Bạn có chắc chắn muốn xóa phương thức giao hàng này?');">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                <?php
                    } // Kết thúc while
                } else {
                    echo '<tr><td colspan="5" class="text-center">Chưa có phương thức giao hàng nào.</td></tr>';
                }
                if($rs_giaohang) mysqli_free_result($rs_giaohang);
                ?>
            </tbody>
        </table>
    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>