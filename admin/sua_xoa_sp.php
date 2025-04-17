<?php
// ----- START DEBUGGING -----
// Bật hiển thị tất cả lỗi PHP - Xóa hoặc comment lại các dòng ini_set/error_reporting sau khi debug xong
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ----- END DEBUGGING -----

include_once("../connection/connect_database.php");

// --- Lấy dữ liệu sản phẩm cần sửa ---
if (!isset($_GET['idSP']) || !is_numeric($_GET['idSP'])) {
    echo "<script language='javascript'>alert('ID sản phẩm không hợp lệ!'); location.href='index_ds_sp.php';</script>";
    exit;
}
$idSP_edit = (int)$_GET['idSP'];

// Sử dụng Prepared Statement để lấy dữ liệu an toàn hơn
$sql_sanpham = "SELECT * FROM sanpham WHERE idSP = ?";
$stmt_sanpham = mysqli_prepare($conn, $sql_sanpham);

if (!$stmt_sanpham) {
    // Sử dụng die() hoặc log lỗi nghiêm trọng ở đây thay vì echo script nếu prepare thất bại hoàn toàn
    die("Lỗi nghiêm trọng: Không thể chuẩn bị câu lệnh lấy sản phẩm. Lỗi: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_sanpham, "i", $idSP_edit);

// Thực thi và kiểm tra lỗi execute
if (!mysqli_stmt_execute($stmt_sanpham)) {
    // Log lỗi hoặc hiển thị thông báo thân thiện, tránh die() nếu có thể
     die("Lỗi nghiêm trọng khi thực thi lấy sản phẩm: " . mysqli_stmt_error($stmt_sanpham));
}

$rs_sanpham = mysqli_stmt_get_result($stmt_sanpham);

if (!$rs_sanpham) {
     // Xử lý lỗi nếu không lấy được result set
    $error_get_result = mysqli_stmt_error($stmt_sanpham) ?: mysqli_error($conn); // Lấy lỗi từ statement hoặc connection
    echo "<script language='javascript'>alert('Lỗi khi lấy bộ kết quả sản phẩm: ' + " . json_encode($error_get_result ?: 'Không rõ lỗi') . "); location.href='index_ds_sp.php';</script>";
    mysqli_stmt_close($stmt_sanpham); // Đóng statement trước khi exit
    exit;
}

if (mysqli_num_rows($rs_sanpham) == 0) {
    echo "<script language='javascript'>alert('Không tìm thấy sản phẩm với ID này!'); location.href='index_ds_sp.php';</script>";
    mysqli_free_result($rs_sanpham); // Giải phóng kết quả
    mysqli_stmt_close($stmt_sanpham); // Đóng statement
    exit;
}


$r_sanpham = mysqli_fetch_assoc($rs_sanpham);
mysqli_free_result($rs_sanpham); // Giải phóng kết quả sau khi fetch
mysqli_stmt_close($stmt_sanpham);

$anhcu = $r_sanpham['urlHinh'] ?? ''; // Xử lý null

// --- Định nghĩa các lựa chọn cho Checkbox ---
$loai_da_options = ["Da dầu", "Da khô", "Da hỗn hợp", "Da thường", "Da nhạy cảm", "Mọi loại da"];
$van_de_da_options = ["Mụn", "Lỗ chân lông to", "Thâm mụn", "Da không đều màu", "Nám, tàn nhang", "Lão hóa", "Da khô, thiếu ẩm", "Da nhạy cảm, mẩn đỏ", "Kiềm dầu", "Làm sáng da", "Dưỡng ẩm", "Chống nắng"];

// --- Lấy giá trị đã lưu từ CSDL và chuyển thành mảng ---
$selected_loai_da = !empty($r_sanpham['loai_da_phu_hop']) ? array_map('trim', explode(',', $r_sanpham['loai_da_phu_hop'])) : [];
$selected_van_de_da = !empty($r_sanpham['van_de_da_giai_quyet']) ? array_map('trim', explode(',', $r_sanpham['van_de_da_giai_quyet'])) : [];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Sửa sản phẩm - ID: <?php echo $idSP_edit; ?></title>
    <?php include_once('header2.php'); ?>
    <script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
    <style>
        /* CSS giữ nguyên */
        h3.page-title { color: #1a0fb1; text-align: center; margin-bottom: 30px; font-family: 'Arial', sans-serif; }
        .custom-form-container { max-width: 950px; margin: auto; background-color: #f9f9f9; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); border: 1px solid #ddd; }
        .current-image-preview { max-width: 150px; height: auto; margin-top: 10px; display: block; }
        .form-label strong { font-weight: 600; }
        .button-group { text-align: center; margin-top: 25px; }
        .button-group .btn { margin: 0 5px; min-width: 100px; }
        .form-check-inline { margin-right: 1rem; margin-bottom: 0.5rem; }
        .checkbox-group-label { margin-bottom: 0.5rem; display: block; }
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <h3 class="page-title">SỬA THÔNG TIN SẢN PHẨM</h3>

    <div class="custom-form-container">
        <form method="post" action="sua_xoa_sp.php?idSP=<?php echo $idSP_edit; ?>" name="SuaSPForm" enctype="multipart/form-data">
            <input type="hidden" name="idSP" value="<?php echo $idSP_edit; ?>">
            <input type="hidden" name="anhcu" value="<?php echo htmlspecialchars($anhcu); ?>">

            <div class="row mb-3">
                <label for="idSP_display" class="col-sm-3 col-form-label"><strong>ID sản phẩm</strong></label>
                <div class="col-sm-9"><input type="text" id="idSP_display" class="form-control" value="<?php echo $r_sanpham['idSP']; ?>" readonly></div>
            </div>
            <div class="row mb-3">
                <label for="TenSP" class="col-sm-3 col-form-label"><strong>Tên sản phẩm</strong></label>
                <div class="col-sm-9"><input type="text" name="TenSP" id="TenSP" class="form-control" value="<?php echo htmlspecialchars($r_sanpham['TenSP'] ?? ''); ?>" required></div>
            </div>
             <div class="row mb-3">
                <label for="idNH" class="col-sm-3 col-form-label"><strong>Nhãn hiệu</strong></label>
                <div class="col-sm-9">
                    <?php $sl_nhanhieu = "select idNH, TenNH from nhanhieu ORDER BY TenNH ASC"; $rs_nhanhieu = mysqli_query($conn, $sl_nhanhieu); if (!$rs_nhanhieu) { echo "<div class='alert alert-danger'>Lỗi truy vấn nhãn hiệu: " . mysqli_error($conn) . "</div>"; } else { ?> <select name="idNH" id="idNH" class="form-select" required> <option value="">-- Chọn Nhãn hiệu --</option> <?php while ($r_nh = mysqli_fetch_assoc($rs_nhanhieu)) { $selected = ($r_nh['idNH'] == ($r_sanpham['idNH'] ?? null)) ? "selected" : ""; echo "<option value='" . $r_nh["idNH"] . "' $selected>" . htmlspecialchars($r_nh['TenNH']) . "</option>"; } mysqli_free_result($rs_nhanhieu); ?> </select> <?php } ?>
                </div>
            </div>
             <div class="row mb-3">
                 <label for="idL" class="col-sm-3 col-form-label"><strong>Loại</strong></label>
                 <div class="col-sm-9">
                      <?php $sl_l = "select idL, TenL from loaisp ORDER BY TenL ASC"; $rs_l = mysqli_query($conn, $sl_l); if (!$rs_l) { echo "<div class='alert alert-danger'>Lỗi truy vấn loại sản phẩm: " . mysqli_error($conn) . "</div>"; } else { ?> <select name="idL" id="idL" class="form-select" required> <option value="">-- Chọn Loại --</option> <?php while ($row_l = mysqli_fetch_assoc($rs_l)) { $selected = ($row_l['idL'] == ($r_sanpham['idL'] ?? null)) ? "selected" : ""; echo "<option value='" . $row_l["idL"] . "' $selected>" . htmlspecialchars($row_l['TenL']) . "</option>"; } mysqli_free_result($rs_l); ?> </select> <?php } ?>
                 </div>
             </div>
             <div class="row mb-3">
                <label for="GiaBan" class="col-sm-3 col-form-label"><strong>Giá bán</strong></label>
                <div class="col-sm-9"><input type="number" id="GiaBan" name="GiaBan" class="form-control" value="<?php echo htmlspecialchars($r_sanpham['GiaBan'] ?? ''); ?>" min="0" step="any" required></div>
            </div>
            <div class="row mb-3">
                <label for="GiaKhuyenmai" class="col-sm-3 col-form-label"><strong>Giá khuyến mãi</strong></label>
                <div class="col-sm-9"><input type="number" id="GiaKhuyenmai" name="GiaKhuyenmai" class="form-control" value="<?php echo htmlspecialchars($r_sanpham['GiaKhuyenmai'] ?? ''); ?>" min="0" step="any"></div>
            </div>
             <div class="row mb-3">
                 <label for="SoLuongTonKho" class="col-sm-3 col-form-label"><strong>Số lượng trong kho</strong></label>
                 <div class="col-sm-9"><input type="number" id="SoLuongTonKho" name="SoLuongTonKho" class="form-control" value="<?php echo htmlspecialchars($r_sanpham['SoLuongTonKho'] ?? ''); ?>" min="0" required></div>
             </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label checkbox-group-label"><strong>Loại da phù hợp</strong> <br><small>(Chọn một hoặc nhiều)</small></label>
                <div class="col-sm-9 pt-2">
                    <?php foreach ($loai_da_options as $option): $option_id = 'loai_da_' . preg_replace('/[^a-zA-Z0-9_]/', '', str_replace([' ', ','], '_', $option)); $is_checked = in_array($option, $selected_loai_da); ?>
                    <div class="form-check form-check-inline"> <input class="form-check-input" type="checkbox" name="loai_da[]" value="<?php echo htmlspecialchars($option); ?>" id="<?php echo $option_id; ?>" <?php echo $is_checked ? 'checked' : ''; ?>> <label class="form-check-label" for="<?php echo $option_id; ?>"><?php echo htmlspecialchars($option); ?></label> </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="row mb-3">
                 <label class="col-sm-3 col-form-label checkbox-group-label"><strong>Vấn đề da giải quyết</strong> <br><small>(Chọn một hoặc nhiều)</small></label>
                 <div class="col-sm-9 pt-2">
                     <?php foreach ($van_de_da_options as $option): $option_id = 'van_de_da_' . preg_replace('/[^a-zA-Z0-9_]/', '', str_replace([' ', ','], '_', $option)); $is_checked = in_array($option, $selected_van_de_da); ?>
                     <div class="form-check form-check-inline"> <input class="form-check-input" type="checkbox" name="van_de_da[]" value="<?php echo htmlspecialchars($option); ?>" id="<?php echo $option_id; ?>" <?php echo $is_checked ? 'checked' : ''; ?>> <label class="form-check-label" for="<?php echo $option_id; ?>"><?php echo htmlspecialchars($option); ?></label> </div>
                     <?php endforeach; ?>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><strong>Hình đại diện</strong></label>
                <div class="col-sm-9">
                    <div> <label>Hình hiện tại:</label><br> <?php if (!empty($anhcu) && file_exists("../images/" . $anhcu)) { ?> <img src="../images/<?php echo htmlspecialchars($anhcu); ?>" alt="Hình ảnh sản phẩm" class="img-thumbnail current-image-preview mb-2"> <?php } else { echo "<span class='text-muted'>Chưa có hình hoặc file không tồn tại.</span><br>"; } ?> <small class='text-muted'>Tên file: <?php echo htmlspecialchars($anhcu); ?></small> </div>
                    <div class="mt-2"> <label for="file" class="form-label">Thay đổi ảnh:</label> <input type="hidden" name="MAX_FILE_SIZE" value="5242880"> <input type="file" name="file" id="file" class="form-control" accept="image/*"> <small class="form-text text-muted">Để trống nếu không muốn thay đổi.</small> </div>
                </div>
            </div>
             <div class="row mb-3">
                 <label for="SoLanXem" class="col-sm-3 col-form-label"><strong>Số lần xem</strong></label>
                 <div class="col-sm-9"><input type="number" id="SoLanXem" name="SoLanXem" class="form-control" value="<?php echo htmlspecialchars($r_sanpham['SoLanXem'] ?? '0'); ?>" min="0"></div>
             </div>
             <div class="row mb-3">
                 <label for="SoLanMua" class="col-sm-3 col-form-label"><strong>Số lần mua</strong></label>
                 <div class="col-sm-9"><input type="number" id="SoLanMua" name="SoLanMua" class="form-control" value="<?php echo htmlspecialchars($r_sanpham['SoLanMua'] ?? '0'); ?>" min="0"></div>
             </div>
             <div class="row mb-3">
                 <label for="GhiChu" class="col-sm-3 col-form-label"><strong>Ghi chú</strong></label>
                 <div class="col-sm-9"><input type="text" id="GhiChu" name="GhiChu" class="form-control" value="<?php echo htmlspecialchars($r_sanpham['GhiChu'] ?? ''); ?>"></div>
             </div>
            <div class="row mb-3">
                <label for="MoTa" class="col-sm-3 col-form-label"><strong>Mô tả</strong></label>
                <div class="col-sm-9"> <textarea id="MoTa" name="MoTa" class="form-control"><?php echo htmlspecialchars($r_sanpham['MoTa'] ?? ''); ?></textarea> <script type="text/javascript"> CKEDITOR.replace('MoTa'); </script> </div>
            </div>
             <div class="row mb-3">
                 <label for="NgayCapNhat_display" class="col-sm-3 col-form-label"><strong>Ngày cập nhật cuối</strong></label>
                 <div class="col-sm-9"><input type="text" id="NgayCapNhat_display" class="form-control" value="<?php echo $r_sanpham['NgayCapNhat'] ?? ''; ?>" readonly></div>
             </div>
            <div class="row mb-3">
                <label for="AnHien" class="col-sm-3 col-form-label"><strong>Trạng thái</strong></label>
                <div class="col-sm-9"> <select id="AnHien" name="AnHien" class="form-select"> <option value="1" <?php if (($r_sanpham['AnHien'] ?? 1) == 1) echo "selected"; ?>><strong>Hiện</strong></option> <option value="0" <?php if (($r_sanpham['AnHien'] ?? 1) == 0) echo "selected"; ?>><strong>Ẩn</strong></option> </select> </div>
            </div>

            <div class="button-group">
                <button name="update" type="submit" class="btn btn-primary">Cập nhật</button>
                <button name="delete" type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Thao tác này không thể hoàn tác!');">Xóa</button>
                <a href="index_ds_sp.php" class="btn btn-secondary">Trở về</a>
            </div>

        </form>
    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>

<?php
// --- Xử lý PHP cho UPDATE và DELETE ---

// Function to safely delete files, suppressing warnings
function safeUnlink($filepath) {
    if (!empty($filepath) && file_exists($filepath) && is_file($filepath)) {
        @unlink($filepath);
    }
}

// 1. Xử lý DELETE
if (isset($_POST['delete'])) {
    $idSP_delete = isset($_POST['idSP']) ? (int)$_POST['idSP'] : 0;
    $anh_can_xoa = isset($_POST['anhcu']) ? $_POST['anhcu'] : '';

    // --- Validate ID before proceeding ---
    if ($idSP_delete <= 0) {
         echo "<script language='javascript'>alert('ID sản phẩm không hợp lệ để xóa!');</script>";
         // Optionally redirect back
         // echo "<script language='javascript'>location.href='index_ds_sp.php';</script>";
         exit;
    }
     if ($idSP_delete == 1) { // Prevent deleting default product ID 1
        echo "<script language='javascript'>alert('Không thể xóa sản phẩm mặc định (ID=1).');</script>";
        exit;
     }

    // --- Kiểm tra ràng buộc ---
    $dem_tensp_dh = 0;
    $sql_ktra_dh = "SELECT COUNT(*) as count FROM donhangchitiet WHERE idSP = ?";
    $stmt_ktra_dh = mysqli_prepare($conn, $sql_ktra_dh);
    if($stmt_ktra_dh) {
        mysqli_stmt_bind_param($stmt_ktra_dh, "i", $idSP_delete);
        if (mysqli_stmt_execute($stmt_ktra_dh)) {
            $rs_ktra_dh = mysqli_stmt_get_result($stmt_ktra_dh);
            if($rs_ktra_dh) {
                $r_count = mysqli_fetch_assoc($rs_ktra_dh);
                $dem_tensp_dh = $r_count['count'];
                mysqli_free_result($rs_ktra_dh);
            } else {
                 echo "<script language='JavaScript'> alert('Lỗi lấy kết quả kiểm tra đơn hàng: ' + " . json_encode(mysqli_stmt_error($stmt_ktra_dh)) . ");</script>";
                 $dem_tensp_dh = 999; // Assume constraint exists on error
            }
        } else {
            echo "<script language='JavaScript'> alert('Lỗi thực thi kiểm tra đơn hàng: ' + " . json_encode(mysqli_stmt_error($stmt_ktra_dh)) . ");</script>";
            $dem_tensp_dh = 999; // Assume constraint exists on error
        }
        mysqli_stmt_close($stmt_ktra_dh);
    } else {
        echo "<script language='JavaScript'> alert('Lỗi chuẩn bị kiểm tra ràng buộc đơn hàng: ' + " . json_encode(mysqli_error($conn)) . ");</script>";
        $dem_tensp_dh = 999; // Assume constraint exists on error
    }

    // --- Proceed with deletion if no constraints ---
    if ($dem_tensp_dh == 0) {
        $sql_delete_sp = "DELETE FROM sanpham WHERE idSP = ?";
        $stmt_delete_sp = mysqli_prepare($conn, $sql_delete_sp);

        if (!$stmt_delete_sp) {
             echo "<script language='JavaScript'> alert('Lỗi chuẩn bị câu lệnh xóa SP: ' + " . json_encode(mysqli_error($conn)) . ");</script>";
        } else {
            mysqli_stmt_bind_param($stmt_delete_sp, "i", $idSP_delete);

            if (mysqli_stmt_execute($stmt_delete_sp)) {
                 $affected_rows_delete = mysqli_stmt_affected_rows($stmt_delete_sp);
                 mysqli_stmt_close($stmt_delete_sp);

                 if ($affected_rows_delete > 0) {
                     // Xóa ảnh đại diện cũ
                     safeUnlink('../images/' . $anh_can_xoa);

                     // Xóa các ảnh mô tả liên quan
                     $sql_get_hinhmt = "SELECT urlHinh FROM sanpham_hinh WHERE idSP = ?";
                     $stmt_get_hinhmt = mysqli_prepare($conn, $sql_get_hinhmt);
                     if($stmt_get_hinhmt){
                         mysqli_stmt_bind_param($stmt_get_hinhmt, "i", $idSP_delete);
                         if (mysqli_stmt_execute($stmt_get_hinhmt)) {
                             $rs_hinhmt = mysqli_stmt_get_result($stmt_get_hinhmt);
                             if($rs_hinhmt){
                                 while($row_hinhmt = mysqli_fetch_assoc($rs_hinhmt)){
                                     safeUnlink('../images/' . $row_hinhmt['urlHinh']);
                                 }
                                 mysqli_free_result($rs_hinhmt);
                             }
                             mysqli_stmt_close($stmt_get_hinhmt);

                             // Xóa bản ghi trong sanpham_hinh
                             $sql_delete_hinhmt = "DELETE FROM sanpham_hinh WHERE idSP = ?";
                             $stmt_delete_hinhmt = mysqli_prepare($conn, $sql_delete_hinhmt);
                             if($stmt_delete_hinhmt){
                                 mysqli_stmt_bind_param($stmt_delete_hinhmt, "i", $idSP_delete);
                                 mysqli_stmt_execute($stmt_delete_hinhmt); // Execute even if no images found
                                 mysqli_stmt_close($stmt_delete_hinhmt);
                             }
                         } else {
                             // Log or alert error executing select sanpham_hinh
                         }
                     }

                     // Cập nhật bài viết liên quan (Set to default product ID 1)
                     $sql_up_bv = "UPDATE baiviet SET idSP = 1 WHERE idSP = ?";
                     $stmt_up_bv = mysqli_prepare($conn, $sql_up_bv);
                     if($stmt_up_bv){
                         mysqli_stmt_bind_param($stmt_up_bv, "i", $idSP_delete);
                         mysqli_stmt_execute($stmt_up_bv); // Execute update
                         mysqli_stmt_close($stmt_up_bv);
                     }

                     echo "<script language='javascript'>alert('Xóa sản phẩm thành công!'); location.href='index_ds_sp.php';</script>";
                     exit;
                 } else {
                     // This might happen if the product was already deleted by another process
                     echo "<script language='javascript'>alert('Xóa không thành công: Không tìm thấy sản phẩm hoặc không có gì để xóa.');</script>";
                 }

            } else {
                 $error_msg_sql = mysqli_stmt_error($stmt_delete_sp);
                 echo "<script language='JavaScript'> alert('Lỗi: Xóa sản phẩm không thành công! Lỗi SQL: ' + " . json_encode($error_msg_sql) . ");</script>";
                 if(isset($stmt_delete_sp)) mysqli_stmt_close($stmt_delete_sp); // Close on error too
            }
        }
    } else if ($dem_tensp_dh > 0) { // Constraint violation
        $error_msg = "Không thể xóa sản phẩm này! Sản phẩm đã tồn tại trong đơn hàng.";
        echo "<script language='javascript'>alert(" . json_encode($error_msg) . ");</script>";
    } else { // Error during constraint check
         // Error alert was already shown during check
    }
}


// 2. Xử lý UPDATE
if (isset($_POST['update'])) {
    // Bỏ comment các dòng echo dưới đây nếu cần debug chi tiết hơn
    // echo "<hr><strong>--- DEBUGGING UPDATE PROCESS ---</strong><br>";

    // Lấy dữ liệu từ form, kiểm tra isset và xử lý giá trị rỗng/null
    $idSP_update = isset($_POST['idSP']) ? (int)$_POST['idSP'] : 0;
    $TenSP_new = isset($_POST['TenSP']) ? trim($_POST['TenSP']) : '';
    $idNH_new = isset($_POST['idNH']) && $_POST['idNH'] !== '' ? (int)$_POST['idNH'] : null;
    $idL_new = isset($_POST['idL']) && $_POST['idL'] !== '' ? (int)$_POST['idL'] : null;
    $GiaBan_new = isset($_POST['GiaBan']) && $_POST['GiaBan'] !== '' ? (float)$_POST['GiaBan'] : 0.0;
    $GiaKhuyenmai_new = isset($_POST['GiaKhuyenmai']) && $_POST['GiaKhuyenmai'] !== '' ? (float)$_POST['GiaKhuyenmai'] : 0.0;
    $SoLuongTonKho_new = isset($_POST['SoLuongTonKho']) && $_POST['SoLuongTonKho'] !== '' ? (int)$_POST['SoLuongTonKho'] : 0;
    $SoLanXem_new = isset($_POST['SoLanXem']) && $_POST['SoLanXem'] !== '' ? (int)$_POST['SoLanXem'] : 0;
    $SoLanMua_new = isset($_POST['SoLanMua']) && $_POST['SoLanMua'] !== '' ? (int)$_POST['SoLanMua'] : 0;
    $GhiChu_new = isset($_POST['GhiChu']) ? trim($_POST['GhiChu']) : '';
    $MoTa_new = isset($_POST['MoTa']) ? trim($_POST['MoTa']) : ''; // CKEditor sends this
    $AnHien_new = isset($_POST['AnHien']) ? (int)$_POST['AnHien'] : 1;
    $ngaycapnhat_new = date('Y-m-d H:i:s');
    $anhcu_submit = isset($_POST['anhcu']) ? $_POST['anhcu'] : '';

    // Validate essential data
    if ($idSP_update <= 0) {
        echo "<script language='javascript'>alert('ID sản phẩm không hợp lệ để cập nhật!');</script>";
        exit;
    }
    if (empty($TenSP_new)) {
         echo "<script language='javascript'>alert('Tên sản phẩm không được để trống!');</script>";
         exit; // Or redirect back
    }
     if ($idNH_new === null) {
         echo "<script language='javascript'>alert('Vui lòng chọn Nhãn hiệu!');</script>";
         exit;
    }
    if ($idL_new === null) {
         echo "<script language='javascript'>alert('Vui lòng chọn Loại sản phẩm!');</script>";
         exit;
    }


    // Xử lý checkbox
    $loai_da_array = isset($_POST['loai_da']) && is_array($_POST['loai_da']) ? $_POST['loai_da'] : [];
    $van_de_da_array = isset($_POST['van_de_da']) && is_array($_POST['van_de_da']) ? $_POST['van_de_da'] : [];
    $loai_da_new = implode(',', $loai_da_array);
    $van_de_da_new = implode(',', $van_de_da_array);

    $urlHinh_update = $anhcu_submit;
    $new_image_uploaded = false;
    $error_upload = '';

    // echo "DEBUG: Dữ liệu nhận được từ POST:<br>";
    // echo "<pre>"; print_r($_POST); echo "</pre>"; // In ra toàn bộ dữ liệu POST
    // echo "DEBUG: Các biến đã xử lý: ...<br>"; // In các biến nếu cần

    // --- Kiểm tra tên sản phẩm trùng ---
    // echo "DEBUG: Bắt đầu kiểm tra tên trùng...<br>";
    $dem_tensp = 1;
    $sql_ktra_ten = "SELECT COUNT(*) as count FROM sanpham WHERE TenSP = ? AND idSP != ?";
    $stmt_ktra_ten = mysqli_prepare($conn, $sql_ktra_ten);
    if($stmt_ktra_ten){
        mysqli_stmt_bind_param($stmt_ktra_ten, "si", $TenSP_new, $idSP_update);
        if(mysqli_stmt_execute($stmt_ktra_ten)){
            $rs_ktra_ten = mysqli_stmt_get_result($stmt_ktra_ten);
            if($rs_ktra_ten){
                $r_ktra_ten = mysqli_fetch_assoc($rs_ktra_ten);
                $dem_tensp = $r_ktra_ten['count'];
                mysqli_free_result($rs_ktra_ten);
                // echo "DEBUG: Kết quả kiểm tra tên trùng = $dem_tensp<br>";
            } else {
                echo "<script language='JavaScript'> alert('Lỗi lấy kết quả kiểm tra tên SP: ' + " . json_encode(mysqli_stmt_error($stmt_ktra_ten)) . ");</script>";
            }
        } else {
             echo "<script language='JavaScript'> alert('Lỗi thực thi kiểm tra tên SP: ' + " . json_encode(mysqli_stmt_error($stmt_ktra_ten)) . ");</script>";
        }
        mysqli_stmt_close($stmt_ktra_ten);
    } else {
         echo "<script language='JavaScript'> alert('Lỗi chuẩn bị kiểm tra tên sản phẩm trùng: ' + " . json_encode(mysqli_error($conn)) . ");</script>";
    }

    if ($dem_tensp == 0) {
        // echo "DEBUG: Tên sản phẩm hợp lệ (không trùng).<br>";
        // --- Xử lý upload ảnh mới ---
        // echo "DEBUG: Bắt đầu xử lý upload ảnh...<br>";
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            // echo "DEBUG: Có file ảnh được upload.<br>";
            $path = "../images/"; if (!is_dir($path)) { @mkdir($path, 0777, true); } if (!is_writable($path)) { $error_upload = "Lỗi: Thư mục upload '$path' không tồn tại hoặc không có quyền ghi."; } else { $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/jpg', 'image/webp']; $max_file_size = 5 * 1024 * 1024; $tmp_name = $_FILES['file']['tmp_name']; $original_name = basename($_FILES['file']['name']); $type = $_FILES['file']['type']; $size = $_FILES['file']['size']; if (in_array($type, $allowed_types) && $size <= $max_file_size) { $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION)); $safe_filename_base = preg_replace('/[^a-zA-Z0-9_.-]/', '_', pathinfo($original_name, PATHINFO_FILENAME)); $safe_filename_base = substr($safe_filename_base, 0, 100); $unique_name = $safe_filename_base . '_sp' . $idSP_update . '_' . time() . '.' . $extension; $destination = $path . $unique_name; if (move_uploaded_file($tmp_name, $destination)) { $urlHinh_update = $unique_name; $new_image_uploaded = true; /*echo "DEBUG: Upload ảnh mới thành công: $urlHinh_update<br>";*/ } else { $error_upload = "Lỗi: Không thể di chuyển file ảnh mới tới '$destination'."; } } else { if (!in_array($type, $allowed_types)) $error_upload = "Lỗi: Định dạng file ảnh mới ('$type') không hợp lệ."; if ($size > $max_file_size) $error_upload = "Lỗi: Kích thước file ảnh mới quá lớn (" . round($size/1024/1024, 2) . "MB > 5MB)."; } }
        } elseif (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
             $phpFileUploadErrors = [ UPLOAD_ERR_INI_SIZE => 'File vượt quá upload_max_filesize.', UPLOAD_ERR_FORM_SIZE => 'File vượt quá MAX_FILE_SIZE.', UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần.', UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm.', UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file.', UPLOAD_ERR_EXTENSION  => 'Upload bị chặn bởi extension.', ]; $err_code = $_FILES['file']['error']; $error_upload = "Lỗi upload file ảnh: " . ($phpFileUploadErrors[$err_code] ?? "Mã lỗi $err_code");
             // echo "DEBUG: Lỗi upload ảnh: $error_upload<br>";
        } else {
             // echo "DEBUG: Không có file ảnh mới được upload.<br>";
        }

        if (!empty($error_upload)) {
             echo "<script language='JavaScript'> alert(" . json_encode($error_upload) . ");</script>";
        } else {
            // echo "DEBUG: Không có lỗi upload ảnh, chuẩn bị thực hiện UPDATE CSDL...<br>";
            // --- Tiến hành UPDATE vào CSDL ---
            $sql_update_sp = "UPDATE sanpham SET
                                TenSP = ?, idNH = ?, idL = ?, GiaBan = ?, GiaKhuyenmai = ?,
                                SoLuongTonKho = ?, urlHinh = ?, SoLanXem = ?, SoLanMua = ?,
                                GhiChu = ?, MoTa = ?, NgayCapNhat = ?, AnHien = ?,
                                loai_da_phu_hop = ?, van_de_da_giai_quyet = ?
                                WHERE idSP = ?";
            // echo "DEBUG: SQL Query: " . htmlspecialchars($sql_update_sp) . "<br>";

            $stmt_update = mysqli_prepare($conn, $sql_update_sp);

            if (!$stmt_update) {
                 echo "<script language='JavaScript'> alert('Lỗi chuẩn bị câu lệnh UPDATE: ' + " . json_encode(mysqli_error($conn)) . ");</script>";
                 // echo "DEBUG: Lỗi mysqli_prepare: " . htmlspecialchars(mysqli_error($conn)) . "<br>";
            } else {
                 // echo "DEBUG: mysqli_prepare thành công.<br>";
                 $types ="siiddisiisssissi";
                 $variables_to_bind = [ // Tạo mảng để dễ kiểm tra count nếu cần
                    $TenSP_new, $idNH_new, $idL_new, $GiaBan_new, $GiaKhuyenmai_new,
                    $SoLuongTonKho_new, $urlHinh_update, $SoLanXem_new, $SoLanMua_new,
                    $GhiChu_new, $MoTa_new, $ngaycapnhat_new, $AnHien_new,
                    $loai_da_new, $van_de_da_new, $idSP_update
                 ];

                 $type_count = strlen($types);
                 $var_count = count($variables_to_bind);
                 // echo "DEBUG: Số lượng kiểu = $type_count, Số lượng biến = $var_count<br>";
                 // echo "DEBUG: Binding values:<pre>"; print_r($variables_to_bind); echo "</pre>";

                 if ($type_count != $var_count) {
                     // echo "ERROR: LỖI ĐẾM SAI SỐ LƯỢNG BIND PARAM!<br>";
                     echo "<script language='JavaScript'> alert('Lỗi Bind: Số lượng không khớp! Kiểu=$type_count, Biến=$var_count');</script>"; // Hiển thị count trong alert
                 } else {
                     $bind_result = false;
                     try {
                         // Dùng list tường minh thay vì unpacking (...)
                         $bind_result = mysqli_stmt_bind_param($stmt_update, $types, $TenSP_new, $idNH_new, $idL_new, $GiaBan_new, $GiaKhuyenmai_new, $SoLuongTonKho_new, $urlHinh_update, $SoLanXem_new, $SoLanMua_new, $GhiChu_new, $MoTa_new, $ngaycapnhat_new, $AnHien_new, $loai_da_new, $van_de_da_new, $idSP_update);
                     } catch (TypeError $e) {
                         // echo "DEBUG: TypeError during bind_param: " . $e->getMessage() . "<br>";
                         echo "<script language='JavaScript'> alert('Lỗi TypeError khi bind parameters: " . json_encode($e->getMessage()) . "');</script>";
                     }

                     if (!$bind_result) {
                         // echo "DEBUG: Lỗi mysqli_stmt_bind_param!<br>";
                         // Lỗi này ít khi xảy ra nếu count đúng, nhưng có thể do kiểu dữ liệu không hợp lệ
                         echo "<script language='JavaScript'> alert('Lỗi khi bind parameters: mysqli_stmt_bind_param trả về false. Kiểm tra kiểu dữ liệu.');</script>";
                     } else {
                         // echo "DEBUG: mysqli_stmt_bind_param thành công.<br>";
                         // echo "DEBUG: Bắt đầu thực thi execute...<br>";

                         if (mysqli_stmt_execute($stmt_update)) {
                             // echo "DEBUG: mysqli_stmt_execute thành công.<br>";
                             $affected_rows = mysqli_stmt_affected_rows($stmt_update);
                             // echo "DEBUG: Số hàng bị ảnh hưởng (Affected Rows) = $affected_rows<br>";
                             mysqli_stmt_close($stmt_update);

                             if ($affected_rows > 0) {
                                 // echo "DEBUG: Affected Rows > 0, chuẩn bị chuyển hướng thành công.<br>";
                                 if ($new_image_uploaded && !empty($anhcu_submit)) { safeUnlink('../images/' . $anhcu_submit); /*echo "DEBUG: Đã xóa ảnh cũ: $anhcu_submit<br>";*/ }
                                 echo "<script language='javascript'>alert('Cập nhật sản phẩm thành công! ($affected_rows hàng được cập nhật)'); location.href='index_ds_sp.php';</script>";
                                 exit;
                             } else if ($affected_rows == 0) {
                                 // echo "DEBUG: Affected Rows == 0.<br>";
                                  if ($new_image_uploaded && !empty($anhcu_submit)) {
                                     $filepath_old = '../images/' . $anhcu_submit;
                                     // Chỉ xóa ảnh cũ nếu nó khác ảnh mới (tránh tự xóa)
                                     if (file_exists($filepath_old) && is_file($filepath_old) && $anhcu_submit != $urlHinh_update) {
                                          safeUnlink($filepath_old);
                                          // echo "DEBUG: Đã xóa ảnh cũ (chỉ đổi ảnh): $anhcu_submit<br>";
                                          echo "<script language='javascript'>alert('Cập nhật ảnh thành công!'); location.href='index_ds_sp.php';</script>";
                                          exit;
                                     } else if ($anhcu_submit == $urlHinh_update && $new_image_uploaded){
                                         // Trường hợp upload lại chính ảnh cũ? Vẫn báo thành công
                                         echo "<script language='javascript'>alert('Cập nhật ảnh thành công (ảnh mới trùng tên ảnh cũ)!'); location.href='index_ds_sp.php';</script>";
                                         exit;
                                     }
                                }
                                // echo "DEBUG: Không có thay đổi dữ liệu, chuẩn bị chuyển hướng về trang sửa.<br>";
                                echo "<script language='javascript'>alert('Cập nhật không có thay đổi dữ liệu nào được ghi nhận. Bạn có thể chưa chỉnh sửa gì.'); location.href='sua_xoa_sp.php?idSP=$idSP_update';</script>";
                                exit;
                             } else { // affected_rows < 0 nghĩa là có lỗi
                                 // echo "DEBUG: Affected Rows < 0 ($affected_rows), đây là lỗi.<br>";
                                 echo "<script language='javascript'>alert('Lỗi cập nhật: Affected rows = $affected_rows.'); location.href='index_ds_sp.php';</script>";
                                 exit;
                             }
                         } else { // Lỗi thực thi execute
                             $error_message_sql = mysqli_stmt_error($stmt_update);
                             // echo "DEBUG: Lỗi mysqli_stmt_execute: " . htmlspecialchars($error_message_sql) . "<br>";
                             echo "<script language='JavaScript'> alert('Lỗi: Cập nhật sản phẩm không thành công! Lỗi SQL: ' + " . json_encode($error_message_sql) . ");</script>";
                             if ($new_image_uploaded) { safeUnlink('../images/' . $urlHinh_update); /*echo "DEBUG: Đã xóa ảnh mới do lỗi DB: $urlHinh_update<br>";*/ }
                             if(isset($stmt_update)) mysqli_stmt_close($stmt_update);
                         }
                     } // end else bind ok
                 } // end else type/var count ok
            } // end else prepare ok
        } // end else no upload error
    } else { // Tên sản phẩm bị trùng
        // echo "DEBUG: Tên sản phẩm bị trùng, không thực hiện UPDATE.<br>";
        echo "<script language='JavaScript'> alert('Lỗi: Tên sản phẩm này đã tồn tại. Vui lòng chọn tên khác.');</script>";
    }
    // echo "<strong>--- END DEBUGGING UPDATE PROCESS ---</strong><hr>";
} // Kết thúc if (isset($_POST['update']))


// Đóng kết nối nếu được mở trong file này và chưa đóng
// if (isset($conn) && $conn instanceof mysqli) {
//    mysqli_close($conn);
// }
?>