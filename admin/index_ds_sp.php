<?php
include_once ('../connection/connect_database.php');

// --- Xử lý tìm kiếm ---
$search_keyword = ''; // Biến lưu từ khóa tìm kiếm
$where_clause = '';   // Biến lưu mệnh đề WHERE cho SQL

// Kiểm tra xem có từ khóa tìm kiếm được gửi lên không
if (isset($_GET['search_keyword']) && !empty(trim($_GET['search_keyword']))) {
    // Lấy và làm sạch từ khóa (QUAN TRỌNG: Chống SQL Injection)
    $search_keyword = mysqli_real_escape_string($conn, trim($_GET['search_keyword']));

    // Tạo mệnh đề WHERE để tìm kiếm trên các cột mong muốn
    // Tìm kiếm trong Tên sản phẩm, Tên loại, Tên nhãn hiệu
    $where_clause = " WHERE (sp.TenSP LIKE '%{$search_keyword}%'
                       OR l.TenL LIKE '%{$search_keyword}%'
                       OR nh.TenNH LIKE '%{$search_keyword}%') ";
    // Bạn có thể thêm các cột khác để tìm kiếm nếu muốn, ví dụ:
    // OR sp.idSP = '{$search_keyword}' // Nếu muốn tìm cả theo ID
}

// --- Câu truy vấn SQL đã được cập nhật để bao gồm mệnh đề WHERE (nếu có) ---
$sl_sanpham = "SELECT sp.*, l.TenL, nh.TenNH
               FROM sanpham sp
               LEFT JOIN loaisp l ON sp.idL = l.idL
               LEFT JOIN nhanhieu nh ON sp.idNH = nh.idNH"
              . $where_clause . // Nối mệnh đề WHERE vào đây
              " ORDER BY sp.idSP DESC"; // Giữ lại ORDER BY

$rs_sanpham = mysqli_query($conn, $sl_sanpham);

// Kiểm tra lỗi truy vấn chính
if (!$rs_sanpham) {
    error_log("Lỗi truy vấn danh sách sản phẩm: " . mysqli_error($conn));
    die("Không thể tải danh sách sản phẩm. Vui lòng thử lại sau.");
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Danh sách sản phẩm</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* CSS cho trang (giữ nguyên như cũ) */
        h3.page-title {
            color: #0d6efd;
            text-align: center;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }
        .table th {
            background-color: #f8f9fa;
            white-space: nowrap;
            vertical-align: middle;
            text-align: center;
        }
        .table td {
            vertical-align: middle;
        }
        .price-column {
            text-align: right;
            white-space: nowrap;
        }
        .action-buttons a {
            margin: 0 3px;
        }
        .add-buttons .btn {
           font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
<?php include_once('header3.php'); // Thanh điều hướng ?>

<div class="container-fluid mt-4">
    <h3 class="page-title">DANH SÁCH SẢN PHẨM</h3>

    <div class="mb-3">
        <form action="" method="GET" class="d-flex flex-wrap gap-2"> 
            <div class="flex-grow-1"> 
                <input type="text" name="search_keyword" class="form-control"
                       placeholder="Nhập tên sản phẩm, loại, nhãn hiệu để tìm..."
                       value="<?php echo htmlspecialchars($search_keyword); // Hiển thị lại từ khóa đã tìm ?>">
            </div>
            <button type="submit" class="btn btn-info text-white" style="white-space: nowrap;">
                <i class="fas fa-search me-1"></i> Tìm Kiếm
            </button>
            <?php if (!empty($search_keyword)): // Chỉ hiển thị nút 'Tất cả' nếu đang có tìm kiếm ?>
                 <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); // Lấy URL gốc không có tham số ?>"
                    class="btn btn-secondary" style="white-space: nowrap;">
                     <i class="fas fa-list me-1"></i> Xem Tất Cả
                 </a>
             <?php endif; ?>
        </form>
    </div>
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
                    <th>Số Lần Mua</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php $stt = 0; ?>
                <?php if ($rs_sanpham && mysqli_num_rows($rs_sanpham) > 0): // Thêm kiểm tra $rs_sanpham tồn tại ?>
                    <?php while ($r = $rs_sanpham->fetch_assoc()) { ?>
                        <tr>
                            <td class="text-center"><?php echo ++$stt; ?></td>
                            <td>
                                <a href="sp_chitiet.php?idSP=<?php echo $r['idSP']; ?>">
                                    <?php echo htmlspecialchars($r['TenSP']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($r['TenL'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($r['TenNH'] ?? 'N/A'); ?></td>
                            <td class="price-column"><?php echo number_format($r['GiaBan'] ?? 0, 0, ',', '.'); ?> đ</td>
                            <td class="price-column"><?php echo number_format($r['GiaKhuyenmai'] ?? 0, 0, ',', '.'); ?> đ</td>
                            <td class="text-center"><?php echo number_format($r['SoLuongTonKho'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars($r['urlHinh']); ?></td>
                            <td class="text-center"><?php echo number_format($r['SoLanXem'] ?? 0); ?></td>
                            <td class="text-center"><?php echo number_format($r['SoLanMua'] ?? 0); ?></td>
                            <td class="text-center">
                                <?php if (isset($r['AnHien']) && $r['AnHien'] == 1): // Thêm kiểm tra isset ?>
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
                        <td colspan="12" class="text-center fst-italic">
                            <?php
                                // Hiển thị thông báo phù hợp: Không có sản phẩm nào hoặc không tìm thấy kết quả
                                if (!empty($search_keyword)) {
                                    echo "Không tìm thấy sản phẩm nào phù hợp với từ khóa: '" . htmlspecialchars($search_keyword) . "'";
                                } else {
                                    echo "Không có sản phẩm nào trong danh sách.";
                                }
                            ?>
                        </td>
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