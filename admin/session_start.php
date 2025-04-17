<?php
// --- SESSION MANAGEMENT ---
// Luôn kiểm tra trạng thái trước khi bắt đầu session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- OUTPUT BUFFERING ---
// Bắt đầu output buffering (hữu ích khi cần dùng header() sau này)
ob_start();

// --- KIỂM TRA QUYỀN ADMIN ---
// Nên kiểm tra dựa trên ID hoặc Role thay vì username cố định
// Ví dụ kiểm tra IDUser = 1 là admin (Như logic đăng nhập cũ)
if (!isset($_SESSION['IDUser']) || $_SESSION['IDUser'] != 1) {
    // Nếu không phải admin (hoặc chưa đăng nhập), chuyển hướng về trang đăng nhập
    header('location: ../site/DangNhap.php');
    exit(); // *** Rất quan trọng: Dừng script ngay sau khi chuyển hướng ***
}

// Nếu là admin thì script sẽ tiếp tục chạy các phần bên dưới (nếu có)
// hoặc các file include file này sẽ tiếp tục chạy

?>