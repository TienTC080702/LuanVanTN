<?php
include_once('../connection/connect_database.php');

if (isset($_POST['idDH']) && isset($_POST['status'])) {
    $idDH = $_POST['idDH'];
    $status = $_POST['status'];

    $sql_update = "UPDATE donhang SET DaXuLy = $status WHERE idDH = $idDH";
    $result = mysqli_query($conn, $sql_update);

    if ($result) {
        echo "<script>alert('Cập nhật trạng thái thành công!'); window.location.href='index_donhang.php';</script>";
    } else {
        echo "<script>alert('Cập nhật trạng thái không thành công!'); window.location.href='index_donhang.php';</script>";
    }
}
?>
