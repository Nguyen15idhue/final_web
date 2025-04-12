<div class="sidebar">
    <div class="user-info">
        <img src="/your_project_root/assets/images/user-icon.png" alt="User Icon" class="user-icon">
        <div>
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
            <span class="user-role">Khách Hàng</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="/your_project_root/pages/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <span class="icon">📊</span> Dashboard
                </a>
            </li>
            <li>
                <a href="/your_project_root/pages/purchase/packages.php">
                    <span class="icon">🛒</span> Mua tài khoản
                </a>
            </li>
            <li>
                <a href="/your_project_root/pages/accountmanagement.php">
                    <span class="icon">📜</span> Quản lý tài khoản
                </a>
            </li>
            <li>
                <a href="/your_project_root/pages/transactions.php">
                    <span class="icon">🤝</span> Quản lý giao dịch
                </a>
            </li>
            <li>
                <a href="/your_project_root/pages/account.php">
                    <span class="icon">👤</span> Chương trình giới thiệu
                </a>
            </li>
            <li>
                <a href="/your_project_root/pages/settings/profile.php">
                    <span class="icon">⚙️</span> Thông tin sử dụng
                </a>
            </li>
            <li class="settings">
                <a href="/your_project_root/pages/support/contact.php">
                    <span class="icon">📞</span> Hỗ trợ
                </a>
            </li>
            <li class="settings">
                <a href="/your_project_root/pages/settings/invoice.php">
                    <span class="icon">📝</span> Cài đặt
                </a>
            </li>
            <li class="logout">
                <a href="/your_project_root/actions/auth/logout.php">
                    <span class="icon">🚪</span> Đăng xuất
                </a>
            </li>
        </ul>
    </nav>
</div>