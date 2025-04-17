<?php
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng
$sl_user = "SELECT * FROM users WHERE idGroup=2 ORDER BY NgayDangKy DESC"; // Lọc user thường và sắp xếp
$rs_user = mysqli_query($conn, $sl_user);
if (!$rs_user) {
    echo "Không thể truy vấn CSDL"; // Nên xử lý lỗi tốt hơn
    // die("Lỗi truy vấn: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Danh Sách Người Dùng</title> <?php include_once('header2.php'); ?>
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
        .table-actions a, .table-actions button { margin: 0 3px; }
        .table th, .table td { vertical-align: middle; } /* Căn giữa dọc */
    </style>
</header>
<body>
<?php include_once('header3.php'); ?>

<div class="container-fluid mt-3"> <h3 class="page-title">DANH SÁCH NGƯỜI DÙNG</h3>

    <div class="add-button-container">
        <a href="them_user.php"> <button type="button" class="btn btn-success"> <i class="fas fa-user-plus"></i> THÊM NGƯỜI DÙNG
            </button>
        </a>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-bordered table-hover table-striped table-sm">
            <thead class="text-center table-primary"> <tr>
                    <th style="width: 5%;">STT</th>
                    <th style="width: 15%;">Tên Đăng Nhập</th>
                    <th style="width: 20%;">Họ Tên Khách Hàng</th>
                    <th style="width: 20%;">Địa Chỉ</th>
                    <th style="width: 10%;">Điện Thoại</th>
                    <th style="width: 15%;">Email</th>
                    <th style="width: 10%;">Ngày Đăng Ký</th>
                    <th style="width: 10%;">Thao Tác</th>
                </tr>
            </thead>
            <tbody> <?php
                $stt = 0;
                if ($rs_user && mysqli_num_rows($rs_user) > 0) {
                    while ($r = $rs_user->fetch_assoc()) {
                        $stt++;
                        $ngayDangKyFormatted = date('d/m/Y', strtotime($r['NgayDangKy'])); // Format ngày
                ?>
                        <tr>
                            <td class="text-center"><?php echo $stt; ?></td>
                            <td class="text-start"><?php echo htmlspecialchars($r['HoTen']); ?></td>
                            <td class="text-start"><?php echo htmlspecialchars($r['HoTenK']); ?></td>
                            <td class="text-start"><?php echo htmlspecialchars($r['DiaChi']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($r['DienThoai']); ?></td>
                            <td class="text-start"><?php echo htmlspecialchars($r['Email']); ?></td>
                            <td class="text-center"><?php echo $ngayDangKyFormatted; ?></td>
                            <td class="text-center table-actions">
                                <a href="sua_user.php?idUser=<?php echo $r['idUser']; ?>" class="btn btn-warning btn-sm" title="Sửa người dùng">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="xoa_user.php?idUser=<?php echo $r['idUser']; ?>" class="btn btn-danger btn-sm" title="Xóa người dùng" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                <?php
                    } // Kết thúc while
                } else {
                    // Thông báo nếu không có user
                    echo '<tr><td colspan="8" class="text-center">Không có người dùng nào trong danh sách.</td></tr>';
                }
                if($rs_user) mysqli_free_result($rs_user); // Giải phóng bộ nhớ
                ?>
            </tbody> </table>
    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>