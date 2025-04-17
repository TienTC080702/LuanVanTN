<?php
include_once ('../connection/connect_database.php');

// --- Tối ưu hóa truy vấn: JOIN để lấy TenL và TenNH ngay từ đầu ---
// Câu truy vấn này đã bao gồm sp.* nên sẽ lấy được cột SoLanMua nếu nó tồn tại trong bảng sanpham
$sl_sanpham = "SELECT sp.*, l.TenL, nh.TenNH
               FROM sanpham sp
               LEFT JOIN loaisp l ON sp.idL = l.idL
               LEFT JOIN nhanhieu nh ON sp.idNH = nh.idNH
               ORDER BY sp.idSP DESC";
$rs_sanpham = mysqli_query($conn, $sl_sanpham);

// Kiểm tra lỗi truy vấn chính
if (!$rs_sanpham) {
    // Ghi log lỗi thay vì echo script
    error_log("Lỗi truy vấn danh sách sản phẩm: " . mysqli_error($conn));
    // Có thể hiển thị thông báo lỗi thân thiện hơn
    die("Không thể tải danh sách sản phẩm. Vui lòng thử lại sau.");
    /*
    echo "<script language='javascript'>alert('Không thể kết nối hoặc truy vấn dữ liệu sản phẩm!');";
    echo "location.href='index.php';</script>";
    */
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); // Nhúng CSS, Bootstrap ?>
    <title>Danh sách sản phẩm</title>
    <?php include_once('header2.php'); // Nhúng CSS/JS khác ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* CSS cho trang */
        h3.page-title { /* Sử dụng class thay vì tag h3 chung */
            color: #0d6efd; /* Màu xanh dương của Bootstrap */
            text-align: center;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem; /* Thêm khoảng cách dưới */
            font-weight: bold;
        }
        .table th {
            background-color: #f8f9fa; /* Màu nền nhẹ cho header */
            white-space: nowrap; /* Không xuống dòng tiêu đề cột */
            vertical-align: middle;
            text-align: center; /* Căn giữa tiêu đề cột */
        }
        .table td {
            vertical-align: middle; /* Căn giữa nội dung theo chiều dọc */
        }
        /* Định dạng tiền tệ căn phải */
        .price-column {
            text-align: right;
            white-space: nowrap; /* Không ngắt dòng giá tiền */
        }
        .action-buttons a {
            margin: 0 3px; /* Khoảng cách nhỏ giữa các nút action */
        }
         /* Điều chỉnh kích thước nút thêm */
        .add-buttons .btn {
            font-weight: bold;
        }
        /* Căn giữa cho các cột số */
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
<?php include_once('header3.php'); // Thanh điều hướng ?>

<div class="container-fluid mt-4">
    <h3 class="page-title">DANH SÁCH SẢN PHẨM</h3>

    <div class="d-flex gap-2 mb-3 add-buttons">
        <a href="them_sp.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Thêm Sản Phẩm
        </a>
        <a href="them_sp_hinh.php" class="btn btn-secondary">
            <i class="fas fa-images me-1"></i> Thêm Hình Ảnh SP
        </a>
    </div>

    <div class="table-responsive shadow-sm bg-white rounded p-3">
        <table class="table table-bordered table-hover table-striped align-middle">
            <thead class="text-center"> <tr>
                    <th>STT</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Loại</th>
                    <th>Nhãn Hiệu</th>
                    <th>Giá Bán</th>
                    <th>Giá KM</th>
                    <th>Tồn Kho</th>
                    <th>URL Hình</th>
                    <th>Lượt Xem</th>
                    <th>Số Lần Mua</th> <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php $stt = 0; ?>
                <?php if (mysqli_num_rows($rs_sanpham) > 0): ?>
                    <?php while ($r = $rs_sanpham->fetch_assoc()) { ?>
                        <tr>
                            <td class="text-center"><?php echo ++$stt; ?></td>
                            <td>
                                <a href="sp_chitiet.php?idSP=<?php echo $r['idSP']; ?>">
                                    <?php echo htmlspecialchars($r['TenSP']); // Dùng htmlspecialchars ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($r['TenL'] ?? 'N/A'); // Lấy từ JOIN, xử lý nếu NULL ?></td>
                            <td><?php echo htmlspecialchars($r['TenNH'] ?? 'N/A'); // Lấy từ JOIN, xử lý nếu NULL ?></td>
                            <td class="price-column"><?php echo number_format($r['GiaBan'] ?? 0, 0, ',', '.'); ?> đ</td>
                            <td class="price-column"><?php echo number_format($r['GiaKhuyenmai'] ?? 0, 0, ',', '.'); ?> đ</td>
                            <td class="text-center"><?php echo number_format($r['SoLuongTonKho'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars($r['urlHinh']); ?></td>
                            <td class="text-center"><?php echo number_format($r['SoLanXem'] ?? 0); ?></td>
                            <td class="text-center"><?php echo number_format($r['SoLanMua'] ?? 0); // Hiển thị số lần mua, giả định tên cột là SoLanMua ?></td>
                            <td class="text-center">
                                <?php if ($r['AnHien'] == 1): ?>
                                    <span class="badge bg-success">Hiện</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Ẩn</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center action-buttons">
                                <a href="sua_xoa_sp.php?idSP=<?php echo $r["idSP"]; ?>" class="btn btn-sm btn-warning" title="Sửa sản phẩm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="xoa_sp.php?idSP=<?php echo $r["idSP"]; ?>" class="btn btn-sm btn-danger" title="Xóa sản phẩm" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm \'<?php echo htmlspecialchars(addslashes($r['TenSP'])); ?>\'?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" class="text-center fst-italic">Không tìm thấy sản phẩm nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
    // Giải phóng bộ nhớ sau khi dùng xong kết quả chính
    if ($rs_sanpham) {
        mysqli_free_result($rs_sanpham);
    }
    // Đóng kết nối nếu cần
    if ($conn) {
        mysqli_close($conn);
    }
    include_once('footer.php');
?>
</body>
</html>