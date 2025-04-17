<?php

include_once ('../connection/connect_database.php');
$sl_mota = "select MoTa from sanpham WHERE idSP=".$_GET['idSP'];
$rs_mota = mysqli_query($conn,$sl_mota);
if(!$rs_mota)
    echo "Không thể truy vấn CSDL";?>?>
<!DOCTYPE html>
<html>
<header>
    <?php include_once ("header1.php");?>
    <title>Nội dung sản phẩm</title>
    <?php include_once('header2.php');?>
</header>
<body>
<?php include_once ('header3.php');?>
<!-- Nội dung ở đây -->
<?php $r = $rs_mota->fetch_assoc(); echo  $r['MoTa'];?>


<?php include_once ('footer.php');?>
</body>
</html>