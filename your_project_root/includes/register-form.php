<div class="form-container">
    <h3>Đăng ký</h3>
    <form action="/your_project_root/actions/auth/register.php" method="POST">
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
    <p>Đã có tài khoản? <a href="/your_project_root/?tab=login">Đăng nhập</a></p>
</div>