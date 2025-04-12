<?php
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';
?>
<div class="container">
    <h2>Chào mừng đến với RTK System</h2>
    
    <!-- Tabs để chuyển đổi -->
    <div class="tabs">
        <a href="/your_project_root/?tab=login" class="<?php echo $active_tab === 'login' ? 'active' : ''; ?>">Đăng nhập</a>
        <a href="/your_project_root/?tab=register" class="<?php echo $active_tab === 'register' ? 'active' : ''; ?>">Đăng ký</a>
    </div>

    <!-- Thông báo -->
    <?php
    if (isset($_SESSION['error'])) {
        echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success']) . '</p>';
        unset($_SESSION['success']);
    }
    ?>

    <!-- Include form dựa trên tab -->
    <?php
    if ($active_tab === 'login') {
        include 'login-form.php';
    } else {
        include 'register-form.php';
    }
    ?>
</div>