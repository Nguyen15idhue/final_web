<?php
session_start();

// Base URL configuration
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $domain . dirname($_SERVER['PHP_SELF']);

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// Include header
include '../includes/header.php';
?>

<!-- Nhúng CSS trực tiếp trong head -->
<style>
/* Dashboard Layout */


/* Main Content Area */

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Stat Card */
.stat-card {
    padding: 1.5rem;
    background: white;
    border-radius: var(--rounded-md);
    border: 1px solid var(--gray-200);
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stat-card .icon {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
}

.stat-card .icon.success { color: var(--primary-500); }
.stat-card .icon.warning { color: var(--badge-yellow-text); }
.stat-card .icon.info { color: var(--badge-blue-text); }

.stat-card h3 {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
    margin-bottom: 0.5rem;
}

.stat-card .value {
    font-size: 1.5rem;
    font-weight: var(--font-semibold);
    color: var(--gray-900);
}

/* Recent Activity Section */
.recent-activity {
    background: white;
    border-radius: var(--rounded-md);
    padding: 1.5rem;
    border: 1px solid var(--gray-200);
}

.recent-activity h3 {
    color: var(--gray-800);
    font-size: var(--font-size-lg);
    margin-bottom: 1rem;
    font-weight: var(--font-semibold);
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    padding: 1rem;
    border-radius: var(--rounded);
    background: var(--gray-50);
    border: 1px solid var(--gray-100);
}

.activity-item p {
    color: var(--gray-700);
    margin-bottom: 0.25rem;
}

.activity-item small {
    color: var(--gray-500);
    font-size: var(--font-size-xs);
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-wrapper {
        grid-template-columns: 1fr; /* Stacked layout on mobile */
    }

    .stats-grid {
        grid-template-columns: 1fr; /* Single column on mobile */
    }

    .content-wrapper {
        padding: 1rem;
    }
}
</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="content-wrapper">
        <h2 class="text-2xl font-semibold mb-6">Dashboard</h2>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <!-- Active Accounts Card -->
            <div class="stat-card">
                <i class="icon fas fa-users success"></i>
                <h3>Tài khoản hoạt động</h3>
                <p class="value" id="active-accounts">0</p>
            </div>

            <!-- Pending Transactions Card -->
            <div class="stat-card">
                <i class="icon fas fa-sync warning"></i>
                <h3>Giao dịch đang xử lý</h3>
                <p class="value" id="pending-transactions">0</p>
            </div>

            <!-- Referrals Card -->
            <div class="stat-card">
                <i class="icon fas fa-user-plus info"></i>
                <h3>Người được giới thiệu</h3>
                <p class="value" id="referral-count">0</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <section class="recent-activity">
            <h3>Hoạt động gần đây</h3>
            <div class="activity-list" id="activity-list">
                <!-- Activities will be loaded here via JavaScript -->
            </div>
        </section>
    </main>
</div>

<!-- JavaScript for Dashboard -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to update dashboard stats
    function updateDashboardStats() {
        // Giả lập dữ liệu - thay thế bằng API call thực tế
        const mockData = {
            activeAccounts: Math.floor(Math.random() * 1000),
            pendingTransactions: Math.floor(Math.random() * 50),
            referralCount: Math.floor(Math.random() * 100)
        };

        document.getElementById('active-accounts').textContent = mockData.activeAccounts;
        document.getElementById('pending-transactions').textContent = mockData.pendingTransactions;
        document.getElementById('referral-count').textContent = mockData.referralCount;
    }

    // Function to load recent activities
    function loadRecentActivities() {
        // Giả lập dữ liệu hoạt động - thay thế bằng API call thực tế
        const mockActivities = [
            {
                description: 'User nguyễn15 đã đăng nhập',
                timestamp: '2025-04-13 02:53:56'
            },
            {
                description: 'Giao dịch mới #123 được tạo',
                timestamp: '2025-04-13 02:50:00'
            }
        ];

        const activityList = document.getElementById('activity-list');
        if (mockActivities.length > 0) {
            activityList.innerHTML = mockActivities.map(activity => `
                <div class="activity-item">
                    <p>${activity.description}</p>
                    <small>${activity.timestamp}</small>
                </div>
            `).join('');
        } else {
            activityList.innerHTML = '<p>Không có hoạt động nào gần đây</p>';
        }
    }

    // Initial load
    updateDashboardStats();
    loadRecentActivities();

    // Refresh data every 5 minutes
    setInterval(() => {
        updateDashboardStats();
        loadRecentActivities();
    }, 300000);
});
</script>

<?php include '../includes/footer.php'; ?>