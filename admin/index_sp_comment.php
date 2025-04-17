<?php
// ---- KHỞI ĐẦU: Kết nối và Xử lý POST/GET ----
// Đảm bảo session được khởi tạo trước bất kỳ output nào
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    ob_start(); // Dùng output buffering nếu cần thiết
}

include_once ('../connection/connect_database.php'); // Kết nối CSDL

// --- Xử lý yêu cầu XÓA bình luận (Thêm mới - qua GET) ---
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['idCM'])) {
    $id_comment_delete = filter_input(INPUT_GET, 'idCM', FILTER_VALIDATE_INT); // Lấy và lọc ID

    // Kiểm tra ID hợp lệ
    if ($id_comment_delete && $id_comment_delete > 0) {
        // Lưu ý: Thiếu CSRF Token - Để bảo mật hơn nên dùng POST và token
        // Tuy nhiên, để phù hợp với cấu trúc hiện tại (link GET + confirm JS), tạm thời bỏ qua token

        $sql_delete = "DELETE FROM sanpham_comment WHERE id_comment = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);

        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "i", $id_comment_delete);
            if (mysqli_stmt_execute($stmt_delete)) {
                // Kiểm tra xem có dòng nào thực sự bị xóa không
                if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
                    $_SESSION['flash_message'] = "Đã xóa bình luận thành công (ID: " . $id_comment_delete . ").";
                } else {
                    // Có thể ID không tồn tại
                    $_SESSION['flash_error'] = "Không tìm thấy bình luận để xóa (ID: " . $id_comment_delete . ") hoặc không có gì thay đổi.";
                }
            } else {
                // Lỗi khi thực thi câu lệnh DELETE
                $_SESSION['flash_error'] = "Lỗi khi thực thi xóa bình luận: " . mysqli_stmt_error($stmt_delete);
                error_log("Delete comment error (execute): " . mysqli_stmt_error($stmt_delete) . " - ID: " . $id_comment_delete);
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            // Lỗi khi chuẩn bị câu lệnh DELETE
            $_SESSION['flash_error'] = "Lỗi chuẩn bị câu lệnh xóa: " . mysqli_error($conn);
            error_log("Delete comment error (prepare): " . mysqli_error($conn));
        }
    } else {
        // ID không hợp lệ
        $_SESSION['flash_error'] = "ID bình luận không hợp lệ để xóa.";
    }

    // Chuyển hướng về trang này để xóa tham số GET và hiển thị flash message
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
    exit;
}


// --- Xử lý yêu cầu thay đổi trạng thái (Duyệt/Bỏ duyệt) (Giữ nguyên - qua POST) ---
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
    // Tải lại trang sau khi cập nhật trạng thái
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
    exit;
}


// --- Lấy danh sách bình luận (Giữ nguyên) ---
$sl_sp_cmt = "SELECT * FROM sanpham_comment ORDER BY ngay_comment DESC";
$rs_sp_cmt = mysqli_query($conn,$sl_sp_cmt);
if(!$rs_sp_cmt) {
    // Thay vì die, ghi log và hiển thị lỗi thân thiện hơn
    error_log("Error fetching comments: " . mysqli_error($conn));
    $error_page_message = "Lỗi: Không thể truy vấn cơ sở dữ liệu bình luận. Vui lòng thử lại sau.";
    // die("Lỗi: Không thể truy vấn cơ sở dữ liệu bình luận.");
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
    <?php // Thêm link Font Awesome nếu chưa có (cần cho icon fas fa-...) ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS Giữ nguyên như trước */
         .action-col { white-space: nowrap; width: 1%; text-align: center; vertical-align: middle; }
         .status-col { width: 120px; text-align: center; vertical-align: middle; }
         .stt-col { width: 60px; text-align: center; vertical-align: middle; }
         .idsp-col { width: 80px; text-align: center; vertical-align: middle; }
         .date-col { min-width: 140px; text-align: center; vertical-align: middle; } /* Đã cập nhật min-width nếu cần */
         .comment-content { max-width: 300px; white-space: normal; word-wrap: break-word; vertical-align: middle;}
         .table td, .table th { vertical-align: middle; }
         .approve-form { display: inline-block; margin-left: 5px; vertical-align: middle; margin-bottom: 0;} /* Bỏ margin bottom cho form */
         .action-col .btn, .approve-form .btn { /* Áp dụng chung cho nút và form */
             padding: 0.25rem 0.5rem;
             font-size: 0.8rem;
             min-width: 95px; /* Giữ kích thước nút bằng nhau */
             margin-bottom: 3px; /* Khoảng cách nếu nút xuống dòng */
             display: inline-flex; /* Để căn icon và text */
             align-items: center;
             justify-content: center;
             gap: 4px; /* Khoảng cách giữa icon và text */
         }
         .card-header { background-color: rgba(0, 123, 255, 0.1); }
         .card-header h5 { color: #007bff; margin-bottom: 0; font-weight: 600; }
         .card { border: 1px solid #dee2e6; }
         .table-hover tbody tr:hover {
             background-color: #f8f9fa;
         }
         .table th { background-color: #e9ecef; } /* Nền header bảng */
    </style>
</head>
<body>
<?php include_once ('header3.php');?>

<div class="container-fluid mt-4">

    <?php
        // Hiển thị thông báo flash từ session
        echo $flash_message;
        echo $flash_error;
        // Hiển thị lỗi truy vấn trang nếu có
        if (isset($error_page_message)) {
             echo "<div class='alert alert-danger'>".htmlspecialchars($error_page_message)."</div>";
        }
    ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary"> <i class="fas fa-comments me-2"></i> DANH SÁCH BÌNH LUẬN SẢN PHẨM </h5>
            <?php // Có thể thêm nút hoặc bộ lọc ở đây nếu cần ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped table-sm align-middle"> <?php // align-middle căn giữa dọc ?>
                    <thead class="table-light text-center">
                        <tr>
                            <th class="stt-col"><strong>STT</strong></th>
                            <th class="idsp-col"><strong>MÃ SP</strong></th>
                            <th><strong>HỌ TÊN</strong></th>
                            <th class="date-col"><strong>NGÀY</strong></th>
                            <th class="comment-content"><strong>NỘI DUNG</strong></th>
                            <th class="status-col"><strong>TRẠNG THÁI</strong></th>
                            <th class="action-col"><strong>THAO TÁC</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Chỉ lặp nếu $rs_sp_cmt hợp lệ và có dữ liệu
                        if (isset($rs_sp_cmt) && mysqli_num_rows($rs_sp_cmt) > 0) {
                            $stt = 1;
                            while ($r = $rs_sp_cmt->fetch_assoc()) {
                                $id_comment_current = $r['id_comment'];
                        ?>
                                <tr>
                                    <td class="stt-col text-center"><strong> <?php echo $stt++; ?> </strong></td>
                                    <td class="idsp-col text-center"><strong><?php echo $r['idSP']; ?> </strong></td>
                                    <td><strong><?php echo htmlspecialchars($r['hoten']); ?> </strong></td>
                                    <td class="date-col text-center"><strong><?php echo date("d/m/Y", strtotime($r['ngay_comment'])); // <<<=== ĐÃ SỬA Ở ĐÂY ?></strong></td>
                                    <td class="comment-content">
                                        <?php
                                             // Xử lý hiển thị nội dung an toàn và giữ định dạng xuống dòng
                                             $plain_text = strip_tags($r['noidung']); // Loại bỏ thẻ HTML nếu có
                                             $decoded_text = html_entity_decode($plain_text); // Giải mã các thực thể HTML (như &lt;)
                                        ?>
                                        <strong><?php echo nl2br(htmlspecialchars($decoded_text)); // Chuyển đổi xuống dòng và escape ký tự đặc biệt ?></strong>
                                    </td>
                                    <td class="status-col text-center">
                                        <strong>
                                        <?php
                                             if ($r['kiem_duyet'] == 1) {
                                                 echo '<span class="badge bg-success">Đã duyệt</span>';
                                             } else {
                                                 echo '<span class="badge bg-warning text-dark">Chưa duyệt</span>';
                                             }
                                        ?>
                                        </strong>
                                    </td>
                                    <td class="action-col">
                                        <?php // Nút Xóa - Đã sửa href ?>
                                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?action=delete&idCM=<?php echo $id_comment_current; ?>"
                                           class="btn btn-danger btn-sm"
                                           title="Xóa bình luận này"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận ID: <?php echo $id_comment_current; ?> ? Hành động này không thể hoàn tác!');"> <?php // Thêm ID vào confirm ?>
                                            <i class="fas fa-trash-alt"></i> <strong>XÓA</strong>
                                        </a>

                                        <?php // Nút/Form Duyệt ?>
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

                                        <?php // Nút/Form Bỏ Duyệt ?>
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
                            mysqli_free_result($rs_sp_cmt); // Giải phóng kết quả sau vòng lặp
                        } else { // Không có bình luận hoặc có lỗi truy vấn trước đó
                            if (!isset($error_page_message)) { // Chỉ hiển thị nếu không có lỗi nghiêm trọng hơn
                        ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted p-3">Chưa có bình luận nào.</td>
                                </tr>
                        <?php
                            } // end if !isset($error_page_message)
                        } // end if mysqli_num_rows
                        ?>
                    </tbody>
                </table>
            </div> <?php // end table-responsive ?>
        </div> <?php // end card-body ?>
    </div> <?php // end card ?>

</div> <?php // end container-fluid ?>

<?php include_once ('footer.php');?>
<?php
    if (isset($conn) && $conn) { // Đóng kết nối nếu nó tồn tại
         mysqli_close($conn);
    }
    if (ob_get_level() > 0) { // Chỉ gọi ob_end_flush nếu output buffering đang bật
         ob_end_flush();
    }
?>
</body>
</html>