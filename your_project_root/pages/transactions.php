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
        align-items: center;
    }

    .user-info span {
        color: #666;
    }

    .user-info .highlight {
        color: #2196F3;
        font-weight: bold;
    }

    .transactions-wrapper {
        padding: 1rem;
    }

    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .summary-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .summary-card h3 {
        margin: 0;
        color: #666;
        font-size: 1rem;
    }

    .summary-card .amount {
        font-size: 1.5rem;
        font-weight: bold;
        color: #2196F3;
        margin: 0.5rem 0;
    }

    .filter-section {
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
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

    .date-filter {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .date-input {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .transactions-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .transactions-table th,
    .transactions-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .transactions-table th {
        background: #f5f5f5;
        font-weight: 600;
        color: #333;
    }

    .transactions-table tr:hover {
        background: #f8f9fa;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        display: inline-block;
    }

    .status-success {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .status-pending {
        background: #fff3e0;
        color: #ef6c00;
    }

    .status-failed {
        background: #ffebee;
        color: #c62828;
    }

    .transaction-amount {
        font-weight: bold;
    }

    .amount-positive {
        color: #2e7d32;
    }

    .amount-negative {
        color: #c62828;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .pagination button {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: white;
        cursor: pointer;
    }

    .pagination button.active {
        background: #2196F3;
        color: white;
        border-color: #2196F3;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    @media (max-width: 768px) {
        .filter-section {
            flex-direction: column;
            align-items: stretch;
        }

        .date-filter {
            flex-direction: column;
        }

        .transactions-table {
            display: block;
            overflow-x: auto;
        }
    }
</style>

<div class="dashboard-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="content">
        <div class="content-header">
            <div class="user-info">
                <span>Current Date: <span class="highlight">2025-04-12 11:18:04 UTC</span></span>
                <span>Username: <span class="highlight">Nguyen15idhue</span></span>
            </div>
        </div>

        <div class="transactions-wrapper">
            <h2>Quản Lý Giao Dịch</h2>

            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Tổng Giao Dịch</h3>
                    <div class="amount">15</div>
                </div>
                <div class="summary-card">
                    <h3>Tổng Chi Tiêu</h3>
                    <div class="amount">2.897.000 ₫</div>
                </div>
                <div class="summary-card">
                    <h3>Giao Dịch Tháng Này</h3>
                    <div class="amount">3</div>
                </div>
            </div>

            <div class="filter-section">
                <div>
                    <button class="filter-button active">Tất cả</button>
                    <button class="filter-button">Thành công</button>
                    <button class="filter-button">Đang xử lý</button>
                    <button class="filter-button">Thất bại</button>
                </div>
                <div class="date-filter">
                    <input type="date" class="date-input" placeholder="Từ ngày">
                    <input type="date" class="date-input" placeholder="Đến ngày">
                    <button class="filter-button">Lọc</button>
                </div>
            </div>

            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>ID Giao Dịch</th>
                        <th>Ngày</th>
                        <th>Loại Giao Dịch</th>
                        <th>Mô Tả</th>
                        <th>Số Tiền</th>
                        <th>Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#TRX123456</td>
                        <td>2025-04-12 10:15:23</td>
                        <td>Mua Gói Premium</td>
                        <td>Gói Premium 90 ngày</td>
                        <td class="transaction-amount amount-negative">-799.000 ₫</td>
                        <td><span class="status-badge status-success">Thành công</span></td>
                    </tr>
                    <tr>
                        <td>#TRX123455</td>
                        <td>2025-04-11 15:30:45</td>
                        <td>Gia Hạn Gói Basic</td>
                        <td>Gia hạn gói Basic 30 ngày</td>
                        <td class="transaction-amount amount-negative">-299.000 ₫</td>
                        <td><span class="status-badge status-pending">Đang xử lý</span></td>
                    </tr>
                    <tr>
                        <td>#TRX123454</td>
                        <td>2025-04-10 09:20:15</td>
                        <td>Mua Gói Business</td>
                        <td>Gói Business 180 ngày</td>
                        <td class="transaction-amount amount-negative">-1.499.000 ₫</td>
                        <td><span class="status-badge status-failed">Thất bại</span></td>
                    </tr>
                </tbody>
            </table>

            <div class="pagination">
                <button>Trước</button>
                <button class="active">1</button>
                <button>2</button>
                <button>3</button>
                <button>Sau</button>
            </div>

            <?php if (false): // Change this condition based on your actual data ?>
            <div class="empty-state">
                <h3>Chưa có giao dịch nào</h3>
                <p>Bạn chưa thực hiện giao dịch nào. Hãy mua gói tài khoản đầu tiên của bạn.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Xử lý các nút lọc
document.querySelectorAll('.filter-button').forEach(button => {
    button.addEventListener('click', function() {
        if (!this.closest('.date-filter')) {
            document.querySelectorAll('.filter-button:not(.date-filter .filter-button)').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
        }
        // Add your filter logic here
    });
});

// Xử lý phân trang
document.querySelectorAll('.pagination button').forEach(button => {
    button.addEventListener('click', function() {
        if (!this.textContent.match(/Trước|Sau/)) {
            document.querySelectorAll('.pagination button').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
        }
        // Add your pagination logic here
    });
});

// Xử lý lọc theo ngày
document.querySelector('.date-filter .filter-button').addEventListener('click', function() {
    const fromDate = document.querySelectorAll('.date-input')[0].value;
    const toDate = document.querySelectorAll('.date-input')[1].value;
    // Add your date filter logic here
    console.log('Filter from', fromDate, 'to', toDate);
});
</script>

<?php include '../includes/footer.php'; ?>