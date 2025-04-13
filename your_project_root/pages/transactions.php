<?php
session_start();

// --- Base URL Configuration ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
// Giả sử file này nằm trong /pages/
$script_dir = dirname($_SERVER['PHP_SELF']); // Should be /pages
$base_project_dir = dirname($script_dir); // Lùi 1 cấp
$base_url = $protocol . $domain . ($base_project_dir === '/' || $base_project_dir === '\\' ? '' : $base_project_dir);

// --- Project Root Path for Includes ---
$project_root_path = dirname(__DIR__); // Lùi 1 cấp từ thư mục chứa file này (pages)

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// --- User Info ---
$user_fullname = $_SESSION['fullname'] ?? 'Người dùng';
$user_id = $_SESSION['user_id'];

// --- Include Header ---
include $project_root_path . '/includes/header.php';
// ====> THÊM DÒNG NÀY ĐỂ INCLUDE FILE HÀM <====
include $project_root_path . '/config/functions.php'; // Sửa thành '/config/'

// --- Dữ liệu giao dịch giả lập (Thay bằng truy vấn CSDL thực tế) ---
$transactions = [
    [
        'id' => 'GD12345',
        'timestamp' => '2024-07-15 10:30:15',
        'description' => 'Mua Gói 1 Năm',
        'amount' => 900000,
        'method' => 'VietQR',
        'status' => 'completed', // completed, pending, failed, cancelled
        'invoice_id' => 'HD001',
        'proof_needed' => false
    ],
    [
        'id' => 'GD12346',
        'timestamp' => '2024-07-14 15:05:00',
        'description' => 'Mua Gói 3 Tháng',
        'amount' => 270000,
        'method' => 'Chuyển khoản thủ công',
        'status' => 'pending',
        'invoice_id' => null,
        'proof_needed' => true
    ],
     [
        'id' => 'GD12340',
        'timestamp' => '2024-06-10 08:00:00',
        'description' => 'Gia hạn Gói 1 Tháng',
        'amount' => 100000,
        'method' => 'VNPAY',
        'status' => 'failed',
        'invoice_id' => null,
        'proof_needed' => false,
        // ** THÊM LÝ DO THẤT BẠI **
        'failure_reason' => 'Thanh toán bị từ chối bởi cổng thanh toán (Mã lỗi: 99)'
    ],
    [
        'id' => 'GD12339',
        'timestamp' => '2024-05-15 11:00:00',
        'description' => 'Mua Gói 6 Tháng',
        'amount' => 500000,
        'method' => 'VietQR',
        'status' => 'completed',
        'invoice_id' => 'HD002',
        'proof_needed' => false
    ],
    [
        'id' => 'GD12341',
        'timestamp' => '2024-06-11 09:15:00',
        'description' => 'Mua Gói Vĩnh Viễn',
        'amount' => 5000000,
        'method' => 'Chuyển khoản thủ công',
        'status' => 'failed',
        'invoice_id' => null,
        'proof_needed' => false,
        // ** THÊM LÝ DO THẤT BẠI **
        'failure_reason' => 'Sai nội dung chuyển khoản.'
    ],
];

// Hàm helper để lấy text và class cho status
function get_transaction_status_display($status) {
    switch ($status) {
        case 'completed':
            return ['text' => 'Hoàn thành', 'class' => 'status-completed'];
        case 'pending':
            return ['text' => 'Chờ xử lý', 'class' => 'status-pending'];
        case 'failed':
            return ['text' => 'Thất bại', 'class' => 'status-failed'];
         case 'cancelled':
            return ['text' => 'Đã hủy', 'class' => 'status-cancelled'];
        default:
            return ['text' => 'Không xác định', 'class' => 'status-unknown'];
    }
}

?>

<style>
    /* --- Kế thừa các style trước --- */
     .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 0.8rem 1.5rem; background: var(--gray-50); border-radius: var(--rounded-md); border: 1px solid var(--gray-200); }
     .user-info span { color: var(--gray-600); font-size: var(--font-size-sm); }
     .user-info .highlight { color: var(--primary-600); font-weight: var(--font-semibold); }
    .transactions-wrapper { padding: 0rem 1rem 1rem 1rem; }
    .upload-proof-section { background: white; border: 1px solid var(--primary-200); border-radius: var(--rounded-lg); padding: 1.5rem; margin-bottom: 2rem; }
    .upload-proof-section h3 { font-size: var(--font-size-lg); font-weight: var(--font-semibold); color: var(--primary-700); margin-bottom: 1rem; }
    .upload-proof-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end; }
    .form-group { margin-bottom: 0; }
    .form-group label { display: block; font-weight: var(--font-medium); color: var(--gray-700); margin-bottom: 0.5rem; font-size: var(--font-size-sm); }
    .form-control { width: 100%; padding: 0.6rem 0.8rem; border: 1px solid var(--gray-300); border-radius: var(--rounded-md); font-size: var(--font-size-sm); transition: border-color 0.2s ease; }
    .form-control:focus { outline: none; border-color: var(--primary-500); box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2); }
    .form-control[type="file"] { padding: 0.4rem 0.8rem; }
    .btn-upload { padding: 0.65rem 1.5rem; background-color: var(--primary-500); color: white; border: none; border-radius: var(--rounded-md); font-weight: var(--font-semibold); cursor: pointer; transition: background-color 0.2s ease; font-size: var(--font-size-sm); white-space: nowrap; }
    .btn-upload:hover { background-color: var(--primary-600); }
    .filter-section { margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; }
    .filter-button { padding: 0.4rem 0.9rem; border: 1px solid var(--gray-300); border-radius: var(--rounded-full); background: white; cursor: pointer; transition: all 0.2s ease; font-size: var(--font-size-sm); color: var(--gray-700); }
    .filter-button.active { background: var(--primary-500); color: white; border-color: var(--primary-500); }
    .search-box { padding: 0.5rem 0.8rem; border: 1px solid var(--gray-300); border-radius: var(--rounded-md); width: 250px; max-width: 100%; font-size: var(--font-size-sm); margin-left: auto; }
     .search-box:focus { outline: none; border-color: var(--primary-500); }
    .transactions-table-wrapper { overflow-x: auto; background: white; border-radius: var(--rounded-lg); border: 1px solid var(--gray-200); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .transactions-table { width: 100%; border-collapse: collapse; }
    .transactions-table th, .transactions-table td { padding: 0.9rem 1rem; text-align: left; border-bottom: 1px solid var(--gray-200); font-size: var(--font-size-sm); vertical-align: middle; }
    .transactions-table th { background-color: var(--gray-50); font-weight: var(--font-semibold); color: var(--gray-600); white-space: nowrap; }
     .transactions-table tr:last-child td { border-bottom: none; }
     .transactions-table tr:hover { background-color: var(--gray-50); }
     .transactions-table td.amount { font-weight: var(--font-medium); color: var(--gray-800); white-space: nowrap; }
     .transactions-table td.status { text-align: center; }
     .transactions-table td.actions {
        /* text-align: right; */ /* Không cần nữa nếu dùng flex */
        /* white-space: nowrap; */ /* Không cần nữa nếu dùng flex */

        /* --- ÁP DỤNG FLEXBOX CHO CĂN CHỈNH DỌC --- */
        display: flex;              /* Kích hoạt Flexbox */
        flex-direction: column;     /* Xếp các item (nút) theo chiều dọc */
        align-items: flex-end;      /* Căn các nút sang bên phải của ô td */
                                    /* Hoặc dùng align-items: stretch; nếu muốn nút rộng hết ô */
                                    /* Hoặc align-items: center; để căn giữa */
        gap: 0.3rem;                /* Khoảng cách giữa các nút theo chiều dọc */
    }
    .status-badge { padding: 0.3rem 0.8rem; border-radius: var(--rounded-full); font-size: 0.8rem; display: inline-block; font-weight: var(--font-medium); text-align: center; min-width: 80px; }
    .status-completed { background: var(--badge-green-bg); color: var(--badge-green-text); }
    .status-pending { background: var(--badge-yellow-bg); color: var(--badge-yellow-text); }
    .status-failed { background: var(--badge-red-bg); color: var(--badge-red-text); }
    .status-cancelled { background: var(--gray-200); color: var(--gray-600); }
    .status-unknown { background: var(--gray-100); color: var(--gray-500); }
    .action-button {
        /* padding: 0.4rem 0.8rem; */ /* Có thể giữ hoặc điều chỉnh padding */
        padding: 0.5rem 0.5rem; /* Tăng padding dọc, giảm ngang chút */
        border: none;
        border-radius: var(--rounded-md);
        cursor: pointer;
        font-size: var(--font-size-xs);
        transition: background 0.2s ease, opacity 0.2s ease;
        /* margin-left: 0.5rem; */ /* Bỏ margin-left vì đã dùng flex gap */
        opacity: 0.9;
        text-decoration: none; /* Đảm bảo thẻ <a> cũng giống button */
        color: white; /* Mặc định màu chữ trắng cho các nút nền màu */
        display: block; /* Hoặc inline-block nếu không dùng flex cho td */

        /* --- ĐẶT KÍCH THƯỚC CỐ ĐỊNH VÀ CĂN GIỮA --- */
        width: 100px;           /* Đặt chiều rộng cố định (điều chỉnh giá trị nếu cần) */
        box-sizing: border-box; /* Đảm bảo padding và border nằm trong width */
        text-align: center;     /* Căn giữa nội dung (icon + text) */
        white-space: nowrap;    /* Ngăn text dài xuống dòng */
        overflow: hidden;       /* Ẩn text tràn */
        text-overflow: ellipsis;/* Hiển thị ... nếu text quá dài */
    }
    .action-button:hover { opacity: 1; }

    /* Điều chỉnh màu chữ cho nút có nền sáng */
    .btn-details { background: var(--gray-200); color: var(--gray-700); }
    .btn-details:hover { background: var(--gray-300); }
    .btn-upload-proof { background: var(--badge-yellow-bg); color: var(--badge-yellow-text); border: 1px solid var(--badge-yellow-text); width: calc(100px - 2px); /* Trừ đi border */}
    .btn-upload-proof:hover { background: var(--badge-yellow-text); color: white; }
    .btn-reason { background: var(--badge-red-bg); color: var(--badge-red-text); }
    .btn-reason:hover { background: var(--badge-red-text); color: white; }
    .btn-invoice { background: var(--primary-500); color: white; }
    .btn-invoice:hover { background: var(--primary-600); }
    .btn-upload-proof { background: var(--badge-yellow-bg); color: var(--badge-yellow-text); border: 1px solid var(--badge-yellow-text); }
    .btn-upload-proof:hover { background: var(--badge-yellow-text); color: white; }
    .btn-details { background: var(--gray-200); color: var(--gray-700); }
    .btn-details:hover { background: var(--gray-300); }

    /* --- THÊM STYLE CHO NÚT XEM LÝ DO --- */
    .btn-reason {
        background: var(--badge-red-bg);
        color: var(--badge-red-text);
        /* border: 1px solid var(--badge-red-text); */ /* Bỏ border nếu muốn */
    }
    .btn-reason:hover {
        background: var(--badge-red-text);
        color: white;
    }
    /* --- End Style nút xem lý do --- */

    .empty-state { text-align: center; padding: 3rem 1rem; color: var(--gray-500); background: white; border-radius: var(--rounded-lg); border: 1px dashed var(--gray-300); margin-top: 1.5rem; }
    .empty-state i { font-size: 2.5rem; color: var(--gray-400); margin-bottom: 1rem; display: block; }
    @media (max-width: 992px) {
        .transactions-table th:nth-child(3), .transactions-table td:nth-child(3),
        .transactions-table th:nth-child(5), .transactions-table td:nth-child(5) { }
        .transactions-table th, .transactions-table td { padding: 0.8rem 0.6rem; }
        .action-button { padding: 0.4rem 0.6rem; }
    }
     @media (max-width: 768px) {
         .content-header { flex-direction: column; align-items: flex-start; gap: 0.5rem;}
         .filter-section { flex-direction: column; align-items: stretch; }
         .search-box { width: 100%; margin-left: 0; }
         .upload-proof-form { grid-template-columns: 1fr; }
         .transactions-table th:nth-child(2), .transactions-table td:nth-child(2) { display: none; }
         .transactions-table td.actions {
             align-items: flex-start; /* Căn trái các nút trên mobile nếu muốn */
             width: auto; /* Cho phép td co giãn */
         }
         .action-button {
             /* width: 90px; */ /* Có thể giảm width nút trên mobile nếu cần */
             margin-right: 0;
             margin-bottom: 0; /* Không cần margin bottom vì đã có gap */
             /* display: inline-block; */ /* Bỏ dòng này nếu giữ flex */
         }
     }
</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php include $project_root_path . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="content-wrapper" style="padding-top: 1rem;">
        <!-- Header nhỏ trong content -->
        <div class="content-header">
            <div class="user-info">
                <span>User ID: <span class="highlight"><?php echo htmlspecialchars($user_id); ?></span></span>
                <span>|</span>
                <span>Username: <span class="highlight"><?php echo htmlspecialchars($user_fullname); ?></span></span>
            </div>
             <span style="font-size: var(--font-size-sm); color: var(--gray-500);"><?php echo date('Y-m-d H:i:s'); ?> UTC</span>
        </div>

        <!-- Wrapper chính -->
        <div class="transactions-wrapper">
            <h2 class="text-2xl font-semibold mb-5">Quản Lý Giao Dịch</h2>

            <!-- Phần gửi minh chứng -->
            <section class="upload-proof-section">
                <!-- Nội dung form gửi minh chứng giữ nguyên -->
                 <h3>Gửi Minh Chứng Thanh Toán Thủ Công</h3>
                <form action="/path/to/upload/handler.php" method="POST" enctype="multipart/form-data" class="upload-proof-form">
                     <div class="form-group">
                         <label for="transaction_id">Mã giao dịch (Nếu có):</label>
                         <input type="text" id="transaction_id" name="transaction_id" class="form-control" placeholder="Ví dụ: GD12346">
                     </div>
                     <div class="form-group">
                         <label for="payment_proof">Chọn ảnh minh chứng:</label>
                         <input type="file" id="payment_proof" name="payment_proof" class="form-control" accept="image/png, image/jpeg, image/jpg" required>
                     </div>
                    <div class="form-group">
                         <label> </label>
                        <button type="submit" class="btn-upload">
                            <i class="fas fa-upload" style="margin-right: 5px;"></i> Gửi Minh Chứng
                        </button>
                    </div>
                </form>
                <p style="font-size: var(--font-size-xs); color: var(--gray-500); margin-top: 0.75rem;">
                    * Vui lòng tải lên ảnh chụp màn hình hoặc biên lai chuyển khoản rõ ràng. Chỉ chấp nhận file ảnh (PNG, JPG, JPEG).
                </p>
            </section>

            <!-- Bộ lọc và Tìm kiếm -->
            <div class="filter-section">
                <!-- Các nút lọc và ô tìm kiếm giữ nguyên -->
                 <button class="filter-button active" data-filter="all">Tất cả</button>
                <button class="filter-button" data-filter="completed">Hoàn thành</button>
                <button class="filter-button" data-filter="pending">Chờ xử lý</button>
                <button class="filter-button" data-filter="failed">Thất bại</button>
                <input type="text" class="search-box" placeholder="Tìm theo ID, Mô tả...">
            </div>

            <!-- Bảng danh sách giao dịch -->
            <div class="transactions-table-wrapper">
                <table class="transactions-table">
                    <thead>
                        <tr>
                            <th>ID Giao dịch</th>
                            <th>Thời gian</th>
                            <th>Mô tả</th>
                            <th>Số tiền</th>
                            <th>Phương thức</th>
                            <th style="text-align: center;">Trạng thái</th>
                            <th style="text-align: right;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transactions)): ?>
                            <?php foreach ($transactions as $tx): ?>
                                <?php $status_display = get_transaction_status_display($tx['status']); ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($tx['id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($tx['timestamp']); ?></td>
                                    <td><?php echo htmlspecialchars($tx['description']); ?></td>
                                    <td class="amount"><?php echo number_format($tx['amount'], 0, ',', '.'); ?> đ</td>
                                    <td><?php echo htmlspecialchars($tx['method']); ?></td>
                                    <td class="status">
                                        <span class="status-badge <?php echo $status_display['class']; ?>">
                                            <?php echo $status_display['text']; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <!-- Nút Xem chi tiết (Tùy chọn) -->
                                        <button class="action-button btn-details" title="Xem chi tiết" onclick="alert('Xem chi tiết GD <?php echo $tx['id']; ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <!-- Nút Gửi minh chứng -->
                                        <?php if ($tx['status'] === 'pending' && $tx['proof_needed']): ?>
                                            <button class="action-button btn-upload-proof" title="Gửi minh chứng cho GD này" onclick="alert('Mở form/modal gửi minh chứng cho GD <?php echo $tx['id']; ?>')">
                                                <i class="fas fa-upload"></i> Gửi MC
                                            </button>
                                        <?php endif; ?>

                                        <!-- Nút Tải hóa đơn -->
                                        <?php if ($tx['status'] === 'completed' && !empty($tx['invoice_id'])): ?>
                                            <a href="/path/to/download/invoice.php?id=<?php echo htmlspecialchars($tx['invoice_id']); ?>" class="action-button btn-invoice" title="Tải hóa đơn <?php echo htmlspecialchars($tx['invoice_id']); ?>" download>
                                                <i class="fas fa-file-invoice-dollar"></i> Hóa đơn
                                            </a>
                                        <?php endif; ?>

                                        <!-- ** THÊM NÚT XEM LÝ DO CHO GIAO DỊCH THẤT BẠI ** -->
                                        <?php if ($tx['status'] === 'failed'): ?>
                                            <?php
                                                // Lấy lý do, hoặc đặt mặc định nếu không có
                                                $reason = htmlspecialchars($tx['failure_reason'] ?? 'Không có thông tin lý do.');
                                            ?>
                                            <button
                                                class="action-button btn-reason"
                                                title="Xem lý do thất bại"
                                                onclick="showFailureReason('<?php echo htmlspecialchars($tx['id']); ?>', '<?php echo $reason; // Đã htmlspecialchars ?>')">
                                                <i class="fas fa-info-circle"></i> Lý do
                                            </button>
                                        <?php endif; ?>
                                        <!-- ** KẾT THÚC NÚT XEM LÝ DO ** -->

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                             <!-- Dòng empty state giữ nguyên -->
                             <tr> <td colspan="7"> <div class="empty-state" style="border: none; margin: 0; padding: 2rem 1rem;"> <i class="fas fa-receipt"></i> <p>Chưa có giao dịch nào.</p> </div> </td> </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
// --- Hàm hiển thị lý do thất bại ---
function showFailureReason(transactionId, reason) {
    // Thay thế alert bằng modal hoặc cách hiển thị khác nếu muốn
    alert(`Lý do thất bại cho GD #${transactionId}:\n\n${reason}`);
}

document.addEventListener('DOMContentLoaded', function() {
    // --- Các đoạn script xử lý filter, search, upload form giữ nguyên ---
    const filterButtons = document.querySelectorAll('.filter-button');
    const transactionRows = document.querySelectorAll('.transactions-table tbody tr');
    filterButtons.forEach(button => { /* ... (code filter cũ) ... */
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            const filterValue = this.getAttribute('data-filter');
            transactionRows.forEach(row => {
                 if (row.querySelector('.empty-state')) { row.style.display = ''; return; }
                const statusCell = row.querySelector('td.status .status-badge');
                let rowStatus = 'unknown';
                if (statusCell) {
                     if (statusCell.classList.contains('status-completed')) rowStatus = 'completed';
                     else if (statusCell.classList.contains('status-pending')) rowStatus = 'pending';
                     else if (statusCell.classList.contains('status-failed')) rowStatus = 'failed';
                     else if (statusCell.classList.contains('status-cancelled')) rowStatus = 'cancelled';
                }
                if (filterValue === 'all' || rowStatus === filterValue) { row.style.display = ''; }
                else { row.style.display = 'none'; }
            });
        });
    });

    const searchBox = document.querySelector('.search-box');
    searchBox.addEventListener('input', function(e) { /* ... (code search cũ) ... */
        const searchTerm = e.target.value.toLowerCase().trim();
        transactionRows.forEach(row => {
             if (row.querySelector('.empty-state')) { row.style.display = ''; return; }
            const idCell = row.cells[0]?.textContent.toLowerCase() || '';
            const descCell = row.cells[2]?.textContent.toLowerCase() || '';
            if (idCell.includes(searchTerm) || descCell.includes(searchTerm)) { row.style.display = ''; }
            else { row.style.display = 'none'; }
        });
    });

    const uploadForm = document.querySelector('.upload-proof-form');
    if (uploadForm) { /* ... (code validation form upload cũ) ... */
        uploadForm.addEventListener('submit', function(event) {
            const fileInput = document.getElementById('payment_proof');
            if (!fileInput || fileInput.files.length === 0) { alert('Vui lòng chọn ảnh minh chứng.'); event.preventDefault(); return; }
            const file = fileInput.files[0];
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) { alert('Chỉ chấp nhận file ảnh PNG, JPG, JPEG.'); event.preventDefault(); return; }
            // alert('Đang gửi minh chứng (Cần xử lý phía server)...');
            // event.preventDefault();
        });
    }
});
</script>

<?php
// --- Include Footer ---
include $project_root_path . '/includes/footer.php';
?>