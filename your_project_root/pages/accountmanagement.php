<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /your_project_root/?tab=login');
    exit;
}
include '../includes/header.php';
?>

<style>
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding: 1rem;
        background: #f5f5f5;
        border-radius: 5px;
    }

    .user-info {
        display: flex;
        gap: 2rem;
    }

    .user-info span {
        color: #666;
    }

    .user-info .highlight {
        color: #2196F3;
        font-weight: bold;
    }

    .accounts-wrapper {
        padding: 1rem;
    }

    .filter-section {
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .filter-button {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-button.active {
        background: #2196F3;
        color: white;
        border-color: #2196F3;
    }

    .search-box {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        width: 250px;
    }

    .accounts-grid {
        display: grid;
        gap: 1rem;
    }

    .account-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        align-items: center;
        gap: 1rem;
    }

    .account-info h3 {
        margin: 0;
        color: #333;
        font-size: 1.1rem;
    }

    .account-info p {
        margin: 0.5rem 0 0;
        color: #666;
        font-size: 0.9rem;
    }

    .account-status {
        text-align: center;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        display: inline-block;
    }

    .status-active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .status-expired {
        background: #ffebee;
        color: #c62828;
    }

    .status-pending {
        background: #fff3e0;
        color: #ef6c00;
    }

    .account-expiry {
        text-align: center;
    }

    .account-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-button {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.3s ease;
    }

    .view-btn {
        background: #2196F3;
        color: white;
    }

    .view-btn:hover {
        background: #1976D2;
    }

    .renew-btn {
        background: #4CAF50;
        color: white;
    }

    .renew-btn:hover {
        background: #388E3C;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .empty-state p {
        margin: 1rem 0;
    }

    .buy-now-btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: #2196F3;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background 0.3s ease;
    }

    .buy-now-btn:hover {
        background: #1976D2;
    }

    @media (max-width: 768px) {
        .account-card {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .account-actions {
            justify-content: center;
        }

        .filter-section {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            width: 100%;
        }
    }
</style>

<div class="dashboard-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="content">
        <div class="content-header">
            <div class="user-info">
                <span>User ID: <span class="highlight"><?php echo htmlspecialchars($_SESSION['user_id']); ?></span></span>
                <span>Username: <span class="highlight"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span></span>
                <span><?php echo date('Y-m-d H:i:s'); ?> UTC</span>
            </div>
        </div>

        <div class="accounts-wrapper">
            <h2>Quản Lý Tài Khoản</h2>

            <div class="filter-section">
                <button class="filter-button active">Tất cả</button>
                <button class="filter-button">Đang hoạt động</button>
                <button class="filter-button">Hết hạn</button>
                <button class="filter-button">Đang xử lý</button>
                <input type="text" class="search-box" placeholder="Tìm kiếm tài khoản...">
            </div>

            <div class="accounts-grid">
                <!-- Example of an active account -->
                <div class="account-card">
                    <div class="account-info">
                        <h3>Premium Account #12345</h3>
                        <p>Gói Nâng Cao - 90 ngày</p>
                    </div>
                    <div class="account-status">
                        <span class="status-badge status-active">Đang hoạt động</span>
                    </div>
                    <div class="account-expiry">
                        <p>Hết hạn: 2025-07-12</p>
                        <p>Còn lại: 90 ngày</p>
                    </div>
                    <div class="account-actions">
                        <button class="action-button view-btn">Xem chi tiết</button>
                        <button class="action-button renew-btn">Gia hạn</button>
                    </div>
                </div>

                <!-- Example of an expired account -->
                <div class="account-card">
                    <div class="account-info">
                        <h3>Premium Account #12344</h3>
                        <p>Gói Cơ Bản - 30 ngày</p>
                    </div>
                    <div class="account-status">
                        <span class="status-badge status-expired">Hết hạn</span>
                    </div>
                    <div class="account-expiry">
                        <p>Hết hạn: 2025-03-12</p>
                        <p>Đã hết hạn: 30 ngày</p>
                    </div>
                    <div class="account-actions">
                        <button class="action-button view-btn">Xem chi tiết</button>
                        <button class="action-button renew-btn">Gia hạn</button>
                    </div>
                </div>

                <!-- Example of a pending account -->
                <div class="account-card">
                    <div class="account-info">
                        <h3>Premium Account #12346</h3>
                        <p>Gói Doanh Nghiệp - 180 ngày</p>
                    </div>
                    <div class="account-status">
                        <span class="status-badge status-pending">Đang xử lý</span>
                    </div>
                    <div class="account-expiry">
                        <p>Đang chờ xác nhận</p>
                        <p>Thời gian chờ: 2 giờ</p>
                    </div>
                    <div class="account-actions">
                        <button class="action-button view-btn">Xem chi tiết</button>
                    </div>
                </div>
            </div>

            <!-- Empty state when no accounts -->
            <?php if (false): // Change this condition based on your actual data ?>
            <div class="empty-state">
                <h3>Chưa có tài khoản nào</h3>
                <p>Bạn chưa có tài khoản nào. Hãy mua tài khoản đầu tiên của bạn.</p>
                <a href="/your_project_root/pages/purchase/packages.php" class="buy-now-btn">Mua Ngay</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Xử lý các nút lọc
document.querySelectorAll('.filter-button').forEach(button => {
    button.addEventListener('click', function() {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-button').forEach(btn => {
            btn.classList.remove('active');
        });
        // Add active class to clicked button
        this.classList.add('active');
        // Add your filter logic here
    });
});

// Xử lý tìm kiếm
const searchBox = document.querySelector('.search-box');
searchBox.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    // Add your search logic here
});

// Xử lý các nút hành động
document.querySelectorAll('.view-btn').forEach(button => {
    button.addEventListener('click', function() {
        const accountId = this.closest('.account-card').querySelector('.account-info h3').textContent.match(/\d+/)[0];
        // Add your view details logic here
        alert('Xem chi tiết tài khoản #' + accountId);
    });
});

document.querySelectorAll('.renew-btn').forEach(button => {
    button.addEventListener('click', function() {
        const accountId = this.closest('.account-card').querySelector('.account-info h3').textContent.match(/\d+/)[0];
        // Add your renewal logic here
        alert('Gia hạn tài khoản #' + accountId);
    });
});
</script>

<?php include '../includes/footer.php'; ?>