<?php
session_start();
require 'config/database.php';
require 'config/functions.php'; // Nếu có

// Kiểm tra đăng nhập
if (isset($_SESSION['user_id'])) {
    header('Location: /your_project_root/pages/dashboard.php');
    exit;
}

// Lấy URL hoặc mặc định là 'home'
$url = isset($_GET['url']) ? trim($_GET['url'], '/') : 'home';

// Xác định tab cho trang chủ (login/register)
$active_tab = ($url === 'home' && isset($_GET['tab']) && $_GET['tab'] === 'register') ? 'register' : 'login';

// Xác định file giao diện
if ($url === 'home') {
    $page = 'includes/auth.php'; // Partial chứa tabs và form
} else {
    $page = 'pages/' . $url . '.php';
}

// Kiểm tra file tồn tại
if (file_exists($page)) {
    include 'includes/header.php';
    include $page;
    include 'includes/footer.php';
} else {
    include 'includes/header.php';
    include 'pages/404.php'; // Tạo file này nếu cần
    include 'includes/footer.php';
}
?>