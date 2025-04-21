<?php
// ---- KHỞI ĐẦU: Kết nối và Xử lý POST/GET ----
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    ob_start();
}

include_once ('../connection/connect_database.php');

// --- Xử lý yêu cầu XÓA bình luận (Giữ nguyên) ---
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['idCM'])) {
    $id_comment_delete = filter_input(INPUT_GET, 'idCM', FILTER_VALIDATE_INT);
    if ($id_comment_delete && $id_comment_delete > 0) {
        $sql_delete = "DELETE FROM sanpham_comment WHERE id_comment = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "i", $id_comment_delete);
            if (mysqli_stmt_execute($stmt_delete)) {
                 $_SESSION['flash_message'] = (mysqli_stmt_affected_rows($stmt_delete) > 0) ? "Đã xóa bình luận thành công (ID: $id_comment_delete)." : "Không tìm thấy bình luận để xóa (ID: $id_comment_delete) hoặc không có gì thay đổi.";
            } else {
                $_SESSION['flash_error'] = "Lỗi khi thực thi xóa bình luận: " . mysqli_stmt_error($stmt_delete);
                error_log("Delete comment error (execute): " . mysqli_stmt_error($stmt_delete) . " - ID: " . $id_comment_delete);
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            $_SESSION['flash_error'] = "Lỗi chuẩn bị câu lệnh xóa: " . mysqli_error($conn);
            error_log("Delete comment error (prepare): " . mysqli_error($conn));
        }
    } else {
        $_SESSION['flash_error'] = "ID bình luận không hợp lệ để xóa.";
    }
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
    exit;
}


// --- Xử lý yêu cầu thay đổi trạng thái (Giữ nguyên) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'set_status' && isset($_POST['id_comment']) && isset($_POST['new_status'])) {
    $id_comment_update = (int)$_POST['id_comment'];
    $new_status = (int)$_POST['new_status'];
    $allowed_statuses = [0, 1];
    if (in_array($new_status, $allowed_statuses)) {
        $sql_update_status = "UPDATE sanpham_comment SET kiem_duyet = ? WHERE id_comment = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update_status);
        if ($stmt_update) {
            mysqli_stmt_bind_param($stmt_update, "ii", $new_status, $id_comment_update);
            if (mysqli_stmt_execute($stmt_update)) {
                $_SESSION['flash_message'] = "Cập nhật trạng thái bình luận thành công.";
            } else {
                $_SESSION['flash_error'] = "Lỗi: Không thể cập nhật trạng thái. " . mysqli_stmt_error($stmt_update);
                error_log("Update comment status error (execute): " . mysqli_stmt_error($stmt_update) . " - ID: " . $id_comment_update);
            }
            mysqli_stmt_close($stmt_update);
        } else {
            $_SESSION['flash_error'] = "Lỗi chuẩn bị câu lệnh cập nhật: " . mysqli_error($conn);
            error_log("Update comment status error (prepare): " . mysqli_error($conn));
        }
    } else {
        $_SESSION['flash_error'] = "Lỗi: Trạng thái mới không hợp lệ.";
    }
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
    exit;
}


// --- Lấy danh sách bình luận (Giữ nguyên) ---
$sl_sp_cmt = "SELECT * FROM sanpham_comment ORDER BY ngay_comment DESC";
$rs_sp_cmt = mysqli_query($conn,$sl_sp_cmt);
if(!$rs_sp_cmt) {
    error_log("Error fetching comments: " . mysqli_error($conn));
    $error_page_message = "Lỗi: Không thể truy vấn cơ sở dữ liệu bình luận. Vui lòng thử lại sau.";
}

// --- Lấy và xóa thông báo từ session (Giữ nguyên) ---
$flash_message = '';
if (isset($_SESSION['flash_message'])) {
    $flash_message = "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($_SESSION['flash_message']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    unset($_SESSION['flash_message']);
}
$flash_error = '';
if (isset($_SESSION['flash_error'])) {
    $flash_error = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . htmlspecialchars($_SESSION['flash_error']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    unset($_SESSION['flash_error']);
}

// --- Bắt đầu Output HTML ---
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once ("header1.php");?>
    <title>Quản lý Bình luận Sản phẩm</title>
    <?php include_once('header2.php');?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
          /* CSS giữ nguyên như phiên bản trước khi thêm sửa giờ */
          .action-col { white-space: nowrap; width: 1%; text-align: center; vertical-align: middle; }
          .status-col { width: 120px; text-align: center; vertical-align: middle; }
          .stt-col { width: 60px; text-align: center; vertical-align: middle; }
          .idsp-col { width: 80px; text-align: center; vertical-align: middle; }
          .date-col { min-width: 140px; text-align: center; vertical-align: middle; } /* Có thể giảm min-width nếu muốn */
          .rating-col { width: 110px; text-align: center; vertical-align: middle; }
          .rating-col .star { font-size: 0.9em; margin-right: 1px; }
          .rating-col .star.filled { color: #ffc107; }
          .rating-col .star.empty { color: #e0e0e0; }
          .rating-col .no-rating { font-style: italic; color: #888; font-size: 0.9em; }
          .comment-content { max-width: 300px; white-space: normal; word-wrap: break-word; vertical-align: middle;}
          .table td, .table th { vertical-align: middle; }
          .approve-form { display: inline-block; margin-left: 5px; vertical-align: middle; margin-bottom: 0;}
          .action-col .btn, .approve-form .btn {
              padding: 0.25rem 0.5rem;
              font-size: 0.8rem;
              min-width: 95px;
              margin-bottom: 3px;
              display: inline-flex;
              align-items: center;
              justify-content: center;
              gap: 4px;
          }
          .card-header { background-color: rgba(0, 123, 255, 0.1); }
          .card-header h5 { color: #007bff; margin-bottom: 0; font-weight: 600; }
          .card { border: 1px solid #dee2e6; }
          .table-hover tbody tr:hover { background-color: #f8f9fa; }
          .table th { background-color: #e9ecef; }
    </style>
</head>
<body>
<?php include_once ('header3.php');?>

<div class="container-fluid mt-4">

    <?php
        echo $flash_message;
        echo $flash_error;
        if (isset($error_page_message)) {
             echo "<div class='alert alert-danger'>".htmlspecialchars($error_page_message)."</div>";
        }
    ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary"> <i class="fas fa-comments me-2"></i> DANH SÁCH BÌNH LUẬN SẢN PHẨM </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped table-sm align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="stt-col"><strong>STT</strong></th>
                            <th class="idsp-col"><strong>MÃ SP</strong></th>
                            <th><strong>HỌ TÊN</strong></th>
                            <th class="date-col"><strong>NGÀY & GIỜ</strong></th> <?php // Giữ nguyên tiêu đề ?>
                            <th class="comment-content"><strong>NỘI DUNG</strong></th>
                            <th class="rating-col"><strong>ĐÁNH GIÁ</strong></th>
                            <th class="status-col"><strong>TRẠNG THÁI</strong></th>
                            <th class="action-col"><strong>THAO TÁC</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($rs_sp_cmt) && mysqli_num_rows($rs_sp_cmt) > 0) {
                            $stt = 1;
                            while ($r = $rs_sp_cmt->fetch_assoc()) {
                                $id_comment_current = $r['id_comment'];
                                // Lấy và định dạng ngày giờ
                                $ngay_comment_obj = !empty($r['ngay_comment']) ? strtotime($r['ngay_comment']) : null;
                                $display_datetime = $ngay_comment_obj ? date("d/m/Y H:i", $ngay_comment_obj) : 'N/A'; // Chỉ cần định dạng này
                        ?>
                                <tr>
                                    <td class="stt-col text-center"><strong> <?php echo $stt++; ?> </strong></td>
                                    <td class="idsp-col text-center"><strong><?php echo $r['idSP']; ?> </strong></td>
                                    <td><strong><?php echo htmlspecialchars($r['hoten']); ?> </strong></td>
                                    <td class="date-col text-center">
                                        <?php // ***** THAY ĐỔI: Chỉ hiển thị ngày giờ ***** ?>
                                        <strong><?php echo $display_datetime; ?></strong>
                                        <?php // ***** Kết thúc hiển thị ***** ?>
                                    </td>
                                    <td class="comment-content">
                                        <?php
                                             $plain_text = strip_tags($r['noidung']);
                                             $decoded_text = html_entity_decode($plain_text);
                                        ?>
                                        <strong><?php echo nl2br(htmlspecialchars($decoded_text)); ?></strong>
                                    </td>
                                    <td class="rating-col">
                                        <?php /* Hiển thị sao (giữ nguyên) */
                                            $rating = isset($r['rating']) ? (int)$r['rating'] : null;
                                            if ($rating !== null && $rating > 0) {
                                                for ($i = 1; $i <= 5; $i++) {
                                                    $star_class = ($i <= $rating) ? 'fas fa-star star filled' : 'far fa-star star empty';
                                                    echo '<i class="' . $star_class . '"></i>';
                                                }
                                            } else {
                                                echo '<span class="no-rating">Chưa có</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="status-col text-center">
                                        <strong><?php /* Trạng thái (giữ nguyên) */
                                            if ($r['kiem_duyet'] == 1) echo '<span class="badge bg-success">Đã duyệt</span>';
                                            else echo '<span class="badge bg-warning text-dark">Chưa duyệt</span>';
                                        ?></strong>
                                    </td>
                                    <td class="action-col">
                                        <?php // ***** THAY ĐỔI: Đã xóa nút Sửa giờ ***** ?>

                                        <?php // Nút Xóa (giữ nguyên) ?>
                                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?action=delete&idCM=<?php echo $id_comment_current; ?>"
                                           class="btn btn-danger btn-sm" title="Xóa bình luận này"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận ID: <?php echo $id_comment_current; ?> ? Hành động này không thể hoàn tác!');">
                                            <i class="fas fa-trash-alt"></i> <strong>XÓA</strong>
                                        </a>

                                        <?php // Form Duyệt/Bỏ duyệt (giữ nguyên) ?>
                                        <?php if ($r['kiem_duyet'] == 0): ?>
                                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="approve-form">
                                                <input type="hidden" name="action" value="set_status">
                                                <input type="hidden" name="id_comment" value="<?php echo $id_comment_current; ?>">
                                                <input type="hidden" name="new_status" value="1">
                                                <button type="submit" class="btn btn-success btn-sm" title="Duyệt bình luận này">
                                                    <i class="fas fa-check"></i> <strong>DUYỆT</strong>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($r['kiem_duyet'] == 1): ?>
                                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="approve-form">
                                                <input type="hidden" name="action" value="set_status">
                                                <input type="hidden" name="id_comment" value="<?php echo $id_comment_current; ?>">
                                                <input type="hidden" name="new_status" value="0">
                                                <button type="submit" class="btn btn-warning btn-sm" title="Bỏ duyệt (Chuyển về Chưa duyệt)">
                                                    <i class="fas fa-undo"></i> <strong>BỎ DUYỆT</strong>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php
                            } // end while
                            mysqli_free_result($rs_sp_cmt);
                        } else {
                            if (!isset($error_page_message)) {
                        ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted p-3">Chưa có bình luận nào.</td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once ('footer.php');?>

<?php // Giữ lại các script cần thiết khác (jQuery, Bootstrap) nhưng xóa phần script xử lý sửa giờ ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php
    if (isset($conn) && $conn) {
         mysqli_close($conn);
    }
    if (ob_get_level() > 0) {
         ob_end_flush();
    }
?>
</body>
</html>