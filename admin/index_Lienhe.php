<?php

include_once ('../connection/connect_database.php');
$sl_lh= "select * from gopy_lienhe";
$rs_lh = mysqli_query($conn,$sl_lh);
if(!$rs_lh)
    echo "Không thể truy vấn CSDL";?>
?>
<!DOCTYPE html>
<html>
<header>
    <?php include_once ("header1.php");?>
    <title>Danh sách liên hệ </title>
    <?php include_once('header2.php');?>
    <style>
    /* Thay đổi màu chữ "DANH SÁCH LH" thành màu xanh dương */
    h3 {
        color: #1F75FE; /* Màu xanh dương */
        text-align: center;
    }
</style>

</header>
<body>
<?php include_once ('header3.php');?>
<!-- Nội dung ở đây -->
<h3 style="text-align: center">DANH SÁCH LIÊN HỆ</h3>
<div style="overflow-x: scroll"> <table class=" table table-bordered hover"  style="overflow-x: scroll" border="2">
        <thead class="text-center">
        <tr>
            <td width=""><strong> STT</strong></td>
            <td width=""><strong> HỌ TÊN </strong></td>
            <td width=""><strong> NỘI DUNG </strong></td>
            <td width=""><strong> ĐIỆN THOẠI </strong></td>
            <td width=""><strong> EMAIL </strong></td>
            <td width=""><strong> NGÀY GỬI</strong></td>
            <td COLSPAN="2"><a href="Them_Lienhe.php" ><strong> THÊM </strong></a></td>
        </tr>
        </thead>
        <?php $stt = 0;?>
        <?php while ($r = $rs_lh->fetch_assoc()) {?>
            <tbody>
            <td width=""><strong> <?php echo ++$stt ;?> </strong></td>
            <td width=""><strong><?php echo $r['HoTen'];?> </strong></td>
            <td width=""><strong><?php echo $r['noidung'];?> </strong></td>
            <td width=""><strong><?php echo $r['DienThoai'];?> </strong></td>
            <td width=""><strong><?php echo $r['Email'];?> </td>
            <td width=""><strong><?php echo $r['NgayGui'];?> </strong></td>
            <td><a href="sua_lienhe.php?idGL=<?php echo $r['idGL'];?>" ><strong> SỬA </strong></a></td>
            <td><a href="xoa_lienhe.php?idGL=<?php echo $r['idGL'];?>"><strong> XÓA </strong></a></td>
            </tbody>
        <?php }?>
    </table></div>

<?php include_once ('footer.php');?>
</body>
</html>