<?php
include_once ('../connection/connect_database.php');
$sl_thanhtoan = "select * from phuongthucthanhtoan";
$rs_thanhtoan = mysqli_query($conn, $sl_thanhtoan);
if (!$rs_thanhtoan) {
    die("Không thể truy vấn CSDL: " . mysqli_error($conn)); 
}
?>

<!DOCTYPE html>
<html>
<head>
    <?php include_once ("header1.php");?>
    <title>Phương thức thanh toán</title>
    <?php include_once('header2.php');?>
    <style>
        h3.page-title {
            color: #1F75FE; 
            text-align: center;
            margin-top: 20px; 
            margin-bottom: 20px; 
        }
        .add-button-container {
            /* --- THAY ĐỔI Ở ĐÂY --- */
            text-align: left; /* Chuyển nút THÊM MỚI sang trái */
            margin-bottom: 15px; 
        }
        
        /* --- CSS Căn Chỉnh Bảng --- */
        .table th, .table td {
             vertical-align: middle !important; 
        }
        .table thead th {
            text-align: center; 
            white-space: nowrap; 
        }
        .table thead .th-stt { width: 5%; }
        .table thead .th-trangthai { width: 12%; }
        .table thead .th-thaotac { width: 15%; }
        .table thead .th-ten { text-align: left; } 
        .table thead .th-ghichu { text-align: left; } 
        .table tbody .td-stt,
        .table tbody .td-trangthai,
        .table tbody .td-thaotac {
            text-align: center; 
        }
        .table tbody .td-ten {
             text-align: left;
        }
         .table tbody .td-ghichu {
             text-align: left;
        }
        .badge {
            font-size: 0.85em; 
            padding: 0.4em 0.6em; 
        }
        
    </style>
</head>
<body>
<?php include_once ('header3.php');?>

<div class="container mt-4"> 

    <h3 class="page-title">DANH SÁCH PHƯƠNG THỨC THANH TOÁN</h3>

    <div class="add-button-container"> 
        <a href="them_pttt.php" class="btn btn-info"><strong>THÊM MỚI</strong></a> 
    </div>

    <div class="table-responsive shadow-sm"> 
        <table class="table table-bordered table-striped table-hover">
            <thead class="thead-light"> 
                <tr> 
                    <th class="th-stt">STT</th>
                    <th class="th-ten">TÊN PHƯƠNG THỨC THANH TOÁN</th>
                    <th class="th-ghichu">GHI CHÚ</th>
                    <th class="th-trangthai">TRẠNG THÁI</th> 
                    <th class="th-thaotac">THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 0;
                if (mysqli_num_rows($rs_thanhtoan) > 0) {
                    while ($r = $rs_thanhtoan->fetch_assoc()) {
                        $stt++;
                ?>
                    <tr>
                        <td class="td-stt"><?php echo $stt; ?></td>
                        <td class="td-ten"><?php echo htmlspecialchars($r['TenPhuongThucTT']); ?></td>
                        <td class="td-ghichu"><?php echo htmlspecialchars($r['GhiChu']); ?></td>
                        <td class="td-trangthai">
                            <?php 
                            if ($r['AnHien'] == 1) {
                                echo '<span class="badge bg-success">Hiện</span>'; 
                            } else {
                                echo '<span class="badge bg-secondary">Ẩn</span>'; 
                            }
                            ?> 
                        </td>
                        <td class="td-thaotac">
                            <a href="sua_xoa_pttt.php?idPTTT=<?php echo $r['idPTTT']; ?>" class="btn btn-warning btn-sm" title="Sửa hoặc Xóa">Sửa / Xóa</a>
                        </td>
                    </tr>
                <?php 
                    } // end while
                } else {
                    echo '<tr><td colspan="5" class="text-center p-3">Không có dữ liệu phương thức thanh toán nào.</td></tr>'; 
                }
                ?>
            </tbody>
        </table>
    </div> 

</div> 

<?php include_once ('footer.php');?>

</body>
</html>