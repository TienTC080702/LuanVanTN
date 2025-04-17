<?php
include_once ('../connection/connect_database.php');
$sl_loaisp = "select * from loaisp ORDER BY idL ASC"; // Thêm ORDER BY để sắp xếp
$rs_loaisp = mysqli_query($conn,$sl_loaisp);
if(!$rs_loaisp) {
    die("Lỗi: Không thể truy vấn cơ sở dữ liệu loại sản phẩm."); // Hiển thị lỗi rõ ràng hơn
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once ("header1.php");?>
    <title>Danh sách loại sản phẩm</title>
    <?php include_once('header2.php');?>
    <style>
        /* CSS tùy chỉnh nếu cần thêm */
        .action-col { /* Cột thao tác */
            white-space: nowrap; /* Ngăn không cho nút xuống dòng */
            width: 1%; /* Ưu tiên thu nhỏ cột này */
            text-align: center;
        }
        .status-col { /* Cột ẩn hiện */
             width: 100px; /* Độ rộng cố định cho cột trạng thái */
             text-align: center;
        }
         .stt-col { /* Cột STT */
             width: 60px;
             text-align: center;
         }
         .table-sm td, .table-sm th {
            vertical-align: middle;
         }
    </style>
</head>
<body>
<?php include_once ('header3.php');?>

<div class="container mt-4">
    <h3 class="text-center mb-4" style="color: #1F75FE;">DANH SÁCH LOẠI SẢN PHẨM</h3>

    <div class="d-flex justify-content-end mb-3"> <?php // Đặt nút Thêm ở bên phải ?>
        <a href="them_lsp.php" class="btn btn-primary"> <?php // Sử dụng btn-primary hoặc btn-success ?>
           <i class="fas fa-plus me-1"></i> Thêm loại sản phẩm <?php // Thêm icon nếu dùng FontAwesome ?>
        </a>
    </div>

    <div class="table-responsive shadow-sm rounded"> <?php // Thêm shadow và bo góc nhẹ ?>
        <table class="table table-bordered table-hover table-striped table-sm mb-0"> <?php // Bỏ margin bottom mặc định ?>
            <thead class="table-light text-center"> <?php // Dùng thead-light ?>
                <tr>
                    <th class="stt-col">STT</th>
                    <th>Tên loại</th>
                    <th class="status-col">Trạng thái</th>
                    <th class="action-col">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($rs_loaisp) > 0) {
                    $stt = 1; // Bắt đầu STT từ 1
                    while ($r = $rs_loaisp->fetch_assoc()) {
                ?>
                        <tr>
                            <td class="text-center"><?php echo $stt++; ?></td>
                            <td><?php echo htmlspecialchars($r['TenL']); ?></td>
                            <td class="status-col">
                                <?php
                                    if ($r['AnHien'] == 1) {
                                        echo '<span class="badge bg-success">Hiện</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">Ẩn</span>';
                                    }
                                ?>
                            </td>
                            <td class="action-col">
                                <a href="sua_lsp.php?idL=<?php echo $r['idL']; ?>" class="btn btn-warning btn-sm" title="Sửa/Xóa <?php echo htmlspecialchars($r['TenL']); ?>">
                                   <i class="fas fa-edit"></i> Sửa/Xóa <?php // Thêm icon nếu dùng FontAwesome ?>
                                </a>
                            </td>
                        </tr>
                <?php
                    } // end while
                } else { // Trường hợp không có loại sản phẩm nào
                ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted p-3">Chưa có loại sản phẩm nào.</td>
                    </tr>
                <?php
                } // end if
                ?>
            </tbody>
        </table>
    </div> </div> <?php include_once ('footer.php');?>
</body>
</html>