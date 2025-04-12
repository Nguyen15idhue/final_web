<?php
session_start();
// Xóa tất cả dữ liệu session
session_unset();
session_destroy();
// Chuyển hướng về trang đăng nhập
header('Location: /your_project_root/?tab=login');
exit;
?>