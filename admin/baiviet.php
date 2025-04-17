<?php
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Danh Sách Bài Viết</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Định dạng tiêu đề trang */
        h3.page-title {
            color: #3498db; /* Màu xanh dương */
            text-align: center;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }
        /* Khoảng cách nút thêm */
        .add-button-container {
            margin-bottom: 1rem;
        }
        /* Căn lề */
        .text-center { text-align: center !important; vertical-align: middle !important; }
        .text-end { text-align: right !important; vertical-align: middle !important; }
        .text-start { text-align: left !important; vertical-align: middle !important; } /* Căn trái */
        /* Div cuộn */
        .table-responsive-custom { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        /* Icon trong nút */
        .btn .fas { margin-right: 5px; }
        /* Style cho badge */
        .badge { font-size: 0.9em; }
        /* Action buttons */
        .table-actions a { margin: 0 3px; }
        .table th, .table td { vertical-align: middle; } /* Căn giữa dọc */

    </style>
</head> <body>
<?php include_once('header3.php'); ?>

<div class="container-fluid mt-3"> <h3 class="page-title">DANH SÁCH CÁC BÀI VIẾT</h3>

    <div class="add-button-container">
        <a href="them_baiviet.php">
            <button type="button" class="btn btn-info">
                <i class="fas fa-plus"></i> THÊM BÀI VIẾT
            </button>
        </a>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-bordered table-hover table-striped table-sm">
            <thead class="text-center table-primary"> <tr>
                    <th style="width: 5%;">STT</th>
                    <th style="width: 5%;">ID BV</th>
                    <th style="width: 20%;">Tên Sản Phẩm</th>
                    <th style="width: 30%;">Tiêu Đề</th>
                    <th style="width: 15%;">Ngày Cập Nhật</th>
                    <th style="width: 10%;">Trạng Thái</th>
                    <th style="width: 15%;">Thao Tác</th>
                </tr>
            </thead>
            <tbody> <?php
                $stt = 0;
                $sl_baiviet = "SELECT * FROM baiviet ORDER BY NgayCapNhat DESC"; // Sắp xếp theo ngày mới nhất
                $rs_baiviet = mysqli_query($conn, $sl_baiviet);

                if (!$rs_baiviet) {
                    // Hiển thị lỗi ngay trên trang thay vì alert
                    echo "<tr><td colspan='7' class='text-center text-danger'>Lỗi truy vấn cơ sở dữ liệu!</td></tr>";
                } elseif (mysqli_num_rows($rs_baiviet) == 0) {
                     echo "<tr><td colspan='7' class='text-center'>Chưa có bài viết nào.</td></tr>";
                } else {
                    while ($r = $rs_baiviet->fetch_assoc()) {
                        $stt++;
                        // --- Lấy tên sản phẩm (N+1 Query - giữ nguyên logic) ---
                        $tenSP = "N/A";
                        if (isset($r['idSP'])) {
                             $q_sp = "SELECT TenSP FROM sanpham WHERE idSP=" . intval($r['idSP']);
                             $rs_sp = mysqli_query($conn, $q_sp);
                             if ($rs_sp && $row_sp = mysqli_fetch_assoc($rs_sp)) { // Dùng fetch_assoc
                                 $tenSP = htmlspecialchars($row_sp['TenSP']);
                             }
                             if($rs_sp) mysqli_free_result($rs_sp);
                        }
                        // --- Kết thúc lấy tên SP ---

                        // Format ngày - Chỉ lấy ngày, tháng, năm
                        $ngayCapNhatFormatted = date('d/m/Y', strtotime($r['NgayCapNhat'])); // <<<=== ĐÃ SỬA Ở ĐÂY

                        // Định dạng trạng thái
                        $trangThaiBadge = ($r['AnHien'] == 1)
                            ? '<span class="badge bg-success">Hiện</span>'
                            : '<span class="badge bg-secondary">Ẩn</span>';
                ?>
                        <tr>
                            <td class="text-center"><?php echo $stt; ?></td>
                            <td class="text-center"><?php echo $r['idBV']; ?></td>
                            <td class="text-start"><?php echo $tenSP; ?></td>
                            <td class="text-start">
                                <a href="chitiet_baiviet.php?idBV=<?php echo $r["idBV"]; ?>" title="Xem chi tiết">
                                    <?php echo htmlspecialchars($r['TieuDe']); ?>
                                </a>
                            </td>
                            <td class="text-center"><?php echo $ngayCapNhatFormatted; ?></td>
                            <td class="text-center"><?php echo $trangThaiBadge; ?></td>
                            <td class="text-center table-actions">
                                <a href="sua_xoa_baiviet.php?action=edit&idBV=<?php echo $r["idBV"]; ?>" class="btn btn-warning btn-sm" title="Sửa bài viết">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="sua_xoa_baiviet.php?action=delete&idBV=<?php echo $r["idBV"]; ?>" class="btn btn-danger btn-sm" title="Xóa bài viết" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                <?php
                    } // End while
                } // End else
                if($rs_baiviet) mysqli_free_result($rs_baiviet); // Giải phóng bộ nhớ
                ?>
            </tbody> </table>
    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>