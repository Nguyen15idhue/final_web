<?php
session_start();
// Nếu đã đăng nhập, chuyển hướng đến dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
include '../includes/header.php';
?>

<div class="container">
    <h2>Đăng nhập</h2>
    <?php
    // Hiển thị thông báo lỗi nếu có
    if (isset($_SESSION['error'])) {
        echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
        unset($_SESSION['error']);
    }
    ?>
    <form action="../actions/auth/login.php" method="POST">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Đăng nhập</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>