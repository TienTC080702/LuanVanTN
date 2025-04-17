<?php
// Nên đặt session_start() và ob_start() lên đầu tiên tuyệt đối
if(!isset($_SESSION)) {
    session_start();
    ob_start(); // Bắt đầu bộ đệm đầu ra
}

include ("../connection/connect_database.php");

// === Lấy thêm GiaKhuyenmai khi tải sản phẩm ===
$idSP = isset($_GET['idSP']) ? (int)$_GET['idSP'] : 0;

// !!! CẢNH BÁO: Tải tất cả sản phẩm là không hiệu quả !!!
// Tốt hơn là chỉ tải các sản phẩm có trong giỏ hàng hoặc sản phẩm vừa thêm
$product = array();
// !!! NÊN DÙNG PREPARED STATEMENT !!!
// Thêm điều kiện WHERE AnHien = 1 nếu có cột đó để chỉ lấy sản phẩm đang hiển thị
$sl_sanpham = "SELECT idSP, TenSP, GiaBan, GiaKhuyenmai, urlHinh, idNH, SoLuongTonKho FROM sanpham"; // Đã thêm GiaKhuyenmai
$rs_sanpham = mysqli_query($conn,$sl_sanpham);

if($rs_sanpham) {
    while ($r= $rs_sanpham->fetch_assoc())
    {
        // Bỏ qua sản phẩm có idSP = 1 (nếu cần)
        // if($r['idSP'] == 1) continue;

        // Lấy tên nhãn hiệu
        $tennh="Chưa xác định";
        // !!! NÊN DÙNG PREPARED STATEMENT !!!
        $sl_nh = "select TenNH from nhanhieu WHERE idNH=". (int)$r['idNH'];
        $rs_nh = mysqli_query($conn,$sl_nh);
        if ($rs_nh && $r_tennh = $rs_nh->fetch_assoc()){
            $tennh = $r_tennh['TenNH'];
            mysqli_free_result($rs_nh);
        }

        $product[] = array(
            "idSP" => $r['idSP'], "TenSP" => $r['TenSP'], "GiaBan" => $r['GiaBan'],
            "GiaKhuyenmai" => $r['GiaKhuyenmai'], // <<< ĐÃ THÊM CỘT NÀY
            "urlHinh" => $r['urlHinh'], "TenNH" => $tennh, "SoLTon" => $r['SoLuongTonKho']
        );
    }
     mysqli_free_result($rs_sanpham);
} else {
    error_log("Failed to fetch products for cart: " . mysqli_error($conn));
    // Có thể hiển thị thông báo lỗi thân thiện hơn cho người dùng
}

// Tạo mảng sản phẩm mới với key là idSP để dễ truy cập
$newproduct = array();
foreach ($product as $val){
    $newproduct[$val['idSP']] = $val;
}

// === Logic thêm vào giỏ hàng ===
if ($idSP != 0 && isset($newproduct[$idSP])) { // Đã bỏ điều kiện $idSP != 1 nếu bạn muốn cho phép SP có ID=1 vào giỏ
    $product_to_add = $newproduct[$idSP];
    // Chỉ thêm nếu còn hàng
    if ($product_to_add['SoLTon'] > 0) {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) { // Khởi tạo nếu chưa có hoặc không phải mảng
            $_SESSION['cart'] = [];
        }

        if (!isset($_SESSION['cart'][$idSP])) { // Sản phẩm chưa có trong giỏ
            // Thêm giá khuyến mãi vào session cart item nếu có
            $_SESSION['cart'][$idSP] = [
                'idSP' => $product_to_add['idSP'],
                'TenSP' => $product_to_add['TenSP'],
                'GiaBan' => $product_to_add['GiaBan'],
                'GiaKhuyenmai' => $product_to_add['GiaKhuyenmai'], // <<< THÊM VÀO ĐÂY
                'urlHinh' => $product_to_add['urlHinh'],
                'TenNH' => $product_to_add['TenNH'],
                'SoLTon' => $product_to_add['SoLTon'],
                'qty' => 1 // Số lượng ban đầu là 1
            ];
        } else { // Sản phẩm đã có, tăng số lượng nếu còn đủ tồn kho
            if ($_SESSION['cart'][$idSP]['qty'] < $product_to_add['SoLTon']) {
                $_SESSION['cart'][$idSP]['qty'] += 1;
                 // Cập nhật lại số lượng tồn kho trong session nếu cần (tùy logic)
                // $_SESSION['cart'][$idSP]['SoLTon'] = $product_to_add['SoLTon'];
            } else {
                // Thông báo không đủ hàng hoặc không làm gì cả
                 $_SESSION['cart_message'] = "Sản phẩm '" . htmlspecialchars($product_to_add['TenSP']) . "' không đủ số lượng tồn kho.";
            }
        }

        // Chuyển hướng về trang giỏ hàng không có idSP để tránh thêm lại khi refresh
        header("Location: GioHang.php");
        exit();

    } else {
         $_SESSION['cart_message'] = "Sản phẩm '" . htmlspecialchars($product_to_add['TenSP']) . "' đã hết hàng.";
          // Chuyển hướng về trang giỏ hàng không có idSP
        header("Location: GioHang.php");
        exit();
    }
}

// Đếm số loại sản phẩm trong giỏ
$so = 0;
if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $so = count($_SESSION['cart']);
}

// Hiển thị và xóa thông báo (nếu có)
$cart_message = '';
if (isset($_SESSION['cart_message'])) {
    $cart_message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php include_once ("header.php");?>
    <title>Giỏ hàng</title>
    <?php include_once ("header1.php");?>
     <meta charset="UTF-8">
    <style>
         /* --- CSS CHO KHUNG BAO --- */
        body {
             background-color: #f0f0f0; /* Màu nền xám nhạt cho bên ngoài khung */
             padding-top: 20px; /* Khoảng cách trên cùng */
             padding-bottom: 20px; /* Khoảng cách dưới cùng */
        }
        .page-frame {
            background-color: #fff0f5; /* Màu hồng nhạt (LavenderBlush) */
            padding: 20px; /* Khoảng cách bên trong khung */
            border-radius: 8px; /* Bo tròn góc */
            max-width: 1200px; /* Giới hạn chiều rộng tối đa của khung */
            margin: 0 auto; /* Căn giữa khung */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Đổ bóng nhẹ */
        }
        /* --- KẾT THÚC CSS KHUNG BAO --- */

        .glyphicon.glyphicon-pencil{ color: #1fb3f6;}
        .ngang{ background-color: #332c68; margin-top: 2%; margin-bottom: 2%; height: 1px; }
        .price-original { color: #999; text-decoration: line-through; margin-left: 10px; font-size: 0.9em; }
        .price-discounted { color: red; font-weight: bold; font-size: 1.1em;}
        .price-normal { font-weight: bold; }
        .line-total { color: firebrick; font-weight: bold; }
        .action-buttons .btn { margin: 2px; /* Thêm khoảng cách nhỏ giữa các nút */ }
        .table > tbody > tr > td { vertical-align: middle; /* Căn giữa nội dung trong ô theo chiều dọc */ }
        .quantity-input { width: 60px !important; text-align: center; display: inline-block; margin: 0 5px; }
        .quantity-controls { display: flex; justify-content: center; align-items: center; }
        .cart-message { margin-top: 15px; }
    </style>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-theme.min.css">
</head>
<body>
    <div class="page-frame">

        <?php include_once ("header2.php");?>
        <div class="container content">
            <h2 style="text-align: center; margin-bottom: 10px;"><p><b>GIỎ HÀNG CỦA TÔI</b></p></h2>
            <p style="text-align: center; margin-bottom: 20px;"><?php echo "Có ".$so." "."loại sản phẩm trong giỏ";?></p>

             <?php if (!empty($cart_message)): ?>
                 <div class="alert alert-warning text-center cart-message">
                     <?php echo htmlspecialchars($cart_message); ?>
                 </div>
             <?php endif; ?>


            <div class="row">
                <div class="col-md-10 col-md-offset-1"> <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr style="text-align: center; background-color: #f2f2f2;">
                                <th style="width: 10%; text-align: center;">Ảnh</th>
                                <th style="width: 25%; text-align: center;">Tên sản phẩm</th>
                                <th style="width: 15%; text-align: center;">Đơn giá</th>
                                <th style="width: 15%; text-align: center;">Số lượng</th>
                                <th style="width: 15%; text-align: center;">Thành tiền</th>
                                <th style="width: 20%; text-align: center;">Thao tác</th> </tr>
                        </thead>
                        <tbody>
                        <?php
                        $tongtien = 0; // Reset tổng tiền
                        if($so == 0) {
                            echo"<tr><td colspan='6' align='center'><h4 style='color:red;'>Chưa có hàng nào trong giỏ của bạn. Mời bạn lựa chọn sản phẩm!</h4><a href='index.php?index=1' class='btn btn-primary'>Mua sắm ngay</a></td></tr>";
                        } else {
                            foreach ($_SESSION['cart'] as $idSP_loop => $list) {
                                // Kiểm tra xem thông tin sản phẩm có đầy đủ không
                                if (!isset($list['GiaBan']) || !isset($list['qty'])) {
                                    // Sản phẩm lỗi, có thể xóa khỏi giỏ hoặc hiển thị thông báo
                                    unset($_SESSION['cart'][$idSP_loop]);
                                    continue; // Bỏ qua sản phẩm lỗi này
                                }

                                $gia_hien_tai = $list['GiaBan']; // Giá gốc
                                $gia_goc_display = $list['GiaBan']; // Giá gốc để hiển thị nếu có KM
                                $co_khuyen_mai = false;

                                // Kiểm tra và áp dụng giá khuyến mãi
                                if (isset($list['GiaKhuyenmai']) && is_numeric($list['GiaKhuyenmai']) && $list['GiaKhuyenmai'] > 0 && $list['GiaKhuyenmai'] < $list['GiaBan']) {
                                    $gia_hien_tai = $list['GiaKhuyenmai']; // Giá áp dụng là giá KM
                                    $co_khuyen_mai = true;
                                }

                                $so_luong = (int)$list['qty'];
                                $so_luong_ton = isset($list['SoLTon']) ? (int)$list['SoLTon'] : 0; // Lấy số lượng tồn
                                $thanh_tien_dong = $so_luong * $gia_hien_tai;
                                $tongtien += $thanh_tien_dong;
                        ?>
                                <tr style="background-color:#FFF;">
                                    <td style="text-align: center;">
                                        <a href="MoTa.php?idSP=<?php echo $list['idSP'];?>">
                                            <img height="80" width="auto" src="../images/<?php echo htmlspecialchars($list['urlHinh'] ?? 'no_image_available.png'); ?>" alt="<?php echo htmlspecialchars($list['TenSP']); ?>">
                                        </a>
                                    </td>
                                    <td>
                                        <a href="MoTa.php?idSP=<?php echo $list['idSP'];?>"><?php echo htmlspecialchars($list['TenSP']);?></a><br>
                                        <small><i><?php echo htmlspecialchars($list['TenNH'] ?? 'N/A');?></i></small><br>
                                        <?php
                                            if ($so_luong_ton <= 10 && $so_luong_ton > 0)
                                                echo "<small style='color: orange;'>Chỉ còn ".$so_luong_ton." sản phẩm</small>";
                                            elseif ($so_luong_ton > 0)
                                                echo "<small style='color: green;'><span class='glyphicon glyphicon-ok'></span> Còn hàng</small>";
                                            else
                                                echo "<small style='color: red;'>Hết hàng</small>";
                                        ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <?php if ($co_khuyen_mai): ?>
                                            <span class="price-discounted"><?php echo number_format($gia_hien_tai, 0)." VNĐ";?></span><br>
                                            <span class="price-original"><?php echo number_format($gia_goc_display, 0)." VNĐ";?></span>
                                        <?php else: ?>
                                            <span class="price-normal"><?php echo number_format($gia_hien_tai, 0)." VNĐ";?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="quantity-controls">
                                             <?php // Nút giảm chỉ hiện khi SL > 1 ?>
                                             <a href="Giam_SL.php?idSP=<?php echo $list['idSP'];?>" class="btn btn-default btn-xs <?php echo ($so_luong <= 1) ? 'disabled' : ''; ?>" title="Giảm số lượng">-</a>

                                             <input type="text" value="<?php echo $so_luong;?>" class="form-control input-sm quantity-input" readonly />

                                             <?php // Nút tăng chỉ hiện khi SL < SL tồn và còn hàng ?>
                                             <a href="Tang_SL.php?idSP=<?php echo $list['idSP'];?>" class="btn btn-default btn-xs <?php echo ($so_luong >= $so_luong_ton || $so_luong_ton <= 0) ? 'disabled' : ''; ?>" title="Tăng số lượng">+</a>
                                        </div>
                                         <?php if ($so_luong >= $so_luong_ton && $so_luong_ton > 0) {
                                             echo "<small class='text-danger text-center' style='display:block; font-size:0.9em;'>Tối đa</small>";
                                         }?>
                                    </td>
                                    <td style="text-align: right;">
                                        <span class="line-total"><?php echo number_format($thanh_tien_dong, 0)." VNĐ";?></span>
                                    </td>
                                    <td style="text-align: center;" class="action-buttons"> <a onclick="return confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?');" href="Xoa_Gio_Hang.php?idSP=<?php echo $list['idSP'];?>" class="btn btn-danger btn-sm" title="Xóa sản phẩm">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                         <a href="ThanhToan.php?buy_now=1&idSP=<?php echo $list['idSP']; ?>" class="btn btn-success btn-sm" title="Thanh toán ngay sản phẩm này">
                                            <span class="glyphicon glyphicon-share-alt"></span> TT Ngay
                                        </a>
                                        </td>
                                </tr>
                        <?php
                            } // end foreach
                        } // end else ($so > 0)
                        ?>
                        </tbody>
                         <?php if($so > 0): ?>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right;"><strong>Tổng tiền tạm tính (<?php echo $so; ?> loại sản phẩm):</strong></td>
                                <td colspan="2" style="text-align: right;"><strong style="color: firebrick; font-size: 1.2em;"><?php echo number_format($tongtien, 0);?> VNĐ</strong></td>
                            </tr>
                        </tfoot>
                         <?php endif; ?>
                    </table>
                </div>

                 <?php if($so > 0): // Chỉ hiển thị nút nếu có hàng ?>
                 <div class="row" style="margin-top: 20px;">
                     <div class="col-sm-6 col-xs-12" style="margin-bottom: 10px;"> <a href='index.php?index=1' class="btn btn-default btn-block"><span class="glyphicon glyphicon-chevron-left"></span> Tiếp tục mua hàng</a>
                     </div>
                     <div class="col-sm-6 col-xs-12 text-right">
                          <a href='ThanhToan.php' class="btn btn-success btn-block"><span class="glyphicon glyphicon-check"></span> Thanh toán toàn bộ giỏ hàng</a>
                     </div>
                 </div>
                <?php endif; ?>

            </div>
        </div>

        </div> <?php include_once ("footer.php");?>

        <div class="modal fade" id="myModal" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                     <div class="modal-header">
                         <h4 class="modal-title text-center"><span class="glyphicon glyphicon-ok" style="color: #00d247;"></span> Thêm vào giỏ hàng thành công!</h4>
                    </div>
                    <div class="modal-body">
                        <?php
                        // --- Logic hiển thị sản phẩm vừa thêm trong modal ---
                        // Cần đảm bảo biến $added_item được lấy đúng
                        $added_item = null;
                        $added_item_display_price = 0;
                        // Kiểm tra xem có ID sản phẩm vừa thêm trong session hoặc biến nào đó không?
                        // Đoạn code gốc dùng $idSP nhưng $idSP có thể là 0 khi chỉ xem giỏ hàng.
                        // Cách tốt hơn là lưu id sản phẩm vừa thêm vào session tạm thời
                        $last_added_id = isset($_SESSION['last_added_id']) ? (int)$_SESSION['last_added_id'] : 0;

                        if ($last_added_id > 0 && isset($newproduct[$last_added_id])) {
                             $added_item = $newproduct[$last_added_id];
                             $added_item_display_price = $added_item['GiaBan'];
                             if (isset($added_item['GiaKhuyenmai']) && is_numeric($added_item['GiaKhuyenmai']) && $added_item['GiaKhuyenmai'] > 0 && $added_item['GiaKhuyenmai'] < $added_item['GiaBan']) {
                                 $added_item_display_price = $added_item['GiaKhuyenmai'];
                             }
                        ?>
                             <div class="row">
                                 <div class="col-sm-3 text-center">
                                     <img height="100" width="auto" src="../images/<?php echo htmlspecialchars($added_item['urlHinh'] ?? 'no_image_available.png'); ?>">
                                 </div>
                                  <div class="col-sm-9">
                                     <h4><?php echo htmlspecialchars($added_item['TenSP']);?></h4>
                                     <p>Giá: <strong style="color: red;"><?php echo number_format($added_item_display_price, 0);?> VNĐ</strong></p>
                                     <p>Số lượng trong giỏ: <?php echo isset($_SESSION['cart'][$last_added_id]['qty']) ? $_SESSION['cart'][$last_added_id]['qty'] : '?'; ?></p>
                                     <p>Nhãn hiệu: <?php echo htmlspecialchars($added_item['TenNH']);?></p>
                                  </div>
                             </div>
                             <hr>
                              <div class="row">
                                  <div class="col-xs-6">
                                      <strong>Giỏ hàng của bạn hiện có <?php echo $so;?> loại sản phẩm</strong>
                                  </div>
                                   <div class="col-xs-6 text-right">
                                      <strong>Tổng tiền tạm tính: <span style="color: firebrick;"><?php echo number_format($tongtien);?> VNĐ</span></strong>
                                  </div>
                              </div>
                        <?php
                            // Xóa ID sản phẩm vừa thêm khỏi session sau khi hiển thị
                            unset($_SESSION['last_added_id']);
                         } else {
                            // Ẩn modal nếu không có gì để hiển thị (ví dụ khi chỉ vào xem giỏ)
                            echo "<script> $(document).ready(function(){ $('#myModal').modal('hide'); }); </script>";
                         }
                        ?>
                    </div>
                    <div class="modal-footer">
                         <button type="button" class="btn btn-default" data-dismiss="modal">Tiếp tục mua hàng</button>
                         <a href='GioHang.php' class="btn btn-success">Xem chi tiết giỏ hàng</a>
                    </div>
                </div>
            </div>
        </div>
        </div> <script src="../js/jquery-3.1.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>

    <?php
    // === Sửa logic hiển thị modal ===
    // Chỉ hiển thị modal nếu session 'show_cart_modal' được đặt
    if(isset($_SESSION['show_cart_modal']) && $_SESSION['show_cart_modal'] === true) {
        echo " <script>
                 $(window).on('load',function(){
                     // Kiểm tra lại nội dung modal trước khi hiển thị
                     if ($('#myModal .modal-body').children().length > 0) { // Chỉ show nếu có nội dung
                         $('#myModal').modal('show');
                     }
                 });
               </script>";
        // Xóa biến session sau khi sử dụng
        unset($_SESSION['show_cart_modal']);
        // Lưu ID sản phẩm vào session để modal lấy thông tin
        if ($idSP > 0) { $_SESSION['last_added_id'] = $idSP; }

    }
    // Xóa ID sản phẩm cuối cùng nếu không hiển thị modal
    // else { unset($_SESSION['last_added_id']); }


    ob_end_flush(); // Gửi nội dung từ bộ đệm ra trình duyệt
    ?>

</body>
</html>