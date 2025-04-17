<?php

include_once ('../connection/connect_database.php');
$sl="select * from gopy_lienhe where idGL=".$_GET['idGL'];
$kq=mysqli_query($conn,$sl);
$d=mysqli_fetch_array($kq);
if(isset($_POST['Sua'])){
    $sql = "update gopy_lienhe set HoTen='" . $_POST['HoTen'] . "',
        noidung='" . $_POST['noidung'] . "',       
        DienThoai='" . $_POST['DienThoai'] . "',
        Email='" . $_POST['Email'] . "',
        NgayGui='". $_POST['NgayGui'] . "'
        where idGL=" . $_GET['idGL'];
    $query = mysqli_query($conn, $sql);
    if ($query) {
        echo "<script language='javascript'>alert('Sửa thành công');";
        echo "location.href='../admin/index_Lienhe.php';</script>";
    } else
        die("Sửa không hợp lệ!");

}
else
    echo "Thêm không thành công"
?>

<!DOCTYPE html>
<html>
<header>
    <?php include_once ("header1.php");?>
    <title>Sửa liên hệ </title>
    <?php include_once('header2.php');?>
    <script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
    <style>div.row {
            padding-top: 2%;
        }</style>
</header>
<body>
<?php include_once ('header3.php');?>
<!-- Nội dung ở đây -->
<h3 style="text-align: center"> Liên hệ </h3>
<form method="post" action="" name="index_Lienhe" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-2"><strong>Họ Tên: </strong></div>
        <div class="col-md-9"><input type="text" name="HoTen" placeholder="ví dụ: Nguyễn Văn A" size="50" value="<?php echo $d['HoTen'];?>" /></div>
    </div>
    <div class="row">
        <div class="col-md-2"><strong>Nội dung: </strong></div>
        <div class="col-md-9"><textarea id="noidung" name="noidung" cols="" rows=""><?php echo $d['noidung'];?></textarea></div>
        <script type="text/javascript">
            CKEDITOR.replace('noidung');
        </script>
    </div>
    <div class="row">
        <div class="col-md-2"><strong>Điện thoại: </strong></div>
        <div class="col-md-9"><input type="tel" name="DienThoai" placeholder="ví dụ: 0169999999" size="50" value="<?php echo $d['DienThoai'];?>"/></div>
    </div>
    <div class="row">
        <div class="col-md-2"><strong>Email: </strong></div>
        <div class="col-md-9"><input type="email" name="Email" placeholder="ví dụ: abc@gmail.com" size="50" value="<?php echo $d['Email'];?>"/></div>
    </div>
    <div class="row">
        <div class="col-md-2"><strong>Ngày gửi: </strong></div>
        <div class="col-md-9"><input type="text" name="NgayGui" readonly="readonly" size="50" value="<?php echo date("Y-m-d");?>"/></div>
    </div>
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-9"><input type="submit" name="Sua" value="Sửa"/>     <input type="reset" name="Cancel" value="Hủy"/></div>
    </div>

</form>
<?php include_once ('footer.php');?>
</body>
</html>
