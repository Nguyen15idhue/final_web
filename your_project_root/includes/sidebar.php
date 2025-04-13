<?php
$base_path = '/your_project_root'; // Change this to your actual root path

// Simplified navigation items array with active_check added
$nav_items = [
    // Main Navigation
    ['label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => '/pages/dashboard.php', 'active_check' => 'dashboard.php'],
    ['label' => 'Map hiển thị', 'icon' => 'fa-map-marked-alt', 'url' => '/pages/map-display.php', 'active_check' => 'map-display.php'],
    ['label' => 'Mua tài khoản', 'icon' => 'fa-shopping-cart', 'url' => '/pages/purchase/packages.php', 'active_check' => 'packages.php'],
    ['label' => 'Quản lý tài khoản', 'icon' => 'fa-tasks', 'url' => '/pages/accountmanagement.php', 'active_check' => 'accountmanagement.php'],
    ['label' => 'Quản lý giao dịch', 'icon' => 'fa-file-invoice-dollar', 'url' => '/pages/transactions.php', 'active_check' => 'transactions.php'],
    ['label' => 'Chương trình giới thiệu', 'icon' => 'fa-users', 'url' => '/pages/referral.php', 'active_check' => 'referral.php'],
    
    // Trợ giúp section
    ['type' => 'section', 'label' => 'Trợ giúp'],
    ['label' => 'Hướng dẫn sử dụng', 'icon' => 'fa-book-open', 'url' => '/pages/support/guide.php', 'active_check' => 'guide.php'],
    ['label' => 'Hỗ trợ', 'icon' => 'fa-headset', 'url' => '/pages/support/contact.php', 'active_check' => 'contact.php'],
    
    // Cài đặt section
    ['type' => 'section', 'label' => 'Cài đặt'],
    ['label' => 'Thông tin cá nhân', 'icon' => 'fa-user-circle', 'url' => '/pages/settings/profile.php', 'active_check' => 'profile.php'],
    ['label' => 'Thông tin thanh toán', 'icon' => 'fa-credit-card', 'url' => '/pages/settings/payment.php', 'active_check' => 'payment.php'],
    ['label' => 'Thông tin xuất hóa đơn', 'icon' => 'fa-file-alt', 'url' => '/pages/settings/invoice.php', 'active_check' => 'invoice.php'],
    
    // Logout
    ['type' => 'section', 'label' => 'Tài khoản'],
    ['label' => 'Đăng xuất', 'icon' => 'fa-sign-out-alt', 'url' => '/actions/auth/logout.php', 'class' => 'logout-link']
];

// Function to check if current page is active
// Include sidebar CSS
echo '<link rel="stylesheet" href="' . $base_path . '/assets/css/sidebar.css">';
echo '<link rel="stylesheet" href="' . $base_path . '/assets/css/style.css">';
function is_current_page($page_name) {
    $current_script = basename($_SERVER['SCRIPT_NAME']);
    return $current_script === $page_name;
}

$user_fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Khách hàng';
?>

<!-- Hamburger button for mobile -->
<button id="hamburger-btn" class="hamburger-btn" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<aside class="sidebar" id="sidebar">
    <!-- Logo & Toggle -->
    <div class="sidebar-header">
        <a href="<?php echo $base_path; ?>/pages/dashboard.php" class="logo-link">
            <i class="logo-icon fas fa-ruler-combined"></i>
            <span class="logo-text"><b>Tài khoản đo đạc</b></span>
        </a>
        <button class="close-button" onclick="toggleSidebar()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- User Info -->
    <div class="user-info-container">
        <div class="user-info">
            <div class="user-icon-wrapper">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-text">
                <span class="user-name"><?php echo $user_fullname; ?></span>
                <span class="user-role">Khách hàng</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <ul>
            <?php foreach ($nav_items as $item): ?>
                <?php if (isset($item['type']) && $item['type'] === 'section'): ?>
                    <li class="nav-section-title-li">
                        <p class="nav-section-title"><?php echo htmlspecialchars($item['label']); ?></p>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?php echo $base_path . htmlspecialchars($item['url']); ?>" 
                           class="nav-item <?php echo isset($item['class']) ? $item['class'] : ''; ?> 
                                  <?php echo isset($item['active_check']) && is_current_page($item['active_check']) ? 'active' : ''; ?>">
                            <i class="icon fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
                            <span><?php echo htmlspecialchars($item['label']); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>

<!-- Overlay for mobile -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>