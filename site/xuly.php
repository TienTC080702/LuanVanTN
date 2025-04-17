<?php
include_once ('../connection/connect_database.php');
if(isset($_GET['idDH'])) {
    $sl_dhct = "update donhangchitiet SET DaXuLy=2 where idDH=". $_GET['idDH'];
    $kq_dhct = mysqli_query($conn, $sl_dhct);
    $sl_dh = "update donhang SET DaXuLy=2 where idDH=". $_GET['idDH'];
    $kq_dh = mysqli_query($conn, $sl_dh);
    if($kq_dhct&&$kq_dh)
    {
        echo "<script language='javascript'>location.href='DonHang.php';</script>";
    }
    else
        echo "fail";
}
?>