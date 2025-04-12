<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /your_project_root/?tab=login');
    exit;
}
include '../../includes/header.php';
?>

<style>
    .packages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        padding: 2rem;
    }

    .package-card {
        background: #fff;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        position: relative;
    }

    .package-card:hover {
        transform: translateY(-5px);
    }

    .package-card.featured {
        border: 2px solid #4CAF50;
    }

    .package-header {
        text-align: center;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .badge {
        position: absolute;
        top: -12px;
        right: -12px;
        background: #4CAF50;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
    }

    .package-header h3 {
        color: #333;
        margin: 0 0 0.5rem 0;
        font-size: 1.5rem;
    }

    .price {
        font-size: 1.75rem;
        font-weight: bold;
        color: #4CAF50;
    }

    .package-features {
        margin: 1.5rem 0;
    }

    .package-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .package-features li {
        margin: 0.75rem 0;
        color: #666;
    }

    .purchase-btn {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 5px;
        background: #2196F3;
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .purchase-btn:hover {
        background: #1976D2;
    }

    .purchase-btn.featured {
        background: #4CAF50;
    }

    .purchase-btn.featured:hover {
        background: #388E3C;
    }

    /* Thêm styles cho phần thông tin thời gian */
    .current-time {
        text-align: right;
        padding: 1rem;
        color: #666;
        font-size: 0.9rem;
    }

    /* Thêm styles cho user info */
    .user-info-bar {
        background: #f5f5f5;
        padding: 1rem;
        margin-bottom: 2rem;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .user-info-bar span {
        color: #333;
    }

    .user-info-bar .username {
        font-weight: bold;
        color: #2196F3;
    }
</style>

<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="content">
        <div class="current-time">
            <?php echo date('Y-m-d H:i:s'); ?> UTC
        </div>
        
        <div class="user-info-bar">
            <span>Xin chào, <span class="username"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Khách'); ?></span></span>
            <span>User ID: <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'N/A'); ?></span>
        </div>

        <h2>Gói Tài Khoản</h2>
        
        <div class="packages-grid">
            <div class="package-card">
                <div class="package-header">
                    <h3>Gói Cơ Bản</h3>
                    <span class="price">299.000 ₫</span>
                </div>
                <div class="package-features">
                    <ul>
                        <li>✅ 1 tài khoản Premium</li>
                        <li>✅ Thời hạn 30 ngày</li>
                        <li>✅ Hỗ trợ 24/7</li>
                        <li>✅ Bảo hành trong thời gian sử dụng</li>
                    </ul>
                </div>
                <button class="purchase-btn" onclick="handlePurchase('basic', 299000)">Mua Ngay</button>
            </div>

            <div class="package-card featured">
                <div class="package-header">
                    <span class="badge">Phổ biến</span>
                    <h3>Gói Nâng Cao</h3>
                    <span class="price">799.000 ₫</span>
                </div>
                <div class="package-features">
                    <ul>
                        <li>✅ 3 tài khoản Premium</li>
                        <li>✅ Thời hạn 90 ngày</li>
                        <li>✅ Hỗ trợ 24/7</li>
                        <li>✅ Bảo hành trong thời gian sử dụng</li>
                        <li>✅ Ưu tiên hỗ trợ</li>
                    </ul>
                </div>
                <button class="purchase-btn featured" onclick="handlePurchase('premium', 799000)">Mua Ngay</button>
            </div>

            <div class="package-card">
                <div class="package-header">
                    <h3>Gói Doanh Nghiệp</h3>
                    <span class="price">1.499.000 ₫</span>
                </div>
                <div class="package-features">
                    <ul>
                        <li>✅ 7 tài khoản Premium</li>
                        <li>✅ Thời hạn 180 ngày</li>
                        <li>✅ Hỗ trợ 24/7</li>
                        <li>✅ Bảo hành trong thời gian sử dụng</li>
                        <li>✅ Ưu tiên hỗ trợ cao cấp</li>
                        <li>✅ Giảm giá gia hạn</li>
                    </ul>
                </div>
                <button class="purchase-btn" onclick="handlePurchase('business', 1499000)">Mua Ngay</button>
            </div>
        </div>
    </div>
</div>

<script>
function handlePurchase(packageType, amount) {
    if (confirm('Bạn có chắc chắn muốn mua gói ' + packageType + ' với giá ' + amount.toLocaleString() + ' VNĐ?')) {
        // Tại đây bạn có thể thêm code để chuyển hướng đến trang thanh toán
        // hoặc mở modal thanh toán
        alert('Đang chuyển đến trang thanh toán...');
        // window.location.href = '/your_project_root/pages/purchase/checkout.php?package=' + packageType + '&amount=' + amount;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>