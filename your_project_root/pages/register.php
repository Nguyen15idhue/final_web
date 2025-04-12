<?php
session_start();
// Nếu đã đăng nhập, chuyển hướng đến dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
include '../includes/header.php';
?>

<div class="container">
    <h2>Đăng ký tài khoản</h2>
    <?php
    // Hiển thị thông báo lỗi nếu có
    if (isset($_SESSION['error'])) {
        echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
        unset($_SESSION['success']);
    }
    ?>
    <form action="../actions/auth/register.php" method="POST">
        <div>
            <label for="fullname">Họ và tên:</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </div>
        <div>
            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <label for="phone">Số điện thoại:</label>
            <input type="text" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
        </div>
        <button type="submit">Đăng ký</button>
    </form>
    <p>Đã có tài khoản? <a href="account.php">Đăng nhập</a></p>
</div>

<?php include '../includes/footer.php'; ?>