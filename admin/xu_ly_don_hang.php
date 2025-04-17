<?php
include_once('../connection/connect_database.php');
$sl_donhang = "select * from donhang where DaXuLy=3 order by idDH desc";
$rs_donhang = mysqli_query($conn, $sl_donhang);
if (!$rs_donhang)
    echo "Không thể truy vấn CSDL"; ?>
<!DOCTYPE html>
<html>
<header>
    <?php include_once("header1.php"); ?>
    <title>Danh sách đơn hàng </title>
    <?php include_once('header2.php'); ?>
</header>
<body>
<?php include_once('header3.php'); ?>
<!--Content Start Here -->
<h3 style="text-align: center">ĐƠN HÀNG YÊU CẦU HỦY</h3>
<form>
    <table class=" table table-bordered hover" style="overflow-x: scroll" border="2">
        <thead class="text-center">
        <tr>
            <td width=""><strong> MĐH</strong></td>
            <td width=""><strong> ID USER </strong></td>
            <td width=""><strong> TÊN NGƯỜI NHẬN </strong></td>
            <td width=""><strong> SĐT</strong></td>
            <td width=""><strong> TỔNG TIỀN</strong></td>
            <td width=""><strong> TRẠNG THÁI</strong></td>
            <td width=""><strong> NGÀY ĐẶT </strong></td>
            <td width=""><strong> NGÀY HỦY </strong></td>
            <td width=""><strong> LÝ DO </strong></td>
            <td><strong> THAO TÁC </strong></a></td>
        </tr>
        </thead>
        <?php $stt = 0;
        while ($r = $rs_donhang->fetch_assoc()) { ?>
            <tbody>
            <td>
                <strong> <?php echo $r['idDH']; ?> </strong>
            </td>
            <td width=""><strong><?php echo $r['idUser']; ?> </strong></td>
            <td width=""><strong><?php echo $r['TenNguoiNhan']; ?> </strong></td>
            <td width=""><strong><?php echo $r['SDT']; ?></td>
            <td width=""><strong><?php
                    $sl_sp_ctdh="select sum(SoLuong*Gia) as TongTien from sanpham a, donhangchitiet b where a.idSP=b.idSP and idDH=".$r['idDH'];
                    $rs_tt=mysqli_query($conn,$sl_sp_ctdh);
                    $d=mysqli_fetch_array($rs_tt);
                    echo $d['TongTien'];
                    ?> </strong></td>
            <td width=""><strong><?php echo $r['DaXuLy']; ?> </strong></td>
            <td width=""><strong><?php echo $r['ThoiDiemDatHang']; ?> </strong></td>
            <td width=""><strong><?php echo $r['ThoiDiemDatHang']; ?> </strong></td>
            <td width=""><strong><?php echo $r['ThoiDiemDatHang']; ?> </strong></td>
            <td><a href="xoa_donhang.php?idDH=<?php echo $r['idDH'];?>" onclick="return confirm('Bạn có muốn xóa không?');";><strong> XÓA </strong></a>/
                <a href="xuly.php?idDH=<?php echo $r['idDH'];?>"><strong> XÁC NHẬN </strong></a></td>
            </tbody>
        <?php } ?>
    </table>
</form>

<?php include_once('footer.php'); ?>
</body>
</html>
