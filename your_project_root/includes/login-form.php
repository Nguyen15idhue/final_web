<div class="form-container">
    <h3>Đăng nhập</h3>
    <form action="/your_project_root/actions/auth/login.php" method="POST">
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
    <p>Chưa có tài khoản? <a href="/your_project_root/?tab=register">Đăng ký</a></p>
</div>