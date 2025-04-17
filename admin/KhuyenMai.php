<?php
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng
$sl_km = "SELECT * FROM khuyenmai ORDER BY idKM DESC"; // Sắp xếp mới nhất lên đầu
$rs_km = mysqli_query($conn, $sl_km);
if (!$rs_km) {
    echo "Không thể truy vấn CSDL";
    // die("Lỗi truy vấn: ".mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Quản Lý Khuyến Mãi</title>
    <?php include_once('header2.php'); ?>
    <link href="../css/hieuung.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        h3.page-title { color: #1F75FE; text-align: center; margin: 1.5rem 0; font-weight: bold; }
        .add-button-container { margin-bottom: 1rem; }
        .text-center { text-align: center !important; vertical-align: middle !important; }
        .text-start { text-align: left !important; vertical-align: middle !important; }
        .text-end { text-align: right !important; vertical-align: middle !important; }
        .table-responsive-custom { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-actions a { margin: 0 3px; }
        .badge { font-size: 0.9em; padding: 0.4em 0.6em;}
        .table th, .table td { vertical-align: middle; padding: 0.6rem; word-break: break-word; /* Giúp xuống dòng nếu URL quá dài */}
        /* Bỏ class .img-thumbnail-custom */
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container-fluid mt-3">
    <h3 class="page-title">DANH SÁCH KHUYẾN MÃI</h3>

    <div class="add-button-container">
        <a href="them_km.php">
            <button type="button" class="btn btn-success">
                <i class="fas fa-plus"></i> THÊM KHUYẾN MÃI
            </button>
        </a>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-bordered table-hover table-striped table-sm">
            <thead class="text-center table-primary">
                <tr>
                    <th style="width: 5%;" class="text-center">STT</th>
                    <th style="width: 40%;" class="text-start">Mô Tả Khuyến Mãi</th>
                    <th style="width: 20%;" class="text-start">URL Hình Ảnh</th>
                    <th style="width: 15%;" class="text-center">Trạng Thái</th>
                    <th style="width: 20%;" class="text-center">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stt = 0;
                if ($rs_km && mysqli_num_rows($rs_km) > 0) {
                    while ($r = $rs_km->fetch_assoc()) {
                        $stt++;
                        $phiFormatted = number_format($r['Phi'] ?? 0, 0, ',', '.'); // Biến này có vẻ từ code cũ, không dùng ở đây
                        $trangThaiBadge = ($r['AnHien'] == 1)
                            ? '<span class="badge bg-success">Hiện</span>'
                            : '<span class="badge bg-secondary">Ẩn</span>';

                        // *** BỎ PHẦN TẠO THẺ IMG, CHỈ LẤY URL HÌNH ***
                        $urlHinh = htmlspecialchars($r['urlHinh'] ?? ''); // Lấy URL và dùng htmlspecialchars

                ?>
                        <tr>
                            <td class="text-center"><?php echo $stt; ?></td>
                            <td class="text-start"><?php echo nl2br(htmlspecialchars($r['MotaKM'])); ?></td>
                            <td class="text-start"><?php echo $urlHinh; ?></td>
                            <td class="text-center"><?php echo $trangThaiBadge; ?></td>
                            <td class="text-center table-actions">
                                <a href="sua_km.php?idKM=<?php echo $r['idKM']; ?>" class="btn btn-warning btn-sm" title="Sửa khuyến mãi">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="xoa_km.php?idKM=<?php echo $r['idKM']; ?>" class="btn btn-danger btn-sm" title="Xóa khuyến mãi" onclick="return confirm('Bạn có chắc chắn muốn xóa khuyến mãi này?');">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                <?php
                    } // Kết thúc while
                } else {
                    echo '<tr><td colspan="5" class="text-center">Chưa có chương trình khuyến mãi nào.</td></tr>';
                }
                if($rs_km) mysqli_free_result($rs_km);
                ?>
            </tbody>
        </table>
    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>