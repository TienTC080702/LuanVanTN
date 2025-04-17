<?php
session_start();
$id =(int) $_GET['idSP'];
$_SESSION['cart'][$id]['qty']--;
$id =1;
header("location:GioHang.php?idSP=".$id);