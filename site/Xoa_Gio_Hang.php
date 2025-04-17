<?php

session_start();
$id =(int) $_GET['idSP'];
unset($_SESSION['cart'][$id]);
$id =1;

header("location:GioHang.php?idSP=".$id);