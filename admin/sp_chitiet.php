<?php
include_once('../connection/connect_database.php');

// --- Lấy dữ liệu sản phẩm ---
if (!isset($_GET['idSP']) || !is_numeric($_GET['idSP'])) {
    echo "<script language='javascript'>alert('ID sản phẩm không hợp lệ!'); location.href='index_ds_sp.php';</script>";
    exit;
}
$idSP_view = (int)$_GET['idSP'];

// Sử dụng Prepared Statement để lấy dữ liệu sản phẩm chính
// Kết hợp JOIN để lấy tên Nhãn hiệu và Loại trong cùng 1 query cho hiệu quả
$sql_sanpham = "SELECT sp.*, nh.TenNH, l.TenL
                FROM sanpham sp
                LEFT JOIN nhanhieu nh ON sp.idNH = nh.idNH
                LEFT JOIN loaisp l ON sp.idL = l.idL
                WHERE sp.idSP = ?";
$stmt_sanpham = mysqli_prepare($conn, $sql_sanpham);

if (!$stmt_sanpham) {
    die("Lỗi chuẩn bị câu lệnh lấy sản phẩm: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_sanpham, "i", $idSP_view);
mysqli_stmt_execute($stmt_sanpham);
$rs_sanpham = mysqli_stmt_get_result($stmt_sanpham);

if (mysqli_num_rows($rs_sanpham) == 0) {
    echo "<script language='javascript'>alert('Không tìm thấy sản phẩm với ID này!'); location.href='index_ds_sp.php';</script>";
    exit;
}

$row_sanpham = mysqli_fetch_assoc($rs_sanpham); // Dùng fetch_assoc
mysqli_stmt_close($stmt_sanpham);

// --- Lấy tên nhãn hiệu và loại (Không cần nữa nếu đã JOIN ở trên) ---
/*
// Lấy tên Nhãn hiệu
$q_nh = "SELECT TenNH FROM nhanhieu WHERE idNH = ?";
$stmt_nh = mysqli_prepare($conn, $q_nh);
mysqli_stmt_bind_param($stmt_nh, "i", $row_sanpham['idNH']);
mysqli_stmt_execute($stmt_nh);
$rs_nh = mysqli_stmt_get_result($stmt_nh);
$r_nh = mysqli_fetch_assoc($rs_nh);
mysqli_stmt_close($stmt_nh);

// Lấy tên Loại
$q_l = "SELECT TenL FROM loaisp WHERE idL = ?";
$stmt_l = mysqli_prepare($conn, $q_l);
mysqli_stmt_bind_param($stmt_l, "i", $row_sanpham['idL']);
mysqli_stmt_execute($stmt_l);
$rs_l = mysqli_stmt_get_result($stmt_l);
$r_l = mysqli_fetch_assoc($rs_l);
mysqli_stmt_close($stmt_l);
*/
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once ("header1.php");?>
    <title>Chi tiết sản phẩm: <?php echo htmlspecialchars($row_sanpham['TenSP']); ?></title>
    <?php include_once('header2.php');?>
    <style>
        /* CSS tùy chỉnh nhẹ nếu cần */
        .page-title {
            color: #0d6efd; /* Màu xanh dương mặc định của Bootstrap */
            margin-bottom: 30px;
        }
        .details-card {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
        }
        dt { /* Định nghĩa nhãn */
            font-weight: 600;
            color: #555;
        }
        dd { /* Định nghĩa giá trị */
            margin-bottom: 1rem; /* Khoảng cách giữa các dòng */
            color: #333;
        }
        .product-description {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
        }
         .product-image {
             max-width: 400px; /* Giới hạn chiều rộng tối đa của ảnh */
             height: auto;
             display: block; /* Để căn giữa dễ hơn nếu cần */
             margin-left: auto;
             margin-right: auto;
         }
    </style>
</head>
<body>
<?php include_once ('header3.php');?>

<div class="container mt-4 mb-5">
    <h3 class="text-center page-title">CHI TIẾT SẢN PHẨM</h3>

    <div class="details-card">
        <div class="row">
            <div class="col-md-5 text-center mb-3 mb-md-0">
                 <?php if (!empty($row_sanpham['urlHinh']) && file_exists("../images/" . $row_sanpham['urlHinh'])) { ?>
                    <img src="../images/<?php echo htmlspecialchars($row_sanpham['urlHinh']); ?>"
                         alt="Hình ảnh <?php echo htmlspecialchars($row_sanpham['TenSP']); ?>"
                         class="img-fluid img-thumbnail product-image">
                 <?php } else { ?>
                    <div class="text-muted p-5 border rounded text-center">Không có ảnh đại diện</div>
                 <?php } ?>
            </div>

            <div class="col-md-7">
                <dl class="row">
                    <dt class="col-sm-4">Mã sản phẩm</dt>
                    <dd class="col-sm-8"><?php echo $row_sanpham['idSP']; ?></dd>

                    <dt class="col-sm-4">Tên sản phẩm</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($row_sanpham['TenSP']); ?></dd>

                    <dt class="col-sm-4">Nhãn hiệu</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($row_sanpham['TenNH'] ?? 'N/A'); // Sử dụng tên từ JOIN ?></dd>

                    <dt class="col-sm-4">Loại</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($row_sanpham['TenL'] ?? 'N/A'); // Sử dụng tên từ JOIN ?></dd>

                    <dt class="col-sm-4">Giá bán</dt>
                    <dd class="col-sm-8"><?php echo number_format($row_sanpham['GiaBan'], 0, ',', '.'); ?> VNĐ</dd>

                    <dt class="col-sm-4">Giá khuyến mãi</dt>
                    <dd class="col-sm-8">
                        <?php
                            if ($row_sanpham['GiaKhuyenmai'] > 0 && $row_sanpham['GiaKhuyenmai'] < $row_sanpham['GiaBan']) {
                                echo number_format($row_sanpham['GiaKhuyenmai'], 0, ',', '.') . ' VNĐ';
                            } else {
                                echo "<span class='text-muted'>Không có</span>";
                            }
                        ?>
                    </dd>

                    <dt class="col-sm-4">Số lượng tồn kho</dt>
                    <dd class="col-sm-8"><?php echo number_format($row_sanpham['SoLuongTonKho']); ?></dd>

                    <dt class="col-sm-4">Số lần xem</dt>
                    <dd class="col-sm-8"><?php echo number_format($row_sanpham['SoLanXem']); ?></dd>

                    <dt class="col-sm-4">Số lần mua</dt>
                    <dd class="col-sm-8"><?php echo number_format($row_sanpham['SoLanMua']); ?></dd>

                    <dt class="col-sm-4">Ghi chú</dt>
                    <dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($row_sanpham['GhiChu'] ?? 'Không có')); // nl2br giữ xuống dòng ?></dd>

                    <dt class="col-sm-4">Ngày cập nhật</dt>
                    <dd class="col-sm-8"><?php echo date("d/m/Y H:i:s", strtotime($row_sanpham['NgayCapNhat'])); // Định dạng lại ngày ?></dd>

                    <dt class="col-sm-4">Trạng thái</dt>
                    <dd class="col-sm-8">
                        <?php
                            if ($row_sanpham['AnHien'] == 1) {
                                echo '<span class="badge bg-success">Hiện</span>';
                            } else {
                                echo '<span class="badge bg-secondary">Ẩn</span>';
                            }
                        ?>
                    </dd>

                     <dt class="col-sm-4">Bài viết liên quan</dt>
                     <dd class="col-sm-8">
                         <?php
                         // Thêm liên kết thực sự nếu bạn có trang bài viết
                         // Ví dụ: echo "<a href='view_post.php?idSP=".$row_sanpham['idSP']."'>Xem bài viết</a>";
                         echo "Xem"; // Giữ nguyên như cũ nếu chưa có link
                         ?>
                     </dd>
                </dl>
            </div>
        </div>

        <div class="row">
            <div class="col-12 product-description">
                <h5 class="mb-3">Mô tả chi tiết</h5>
                <div>
                    <?php
                    // Hiển thị mô tả, cho phép thẻ HTML cơ bản nếu cần (cân nhắc dùng thư viện lọc HTML)
                    echo $row_sanpham['MoTa'];
                    ?>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="index_ds_sp.php" class="btn btn-secondary">Trở về danh sách</a>
        </div>
    </div> </div> <?php include_once ('footer.php');?>
</body>
</html>