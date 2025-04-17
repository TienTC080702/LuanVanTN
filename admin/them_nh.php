<?php
// Giữ nguyên phần PHP xử lý form ban đầu
include ('../connection/connect_database.php');
if(isset($_POST['TenNH']) && isset($_POST['ok']) )
{
    // Giữ nguyên query kiểm tra trùng tên
    $sl_nhanhieu = "select * from nhanhieu";
    $rs_nhanhieu = mysqli_query($conn,$sl_nhanhieu);
    if(!$rs_nhanhieu)
    {
        // Thông báo lỗi này có vẻ không đúng ngữ cảnh, nhưng giữ nguyên theo code gốc
        echo "<script language='javascript'>alert('Thêm thành công');"; // ?!?! Nên là lỗi truy vấn
        echo "location.href = 'index_nh.php'</script>";
        exit; // Thêm exit
    }
    $check = false; // biến kiểm tra trùng tên
    while ($r = $rs_nhanhieu->fetch_assoc())
    {
        // Giữ nguyên cách kiểm tra trùng tên
        if($r['TenNH'] == $_POST['TenNH'])
        {
            $check = true;
            break; // Thêm break để tối ưu vòng lặp khi đã tìm thấy trùng
        }
    }
    if($check == false)
    {
        // Giữ nguyên query INSERT bằng nối chuỗi (CẢNH BÁO BẢO MẬT!)
        $query = "INSERT INTO nhanhieu(TenNH,idL,ThuTu,AnHien) values ('".$_POST['TenNH']."',".$_POST['idL']." ,  ".$_POST['ThuTu'].",".$_POST['AnHien'].")";
        $result = mysqli_query($conn,$query);
        if($result)
        {
            echo "<script language='javascript'>alert('Thêm thành công');";
            echo "location.href = 'index_nh.php'</script>";
            exit; // Thêm exit
        }
        else
            // Hiển thị lỗi SQL nếu có (chỉ nên dùng khi đang phát triển)
            // echo "<script language='javascript'>alert('Thêm không thành công! Lỗi: ".mysqli_error($conn)."');</script>";
             echo "<script language='javascript'>alert('Thêm không thành công');</script>"; // Giữ nguyên alert gốc
    }else
        echo "<script LANGUAGE='JavaScript'> alert('Tên nhãn hiệu đã tồn tại!');</script>"; // Sửa lại thông báo cho đúng ngữ cảnh
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once ("header1.php");?>
    <title>Thêm Nhãn Hiệu</title>
    <?php include_once('header2.php');?>
    <style>
        /* CSS tùy chỉnh nhẹ nếu cần */
        .page-title {
            color: #007bff; /* Màu xanh dương */
            text-align: center;
            margin-bottom: 30px;
        }
        .custom-form-container {
            max-width: 750px; /* Điều chỉnh độ rộng nếu cần */
            margin: 30px auto; /* Căn giữa và thêm khoảng cách */
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .button-group {
            text-align: center;
            margin-top: 25px;
        }
        .button-group .btn {
            margin: 0 5px; /* Khoảng cách giữa các nút */
        }
        /* Bỏ các style cũ cho input, form-container width, nút */
    </style>
</head>
<body>
<?php // include ('../connection/connect_database.php');?> <?php // Chỉ cần include 1 lần ở đầu ?>
<?php include_once ('header3.php');?>

<div class="container mt-4"> <?php // Thêm container ?>
    <div class="custom-form-container">
        <h3 class="page-title">THÊM NHÃN HIỆU MỚI</h3>
        <form method="post" action="" name="ThemNHForm" id="ThemNHForm" enctype="multipart/form-data">
            <div class="row mb-3"> <?php // Thêm mb-3 cho khoảng cách ?>
                <label for="TenNH" class="col-sm-3 col-form-label"><strong>Tên nhãn hiệu</strong></label> <?php // Dùng label và col-sm-* ?>
                <div class="col-sm-9">
                    <input type="text" name="TenNH" id="TenNH" class="form-control" required> <?php // Thêm class và required ?>
                </div>
            </div>

            <div class="row mb-3">
                <label for="idL" class="col-sm-3 col-form-label"><strong>Loại sản phẩm</strong></label> <?php // Sửa label ?>
                <div class="col-sm-9">
                    <?php
                        // Giữ nguyên query lấy loại SP gốc
                        $sl_l = "select * from loaisp";
                        $rs_l = mysqli_query($conn,$sl_l);
                        if(!$rs_l) {
                            echo "<div class='text-danger'>Lỗi: Không thể truy vấn Loại sản phẩm!</div>";
                        } else {
                    ?>
                    <select name="idL" id="idL" class="form-select" required> <?php // Thêm class và required ?>
                        <option value="">-- Chọn loại sản phẩm --</option> <?php // Thêm option mặc định ?>
                        <?php while ($row_l = $rs_l->fetch_assoc()) { ?>
                            <option value="<?php echo $row_l["idL"];?>"><?php echo htmlspecialchars($row_l['TenL']);?></option> <?php // Thêm htmlspecialchars ?>
                        <?php } ?>
                    </select>
                    <?php } // end if $rs_l ?>
                </div>
            </div>

            <div class="row mb-3">
                <label for="ThuTu" class="col-sm-3 col-form-label"><strong>Thứ tự</strong></label>
                <div class="col-sm-9">
                    <input type="number" name="ThuTu" id="ThuTu" class="form-control" value="0"> <?php // Thêm class và giá trị mặc định ?>
                </div>
            </div>

            <div class="row mb-3">
                <label for="AnHien" class="col-sm-3 col-form-label"><strong>Trạng thái</strong></label> <?php // Sửa label ?>
                <div class="col-sm-9">
                    <select name="AnHien" id="AnHien" class="form-select"> <?php // Thêm class ?>
                        <option value="1" selected>Hiện</option> <?php // Bỏ strong, thêm selected ?>
                        <option value="0">Ẩn</option> <?php // Bỏ strong ?>
                    </select>
                </div>
            </div>

            <div class="button-group"> <?php // Nhóm các nút lại ?>
                 <?php // Giữ nguyên input type nhưng thêm class btn ?>
                <input name="ok" type="submit" value="Lưu" class="btn btn-success" /> <?php // Đổi class và value ?>
                <input name="reset" type="reset" value="Tạo Lại" class="btn btn-warning"> <?php // Đổi class ?>
                <input type="button" name="Huy" value="Hủy" class="btn btn-secondary" onclick="getConfirmation();"> <?php // Đổi class ?>
            </div>
        </form>
    </div></div> <script type="text/javascript">
    // Giữ nguyên hàm JS gốc
    function getConfirmation(){
        var retVal = confirm("Bạn có muốn hủy ?");
        if( retVal == true ){
            // Sửa lại link cho đúng ngữ cảnh trang nhãn hiệu
            location.href = 'index_nh.php'; // Chuyển về trang danh sách nhãn hiệu
        }
    }

     // Optional: Client-side validation
    document.getElementById('ThemNHForm').addEventListener('submit', function(event) {
        var tenNHInput = document.getElementById('TenNH');
        var idLSelect = document.getElementById('idL');

        if (tenNHInput.value.trim() === '') {
            alert('Vui lòng nhập Tên nhãn hiệu.');
            event.preventDefault();
            tenNHInput.focus();
            return;
        }
         if (idLSelect.value === '') {
            alert('Vui lòng chọn Loại sản phẩm.');
            event.preventDefault();
            idLSelect.focus();
            return;
        }
    });
</script>

<?php include_once ('footer.php');?>
</body>
</html>