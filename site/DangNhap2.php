<?php

session_start();
    include("../connection/connect_database.php");
    if (isset($_GET['user']) && isset($_GET['pass'])) {
        $username = $_GET['user'];
        $password = $_GET['pass'];
        //xóa tag html và ký tự đặc biệt
        $username = strip_tags($username);
        $username = addslashes($username);
        $password = strip_tags($password);
        $password = addslashes($password);

            $sql = "select * from Users where HoTen='" . $username . "' and Password='" . $password. "'";
            $query = mysqli_query($conn, $sql);
            $num_rows = mysqli_num_rows($query);
            if ($num_rows > 0) {
                //lưu tên đăng nhập vào session
                $_SESSION['Username'] = $username;
                $r_us = mysqli_fetch_array($query);
                $_SESSION['HoTenK'] = $r_us['HoTenK'];
                $_SESSION['IDUser'] = $r_us['idUser'];
                if ($username == 'admin') {
                    echo "<script language='JavaScript' >location.href='../site/ThanhToan.php';</script>";
                } else {
                    echo "<script language='javascript'> alert('thanh cong');location.href='../site/ThanhToan.php';</script>";
                }
            } else {
                echo "<script language='javascript'>alert('Đăng nhập thất bại!');</script>";
            }

    }
