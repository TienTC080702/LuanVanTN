<?php
include_once ('../connection/connect_database.php');
if(isset($_GET['idDH'])) {
    $sl_dhct = "update donhangchitiet SET DaXuLy=4 where idDH=". $_GET['idDH'];
    $kq_dhct = mysqli_query($conn, $sl_dhct);
    $sl_dh = "update donhang SET DaXuLy=4 where idDH=". $_GET['idDH'];
    $kq_dh = mysqli_query($conn, $sl_dh);
    if($kq_dhct&&$kq_dh)
    {
        mysqli_query($conn, "UPDATE sanpham SET SoLuongTonKho=SoLuongTonKho+'$soluong' WHERE idSP='$idSP'");
        echo "<script language='javascript'>location.href='xu_ly_don_hang.php';</script>";
    }
    else
        echo "fail";
}

?>