<?php

include_once ('../connection/connect_database.php');
$sl_thanhtoan = "select * from phuongthucthanhtoan";
$rs_thanhtoan = mysqli_query($conn,$sl_thanhtoan);
if(!$rs_thanhtoan)
    echo "Không thể truy vấn CSDL";?>

<!DOCTYPE html>
<html>
<header>
    <?php include_once ("header1.php");?>
    <title>Thống kê sản phẩm </title>
    <?php include_once('header2.php');?>
    <style>
    /* Thay đổi màu chữ "DANH SÁCH" thành màu xanh dương */
    h3 {
        color: #1F75FE; /* Màu xanh dương */
        text-align: center;
    }
</style>
</header>
<body>
<?php include_once ('header3.php');?>
<!--Content Start Here -->
<h3 style="text-align: center">DANH SÁCH THỐNG KÊ </h3>
<a href="them_pttt.php?idPTTT=<?php echo $r['idPTTT'];?>" ><strong><button type="button" class="btn btn-info"> THÊM </button></strong></a>
<div>
    <table class=" table table-bordered " border="2">
        <thead class="text-center">
        <tr>
            <td width=""><strong> STT</strong></td>
            <td width=""><strong> TÊN PHƯƠNG THỨC THANH TOÁN </strong></td>
            <td width=""><strong> GHI CHÚ </strong></td>
            <td width=""><strong> ẨN / HIỆN </strong></td>
            <td width=""><strong> THAO TÁC </strong></td>
        </tr>
        </thead>
        <?php $stt = 0;?>
        <?php while ($r = $rs_thanhtoan->fetch_assoc()) {?>
            <tbody>
            <td width="5%"><strong> <?php echo ++$stt ;?> </strong></td>
            <td width="30%"><strong><?php echo $r['TenPhuongThucTT'];?> </strong></td>
            <td width="30%"><strong><?php echo $r['GhiChu'];?> </strong></td>
            <td width="10%"><strong><?php if($r['AnHien'] ==1 ) echo "Hiện"; else echo "Ẩn";?> </strong></td>
            <td><a href="sua_xoa_pttt.php?idPTTT=<?php echo $r['idPTTT'];?>" ><strong> SỬA/XÓA </strong></a></td>
            </tbody>
        <?php }?>
    </table>
</div>
<?php include_once ('footer.php');?>
</body>
</html>
