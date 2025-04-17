
<?php
include_once('../connection/connect_database.php');
$sl_donhang = "select * from donhang";
$rs_donhang = mysqli_query($conn, $sl_donhang);
if (!$rs_donhang)
    echo "Không thể truy vấn CSDL"; ?>
<?php if (isset($_SESSION['IDUser'])) {
    $sql_u = "select * from donhang where idUser=" . (int)$_SESSION['IDUser'];
    $query_u = mysqli_query($conn, $sql_u);
    $r_us = mysqli_fetch_array($query_u);
    $flag = true;/// đã đăng nhập
} ?>
<!DOCTYPE html>
<html>
<header>
    <?php include_once("header1.php"); ?>
    <title>Quản lý đơn hàng </title>
    <?php include_once('header2.php'); ?>
    <?php include ('../libs/lib.php');?>
    <style>
        .box{ border-radius: 2%;}
        .box.hover{border-color: green;}
        .boxsizing {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            width: 180px;
            height: 182px;
            border: 1px solid blue;
        }
        div.row{padding-top: 2%;}

        .buyproduct{ background-color: #fbc902;}
    </style>
    <style type="text/css">
        .hovergallery:hover{
            -webkit-transform:scale(1.2); /*Webkit: Scale up image to 1.2x original size*/
            -moz-transform:scale(1.2); /*Mozilla scale version*/
            -o-transform:scale(1.2); /*Opera scale version*/
            box-shadow:0px 0px 30px gray; /*CSS3 shadow: 30px blurred shadow all around image*/
            -webkit-box-shadow:0px 0px 30px gray; /*Safari shadow version*/
            -moz-box-shadow:0px 0px 30px gray; /*Mozilla shadow version*/
            opacity: 1;
        }
        </style>
        <link rel="stylesheet" href="../css/hoa.min.css" type="text/css">
        <link rel="stylesheet" href="../css/layout.min.css" type="text/css">
        <script type="text/javascript" src="../js/jquery-3.1.1.min.js"></script>
        <script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
    </header>
<body>
<!-- Content -->
<h3 style="text-align: center; margin-top: -40px">LÝ DO HỦY ĐƠN</h3>
<div class="center" style="width:50%; margin-left:400px">
<div class="row" style="margin-right:10%"><strong>Nêu lý do:</strong></div>
    <?php
    if(isset($_POST['binhluan'])) {
        if (!isset($_SESSION['Username'])) {
            echo "<script language='javascript'>alert('Bạn phải đăng nhập!');";
            header('location: ../site/DangNhap.php');
        } else {
            $sl_cm = "insert into donhang_huy(idDH,idUser,NoiDung,NgayHuy) 
                    VALUES('" . $_GET['idDH'] . "','" . $_SESSION['Username'] . "','" . $_POST['comment'] . "','" . date("Y-m-d h:i:s") . "') ";
            $rs_themcm = mysqli_query($conn, $sl_cm);
            if ($rs_themcm) { ?>
                <div class="row" style="color: darkblue; margin-left:2px;font-size: 150%; font-family: 'Microsoft Sans Serif' , Arial, Helvetica, Verdana;margin-bottom: -20px;">

                    <strong><?php echo $_SESSION['Username']; ?></strong>
                </div>
                <div style="margin-left: 58px;"><i><?php echo date("Y-m-d h:i:s"); ?></i></div>
                <div class="prod-box"><?php echo $_POST['comment']; ?></div>
            <?php } else {
                echo "<script language='javascript'>alert('Thêm không thành công!');</script>";
            }
        }
    }
    ?>
    <div class="row">
        <textarea name="comment" id="comment" rows=""></textarea>
    </div>
        <script type="text/javascript">
            CKEDITOR.replace('comment');
        </script>
    <div class="row">
        <button id="binhluan" name="binhluan"  class="btn btn-success">Gửi yêu cầu</button>
    </div>
</div>
</div>
<?php include_once('footer.php'); ?>
</body>
</html>
