<?php
session_start();
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Vui lòng nhập đầy đủ email và mật khẩu.';
        header('Location: /your_project_root/?tab=login');
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            // Chuyển hướng đến Dashboard
            header('Location: /your_project_root/pages/dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = 'Email hoặc mật khẩu không đúng.';
            header('Location: /your_project_root/?tab=login');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.';
        header('Location: /your_project_root/?tab=login');
        exit;
    }
} else {
    header('Location: /your_project_root/?tab=login');
    exit;
}
?>