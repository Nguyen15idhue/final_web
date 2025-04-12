<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /your_project_root/?tab=login');
    exit;
}
include '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="content">
        <h2>Dashboard</h2>
        <div class="stats">
            <div class="stat-card">
                <span class="icon success">✅</span>
                <h3>Tài khoản hoạt động</h3>
                <p>0</p>
            </div>
            <div class="stat-card">
                <span class="icon warning">🔄</span>
                <h3>Giao dịch xử lý</h3>
                <p>0</p>
            </div>
            <div class="stat-card">
                <span class="icon info">👥</span>
                <h3>Nguời đã giới thiệu</h3>
                <p>0</p>
            </div>
        </div>
        <div class="recent-activity">
            <h3>Hoạt động gần đây</h3>
            <p>Chưa có hoạt động nào</p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>