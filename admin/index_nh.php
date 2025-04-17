<?php
include_once ('../connection/connect_database.php');

// Giữ nguyên query gốc lấy danh sách nhãn hiệu
$sl_nhanhieu = "SELECT * FROM nhanhieu"; // Bỏ ORDER BY nếu không có trong gốc
$rs_nhanhieu = mysqli_query($conn, $sl_nhanhieu);
if (!$rs_nhanhieu) {
    // Giữ nguyên cách báo lỗi gốc
    echo "Không thể truy vấn CSDL";
    exit(); // Thêm exit để dừng hẳn nếu lỗi
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Danh sách nhãn hiệu</title>
    <?php include_once('header2.php'); ?>
    <style>
        /* Giữ lại style cho tiêu đề */
        .page-title {
            color: #1F75FE; /* Màu xanh dương */
            text-align: center;
            margin-bottom: 30px; /* Thêm khoảng cách dưới tiêu đề */
        }
        /* CSS tùy chỉnh cho cột */
         .action-col {
            white-space: nowrap;
            width: 1%;
            text-align: center;
        }
        .status-col {
             width: 100px;
             text-align: center;
        }
         .stt-col {
             width: 60px;
             text-align: center;
         }
         .type-col {
            min-width: 150px;
         }
         .table-sm td, .table-sm th {
            vertical-align: middle; /* Căn giữa nội dung trong ô */
         }
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <h3 class="page-title">DANH SÁCH CÁC NHÃN HIỆU</h3>

    <div class="d-flex justify-content-end mb-3">
         <?php /* Giữ nguyên cấu trúc nút gốc nhưng dùng class btn-primary */ ?>
        <a href="them_nh.php"><button type="button" class="btn btn-primary"> <i class="fas fa-plus me-1"></i> THÊM NHÃN HIỆU </button></a>
    </div>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered table-hover table-striped table-sm mb-0">
            <thead class="table-light text-center"> <?php // Dùng thead-light ?>
                <tr>
                    <th class="stt-col">STT</th>
                    <th>TÊN NHÃN HIỆU</th>
                    <th class="type-col">TÊN LOẠI</th>
                    <th class="status-col">ẨN/HIỆN</th>
                    <th class="action-col">THAO TÁC</th>
                </tr>
            </thead>
            <tbody> <?php // tbody đặt ngoài vòng lặp ?>
                <?php
                if (mysqli_num_rows($rs_nhanhieu) > 0) {
                    $stt = 1; // Bắt đầu STT từ 1
                    while ($r = $rs_nhanhieu->fetch_assoc()) {
                ?>
                        <tr>
                            <td class="text-center"><?php echo $stt++; ?></td>
                            <td><?php echo htmlspecialchars($r['TenNH']); // Giữ htmlspecialchars ?></td>
                            <td class="type-col">
                                <?php
                                // --- Giữ nguyên logic query con gốc ---
                                $tenL = "Không xác định"; // Giá trị mặc định
                                if (!empty($r['idL'])) {
                                    $idL_current = (int)$r['idL']; // Ép kiểu an toàn
                                    $sl_loai = "SELECT TenL FROM loaisp WHERE idL = " . $idL_current; // Query gốc
                                    $result_loai = mysqli_query($conn, $sl_loai);
                                    if ($result_loai && mysqli_num_rows($result_loai) > 0) {
                                        $r_loai = mysqli_fetch_array($result_loai); // Dùng fetch_array như gốc
                                        $tenL = $r_loai['TenL'];
                                    }
                                    // Không có else trong code gốc để xử lý lỗi query con
                                }
                                // ------------------------------------
                                echo htmlspecialchars($tenL); // Hiển thị tên loại
                                ?>
                            </td>
                            <td class="status-col">
                                <?php // Sử dụng badge để hiển thị trạng thái đẹp hơn
                                    if ($r['AnHien'] == 1) {
                                        echo '<span class="badge bg-success">Hiện</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">Ẩn</span>';
                                    }
                                ?>
                            </td>
                            <td class="action-col">
                                <?php // Link sửa/xóa giữ nguyên nhưng tạo kiểu như nút ?>
                                <a href="sua_xoa_nh.php?idNH=<?php echo $r['idNH']; ?>" class="btn btn-warning btn-sm" title="Sửa/Xóa <?php echo htmlspecialchars($r['TenNH']); ?>">
                                   <i class="fas fa-edit"></i> SỬA/XÓA
                                </a>
                            </td>
                        </tr>
                <?php
                    } // end while
                } else { // Không có nhãn hiệu nào
                ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted p-3">Chưa có nhãn hiệu nào.</td>
                    </tr>
                <?php
                } // end if
                ?>
            </tbody>
        </table>
    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>