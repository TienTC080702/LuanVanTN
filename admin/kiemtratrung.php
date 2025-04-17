<?php


// hàm kiểm tra sự tồn tại của $k trong bảng $ten_ban với $ma, $con: kết nối csdl
function kiemtra($k,$ten_bang,$ma,$con)
{
    $sql_ktra = "select * from $ten_bang WHERE $ma = $k";
    $rs = mysqli_query($con,$sql_ktra);
    if($rs->field_count != 0)
        return false;
    return true;
}