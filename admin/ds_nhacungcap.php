<?php
// 1. Khởi tạo Session (quan trọng để nhận flash message)
if (!isset($_SESSION)) {
    session_start();
}

// Giả sử đã có kiểm tra đăng nhập admin và kết nối DB ở header
// include_once('session_check_admin.php'); // Ví dụ kiểm tra session admin
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

// --- THÊM ĐOẠN NÀY ĐỂ HIỂN THỊ THÔNG BÁO ---
$success_message_display = ''; // Chuẩn bị biến để chứa HTML thông báo
if (isset($_SESSION['success_message'])) {
    $success_message_display = '<div class="container-fluid mt-3">'; // Thêm container
    $success_message_display .= '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    $success_message_display .= htmlspecialchars($_SESSION['success_message']); // Hiển thị thông báo
    $success_message_display .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    $success_message_display .= '</div>';
    $success_message_display .= '</div>';
    unset($_SESSION['success_message']); // Xóa thông báo khỏi session sau khi đã chuẩn bị hiển thị
}
// --- KẾT THÚC ĐOẠN THÊM ---


$query = "SELECT * FROM nhacungcap ORDER BY TenNCC ASC"; // Lấy danh sách NCC
$result = mysqli_query($conn, $query);

if (!$result) {
    // Thay vì die(), có thể hiển thị lỗi thân thiện hơn
    $db_error = "Lỗi truy vấn CSDL: " . mysqli_error($conn);
    // die("Lỗi truy vấn CSDL: " . mysqli_error($conn));
} else {
    $db_error = null; // Không có lỗi DB
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Nhà Cung Cấp</title>
    <?php include_once("header1.php"); ?>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS cơ bản cho trang */
        h3.page-title { color: #007bff; text-align: center; margin: 1.5rem 0; font-weight: bold; }
        .add-button-container { margin-bottom: 1rem; }
        .table-actions a { margin: 0 3px; /* Giảm khoảng cách nút */ }
        .table th, .table td { vertical-align: middle; } /* Căn giữa chiều dọc */
        .table-responsive-custom { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .text-center { text-align: center !important; }
        .text-end { text-align: right !important; }
        .alert { margin-bottom: 1rem; } /* Đảm bảo khoảng cách dưới cho alert */
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<?php echo $success_message_display; ?>

<?php if ($db_error): ?>
    <div class="container-fluid mt-3">
         <div class="alert alert-danger">
             <?php echo htmlspecialchars($db_error); ?>
         </div>
    </div>
<?php endif; ?>


<div class="container-fluid mt-3">
    <h3 class="page-title">QUẢN LÝ NHÀ CUNG CẤP</h3>

    <div class="add-button-container">
        <a href="them_nhacungcap.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Thêm Nhà Cung Cấp Mới
        </a>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-bordered table-hover table-striped table-sm">
            <thead class="table-primary text-center">
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 25%;">Tên Nhà Cung Cấp</th>
                    <th style="width: 30%;">Địa Chỉ</th>
                    <th style="width: 15%;">Điện Thoại</th>
                    <th style="width: 15%;">Email</th>
                    <th style="width: 10%;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="text-center"><?php echo $row['idNCC']; ?></td>
                            <td><?php echo htmlspecialchars($row['TenNCC']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['DiaChiNCC'])); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['DienThoaiNCC']); ?></td>
                            <td><?php echo htmlspecialchars($row['EmailNCC']); ?></td>
                            <td class="text-center table-actions">
                                <a href="sua_nhacungcap.php?id=<?php echo $row['idNCC']; ?>" class="btn btn-warning btn-sm" title="Sửa nhà cung cấp">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="xuly_nhacungcap.php?action=delete&id=<?php echo $row['idNCC']; ?>" class="btn btn-danger btn-sm" title="Xóa nhà cung cấp" onclick="return confirm('Bạn có chắc chắn muốn xóa nhà cung cấp này? Việc này có thể ảnh hưởng đến các phiếu nhập liên quan.');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php elseif (!$db_error): // Chỉ hiển thị 'Chưa có' nếu không có lỗi DB ?>
                    <tr><td colspan="6" class="text-center">Chưa có nhà cung cấp nào trong danh sách.</td></tr>
                    <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include_once('footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Giải phóng bộ nhớ
if($result) mysqli_free_result($result);
// Đóng kết nối nếu cần
// if(isset($conn)) mysqli_close($conn);
?>