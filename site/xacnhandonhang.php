<?php
include_once('../connection/connect_database.php');
$sl_donhang = "select * from donhang";
$rs_donhang = mysqli_query($conn, $sl_donhang);
if (!$rs_donhang)
    echo "Không thể truy vấn CSDL"; ?>
<!DOCTYPE html>
<html>
<header>
    <?php include_once("header1.php"); ?>
    <title>Quản lý đơn hàng </title>
    <?php include_once('header2.php'); ?>
</header>
<body>

<!--Content Start Here -->
<h3 style="text-align: center; margin-top: -40px">ĐƠN HÀNG CỦA TÔI</h3>
<div class="main" style="margin-left:10%; margin-right:10%; height:380px">
<div style="overflow-x: scroll">
    <table class=" table table-bordered hover" style="overflow-x: scroll" border="2">
        <thead class="text-center">
        <tr>
            <td width=""><strong> MĐH</strong></td>
            <td width=""><strong> ID USER </strong></td>
            <td width=""><strong> THỜI GIAN ĐẶT</strong></td>
            <td width=""><strong> TÌNH TRẠNG </strong></td>
            <td width=""><strong> TỔNG TIỀN</strong></td>
            <td><strong> THAO TÁC </strong></a></td>
        </tr>
        </thead>
        <?php $stt = 0;
        if (isset($_SESSION['IDUser'])) {
            $sql_u = "select * from donhang where idUser=" . (int)$_SESSION['IDUser'];
            $query_u = mysqli_query($conn, $sql_u);
            //$r_us = mysqli_fetch_array($query_u);
            $flag = true;/// đã đăng nhập
        }
        while ($r = $query_u->fetch_assoc()) { 
            ?>
            <tbody>         
            <td width=""><strong><?php echo $r['idDH']; ?><strong></td>
            <td width=""><strong><?php if($flag==true)echo $r['idUser']; ?> </strong></td>
            <td width=""><strong><?php echo $r['ThoiDiemDatHang']; ?> </strong></td>
            <td width=""><strong><?php if($r['DaXuLy'] ==1 ) echo "Đang giao hàng"; else echo "Chờ xác nhận";?> </strong></td>
            <td width=""><strong><?php
                    $sl_sp_ctdh="select sum(SoLuong*Gia) as TongTien from sanpham a, donhangchitiet b where a.idSP=b.idSP and idDH=".$r['idDH'];
                    $rs_tt=mysqli_query($conn,$sl_sp_ctdh);
                    $d=mysqli_fetch_array($rs_tt);
                    echo $d['TongTien'];
                    ?> </strong></td>
            <?php
                if($r['DaXuLy']=='0'){
                ?>
                <td><?php echo 'N/A';?></td>
                <?php
                
                }elseif($r['DaXuLy']=='1'){
                
                ?>
                <td>Đã nhận</td>
                <?php
            }else{
                ?>
                <td><?php echo 'Đã nhận'; ?></td>
                <?php
                }	
                ?>
            <!-- <td><a href="xoa_donhang.php?idDH=<?php echo $r['idDH'];?>"><strong> HỦY ĐƠN </strong></a></td> -->
            </tbody>
        <?php } ?>
    </table>
</div>
</div>

<?php include_once('footer.php'); ?>
</body>
</html>
