<?php
// PHP Logic giữ nguyên
include ('../connection/connect_database.php');
// ... (toàn bộ code PHP xử lý form của bạn giữ nguyên ở đây) ...
if(isset($_POST['TenPhuongThucTT']) && isset($_POST['ok'])) {
    $sl_pttt = "select * from phuongthucthanhtoan";
    $rs_pttt = mysqli_query($conn, $sl_pttt);
    if(!$rs_pttt) {
        echo "<script language='javascript'>alert('Thêm thành công');";
        echo "location.href = 'index_pttt.php'</script>";
    }
    else {
        $check = false;
        while ($r = $rs_pttt->fetch_assoc()) {
            if($r['TenPhuongThucTT'] == $_POST['TenPhuongThucTT']) {
                $check = true;
                break;
            }
        }

        if($check == false) {
            $query = "INSERT INTO phuongthucthanhtoan(TenPhuongThucTT,GhiChu,AnHien) values ('".mysqli_real_escape_string($conn, $_POST['TenPhuongThucTT'])."','".mysqli_real_escape_string($conn, $_POST['GhiChu'])."','".mysqli_real_escape_string($conn, $_POST['AnHien'])."')";
            $result = mysqli_query($conn,$query);
            if($result) {
                echo "<script language='javascript'>alert('Thêm thành công');";
                echo "location.href = 'index_pttt.php'</script>";
            } else {
                echo "<script language='javascript'>alert('Thêm không thành công. Lỗi: ".mysqli_error($conn)."');</script>";
            }
        } else {
            echo "<script language='JavaScript'> alert('Tên phương thức thanh toán này đã tồn tại!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <?php include_once ("header1.php");?>
    <title>Thêm Phương Thức Thanh Toán</title>
    <?php include_once('header2.php');?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #e9ecef; /* Màu nền trang giống ảnh (tùy chọn) */
        }
        .form-wrapper {
            background-color: #f8f9fa; /* Nền xám rất nhạt cho form */
            padding: 30px;
            border-radius: 8px;
            /* border: 1px solid #dee2e6; */ /* Bỏ border nếu muốn giống hệt ảnh */
            max-width: 700px; /* Giới hạn độ rộng tối đa */
            margin: auto; /* Căn giữa form */
        }
        .form-title-custom {
             color: #2E7D32; /* Màu xanh lá cây đậm */
             /* color: #4CAF50; */ /* Hoặc màu xanh lá cây sáng hơn */
             font-weight: bold;
             margin-bottom: 30px;
             text-align: center;
        }

        .form-label {
            margin-bottom: 0.5rem;
            font-weight: 500; /* Hơi đậm một chút */
             display: block; /* Đảm bảo label chiếm 1 dòng */
        }

        .required-star {
            color: red;
        }

        .helper-text {
            font-size: 0.875em;
            color: #6c757d; /* Màu xám cho text hướng dẫn */
             margin-top: 0.25rem;
             display: block;
        }

        /* Input và Select */
        .form-control, .form-select {
            border-color: #ced4da; /* Màu viền xám nhạt */
            border-radius: 4px; /* Bo góc nhẹ */
        }
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* CSS cho các nút tùy chỉnh */
        .button-group {
            text-align: center; /* Căn giữa các nút */
            margin-top: 30px; /* Khoảng cách trên nhóm nút */
            padding-top: 20px;
            border-top: 1px solid #dee2e6; /* Đường kẻ ngang phân tách nút */
        }

        .btn-custom {
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 1rem;
            margin: 0 5px;
            border: 1px solid transparent;
            transition: all 0.2s ease-in-out;
            display: inline-flex; /* Để icon và text thẳng hàng */
            align-items: center; /* Để icon và text thẳng hàng */
            gap: 0.5rem; /* Khoảng cách giữa icon và text */
        }

        .btn-custom-save {
            background-color: #4CAF50; /* Màu xanh lá cây */
            color: white;
            border-color: #4CAF50;
        }
        .btn-custom-save:hover {
            background-color: #45a049;
            border-color: #45a049;
            color: white;
        }

        .btn-custom-cancel {
            background-color: white;
            color: #343a40; /* Màu chữ đen/xám đậm */
            border-color: #343a40; /* Màu viền đen/xám đậm */
        }
         .btn-custom-cancel:hover {
            background-color: #f8f9fa; /* Nền hơi xám khi hover */
            color: #343a40;
            border-color: #343a40;
         }

    </style>
</head>
<body>
<?php include_once ('header3.php');?>

<div class="container mt-5 mb-5">
    <div class="form-wrapper shadow-sm"> <h3 class="form-title-custom">THÊM PHƯƠNG THỨC THANH TOÁN MỚI</h3>

        <form method="post" action="" name="ThemPTTT" id="paymentMethodForm">

            <div class="mb-4"> <label for="TenPhuongThucTT" class="form-label">
                    Tên phương thức thanh toán <span class="required-star">*</span>
                </label>
                <input type="text" class="form-control" id="TenPhuongThucTT" name="TenPhuongThucTT" placeholder="Ví dụ: Chuyển khoản ngân hàng, Tiền mặt khi nhận hàng..." required>
                </div>

            <div class="mb-4">
                <label for="GhiChu" class="form-label">Ghi Chú</label>
                <input type="text" class="form-control" id="GhiChu" name="GhiChu" placeholder="Ví dụ: Miễn phí cho đơn hàng trên 500k, Nội dung chuyển khoản...">
                 <small class="helper-text">Thông tin bổ sung hoặc hướng dẫn (nếu có).</small>
            </div>

            <div class="mb-4">
                <label for="AnHien" class="form-label">Trạng thái</label>
                <select class="form-select" id="AnHien" name="AnHien">
                    <option value="1">Hiện</option>
                    <option value="0">Ẩn</option>
                </select>
                 <small class="helper-text">Chọn "Hiện" để áp dụng phương thức này cho khách hàng.</small>
            </div>

            <div class="button-group">
                 <button name="ok" type="submit" class="btn btn-custom btn-custom-save" form="paymentMethodForm">
                     <i class="bi bi-plus-lg"></i> Thêm Phương Thức
                 </button>
                 <button type="button" name="Huy" class="btn btn-custom btn-custom-cancel" onclick="getConfirmation()">
                     <i class="bi bi-x-lg"></i> Hủy
                 </button>
            </div>

        </form>
    </div> </div>

<script type="text/javascript">
    // JavaScript giữ nguyên
    function getConfirmation() {
        var retVal = confirm("Bạn có chắc chắn muốn hủy bỏ thao tác này?");
        if(retVal == true) {
            location.href = 'index_pttt.php';
        }
    }
</script>

<?php include_once ('footer.php');?>
</body>
</html>