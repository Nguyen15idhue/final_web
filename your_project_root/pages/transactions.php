<?php
session_start();

// --- Base URL và Path ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
// File này nằm trong /pages/ => cần lùi lại 1 cấp để đến gốc dự án
$script_dir = dirname($_SERVER['PHP_SELF']); // Should be /pages
$base_project_dir = dirname($script_dir); // Lùi 1 cấp
$base_url = $protocol . $domain . ($base_project_dir === '/' || $base_project_dir === '\\' ? '' : $base_project_dir);
$project_root_path = dirname(__DIR__); // Lùi 1 cấp từ /pages

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// --- Include Header ---
include $project_root_path . '/includes/header.php';

// ===============================================
// == DỮ LIỆU GIAO DỊCH GIẢ LẬP ==
// ===============================================
// Trong thực tế, lấy từ CSDL dựa trên user_id
$transactions = [
    [
        'id' => 'TXN1001',
        'transaction_code' => 'GD20240515A01',
        'date' => '2024-05-15 10:30:00',
        'package_name' => 'Gói 1 Năm',
        'quantity' => 1,
        'province' => 'Hà Nội',
        'amount' => 900000,
        'payment_method' => 'Chuyển khoản VietQR',
        'status' => 'completed', // 'completed', 'pending', 'failed', 'refunded'
        'description' => 'Thanh toán gia hạn gói Premium.',
        'invoice_available' => true,
        'payment_proof_uploaded' => true,
        'payment_proof_url' => '/path/to/proof/image1.jpg', // Link ảnh (nếu có)
        'related_account_id' => '12345',
    ],
    [
        'id' => 'TXN1002',
        'transaction_code' => 'GD20240514B02',
        'date' => '2024-05-14 15:00:00',
        'package_name' => 'Gói 3 Tháng',
        'quantity' => 2,
        'province' => 'TP Hồ Chí Minh',
        'amount' => 540000, // 270000 * 2
        'payment_method' => 'Chuyển khoản Ngân hàng',
        'status' => 'pending',
        'description' => 'Đang chờ xác nhận thanh toán thủ công.',
        'invoice_available' => false,
        'payment_proof_uploaded' => false,
        'payment_proof_url' => null,
        'related_account_id' => null,
    ],
    [
        'id' => 'TXN1003',
        'transaction_code' => 'GD20240420C03',
        'date' => '2024-04-20 08:15:00',
        'package_name' => 'Gói 1 Tháng',
        'quantity' => 1,
        'province' => 'Đà Nẵng',
        'amount' => 100000,
        'payment_method' => 'Thanh toán Online (Failed)',
        'status' => 'failed',
        'description' => 'Thanh toán không thành công do thẻ hết hạn.',
        'invoice_available' => false,
        'payment_proof_uploaded' => false,
        'payment_proof_url' => null,
        'related_account_id' => null,
    ],
     [
        'id' => 'TXN1004',
        'transaction_code' => 'GD20240310D04',
        'date' => '2024-03-10 11:00:00',
        'package_name' => 'Gói 6 Tháng',
        'quantity' => 1,
        'province' => 'Cần Thơ',
        'amount' => 500000,
        'payment_method' => 'Chuyển khoản VietQR',
        'status' => 'completed',
        'description' => 'Kích hoạt gói mới.',
        'invoice_available' => true,
        'payment_proof_uploaded' => true, // Giả sử đã upload
        'payment_proof_url' => '#', // Link ảnh thật
        'related_account_id' => '12344',
    ],
    // Thêm giao dịch khác nếu cần
];

// Hàm định dạng ngày giờ
function format_datetime_display($datetime_str) {
    if (!$datetime_str) return 'N/A';
    try {
        $date = new DateTime($datetime_str);
        return $date->format('H:i d-m-Y'); // Định dạng hh:mm dd-mm-yyyy
    } catch (Exception $e) {
        return 'N/A';
    }
}
?>

<!-- CSS cho Trang Quản Lý Giao Dịch -->
<style>
    /* --- Kế thừa các biến màu từ style.css hoặc định nghĩa lại --- */
    :root {
        --blue-500: #2196F3;
        --blue-600: #1976D2;
        --green-500: #4CAF50;
        --green-600: #388E3C;
        --green-bg-light: #e8f5e9;
        --green-text-dark: #2e7d32;
        --red-500: #F44336;
        --red-600: #D32F2F;
        --red-bg-light: #ffebee;
        --red-text-dark: #c62828;
        --orange-500: #FF9800;
        --orange-600: #F57C00;
        --orange-bg-light: #fff3e0;
        --orange-text-dark: #ef6c00;
        --gray-bg-light: #f5f5f5; /* Màu nền cho chi tiết */
    }

    /* --- Content Wrapper --- */
    /* .content-wrapper { padding: 1.5rem; } */

    /* --- Header Trang --- */
    .transaction-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-200);
        flex-wrap: wrap;
        gap: 1rem;
    }
    .transaction-page-header h2 {
         margin: 0;
         font-size: 1.75rem;
         font-weight: var(--font-semibold);
    }

    /* --- Filter & Search Section --- */
    .filter-search-section {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    .filter-tabs button {
        padding: 0.5rem 1.25rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--rounded-md);
        background-color: white;
        color: var(--gray-700);
        cursor: pointer;
        transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        font-size: var(--font-size-sm);
        margin-right: 0.5rem;
    }
    .filter-tabs button:last-child { margin-right: 0;}
    .filter-tabs button.active {
        background-color: var(--blue-500);
        color: white;
        border-color: var(--blue-500);
    }
    .filter-tabs button:hover:not(.active) { background-color: var(--gray-100); }
    .search-input {
        padding: 0.55rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--rounded-md);
        font-size: var(--font-size-sm);
        min-width: 250px;
        flex-grow: 1;
    }
    .search-input:focus {
        outline: none;
        border-color: var(--blue-500);
        box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
    }

    /* --- Grid Danh Sách Giao Dịch --- */
    .transactions-list {
        display: flex;
        flex-direction: column;
        gap: 1rem; /* Giảm khoảng cách giữa các card */
    }

    /* --- Card Giao Dịch --- */
    .transaction-card {
        background-color: white;
        border-radius: var(--rounded-lg);
        border: 1px solid var(--gray-200);
        /* box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04); */ /* Giảm shadow */
        overflow: hidden; /* Để border-left hiển thị đúng */
        position: relative;
        border-left-width: 4px; /* Dùng border-left thay vì border-top */
        border-left-style: solid;
        padding: 1rem 1.5rem; /* Điều chỉnh padding */
        display: grid;
        /* Layout 3 cột chính + 1 cột hành động */
        grid-template-columns: minmax(150px, 1fr) minmax(180px, 1.2fr) minmax(120px, 0.8fr) auto;
        gap: 1rem 1.5rem;
        align-items: center; /* Căn giữa các mục theo chiều dọc */
    }
    /* Màu border-left theo status */
    .transaction-card.status-completed { border-left-color: var(--green-500); }
    .transaction-card.status-pending { border-left-color: var(--orange-500); }
    .transaction-card.status-failed { border-left-color: var(--red-500); }
    .transaction-card.status-refunded { border-left-color: var(--gray-400); } /* Thêm nếu cần */

    /* --- Các khu vực trong Card --- */
    .card-section {
        display: flex;
        flex-direction: column;
        gap: 0.2rem; /* Khoảng cách nhỏ */
    }
     .card-section .label { /* Nhãn nhỏ */
        font-size: var(--font-size-xs);
        color: var(--gray-500);
        margin-bottom: 0.1rem;
        display: block;
    }
    .card-section .value { /* Giá trị chính */
        font-size: var(--font-size-sm);
        color: var(--gray-800);
        font-weight: var(--font-medium);
        line-height: 1.4;
    }
     .card-section .value.transaction-code {
        font-weight: var(--font-semibold);
        color: var(--gray-900);
    }
     .card-section .value.amount {
         color: var(--primary-600);
         font-weight: var(--font-semibold);
     }

    /* Trạng thái */
    .badge-status {
        padding: 0.3rem 0.8rem;
        border-radius: var(--rounded-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-semibold);
        display: inline-block;
        text-align: center;
        white-space: nowrap;
    }
    .status-completed { background-color: var(--green-bg-light); color: var(--green-text-dark); }
    .status-pending { background-color: var(--orange-bg-light); color: var(--orange-text-dark); }
    .status-failed { background-color: var(--red-bg-light); color: var(--red-text-dark); }
    .status-refunded { background-color: var(--gray-bg-light); color: var(--gray-600); }

    /* Khu vực Hành động */
    .card-actions {
        /* grid-column: 4 / 5; */ /* Tự động ở cột cuối */
        justify-self: end; /* Căn phải */
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap; /* Cho phép xuống dòng nếu không đủ chỗ */
        justify-content: flex-end;
    }
    .btn-action {
        padding: 0.35rem 0.8rem; /* Nút nhỏ hơn */
        border: 1px solid transparent;
        border-radius: var(--rounded-md);
        cursor: pointer;
        font-size: var(--font-size-xs); /* Chữ nhỏ hơn */
        font-weight: var(--font-medium);
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        text-decoration: none;
        display: inline-flex; /* Căn icon và text */
        align-items: center;
        gap: 0.3rem;
    }
    /* Style nút khác nhau */
    .btn-details { background-color: var(--blue-500); color: white; }
    .btn-details:hover { background-color: var(--blue-600); }

    .btn-upload-proof { background-color: white; color: var(--primary-500); border-color: var(--primary-500); }
    .btn-upload-proof:hover { background-color: var(--primary-50); }
    .btn-upload-proof.uploaded { /* Style khi đã upload */
        background-color: var(--green-bg-light);
        color: var(--green-text-dark);
        border-color: var(--green-bg-light);
        cursor: default; /* Hoặc link đến xem ảnh */
    }
    .btn-upload-proof.uploaded:hover { background-color: var(--green-bg-light); }


    .btn-invoice { background-color: var(--gray-600); color: white; }
    .btn-invoice:hover { background-color: var(--gray-700); }
    .btn-invoice:disabled,
    .btn-upload-proof:disabled { /* Style khi bị vô hiệu hóa */
        background-color: var(--gray-200);
        color: var(--gray-400);
        border-color: var(--gray-200);
        cursor: not-allowed;
    }

    /* --- Hidden File Input --- */
    .hidden-file-input {
        display: none;
    }

    /* --- Trạng thái trống --- */
    .empty-state { text-align: center; padding: 3rem; color: var(--gray-500); background-color: white; border-radius: var(--rounded-lg); }
    .empty-state h3 { color: var(--gray-700); margin-bottom: 0.5rem; }
    .empty-state p { margin-bottom: 1.5rem; }
    .buy-now-btn { /* Style lại nút mua */
        display: inline-block; padding: 0.75rem 1.5rem; background: var(--primary-500); color: white; text-decoration: none; border-radius: var(--rounded-md); transition: background 0.3s ease; font-weight: var(--font-semibold);
    }
    .buy-now-btn:hover { background: var(--primary-600); }


    /* --- Responsive --- */
    @media (max-width: 992px) {
        .transaction-card {
            grid-template-columns: repeat(2, 1fr) auto; /* 2 cột + hành động */
            padding: 1rem;
        }
        .card-actions {
           grid-column: 3 / 4; /* Đẩy sang cột 3 */
           flex-direction: column; /* Xếp dọc lại */
           align-items: flex-end;
        }
        /* Ẩn bớt label nếu cần */
        /* .card-section .label { display: none; } */
    }

     @media (max-width: 768px) {
        .transaction-card {
             grid-template-columns: 1fr auto; /* 1 cột nội dung + hành động */
             gap: 0.75rem 1rem;
        }
        .card-section {
            /* Có thể cần gộp các section lại */
            grid-column: 1 / 2; /* Tất cả nội dung vào cột 1 */
        }
        .card-actions {
            grid-column: 2 / 3; /* Hành động ở cột 2 */
            justify-self: end;
            align-self: center; /* Căn giữa dọc */
            flex-direction: column;
            align-items: flex-end;
        }
         .filter-search-section { flex-direction: column; align-items: stretch;}
         .filter-tabs { display: flex; flex-wrap: wrap; justify-content: center;}
         .filter-tabs button { flex-grow: 1; text-align: center; margin-bottom: 0.5rem;}
         .search-input { min-width: unset; width: 100%; }
         .transaction-page-header h2 {font-size: 1.5rem;}
     }
      @media (max-width: 480px) {
          .transaction-card {
            grid-template-columns: 1fr; /* 1 cột duy nhất */
            border-left-width: 3px;
            padding: 0.75rem 1rem;
          }
          .card-actions {
            grid-column: 1 / 2; /* Chiếm hết cột 1 */
            justify-self: stretch; /* Căn đều */
            flex-direction: row; /* Lại xếp ngang */
            justify-content: space-around; /* Phân bố đều nút */
            margin-top: 0.75rem;
            border-top: 1px solid var(--gray-100);
            padding-top: 0.75rem;
            gap: 0.3rem;
          }
          .btn-action { flex-grow: 1; font-size: var(--font-size-xs); }
      }

</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php include $project_root_path . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="content-wrapper" style="padding: 1.5rem;">

        <div class="transaction-page-header">
             <h2>Quản Lý Giao Dịch</h2>
             <!-- Có thể thêm nút như "Tải báo cáo" -->
        </div>

        <div class="filter-search-section">
            <div class="filter-tabs">
                <button class="filter-button active" data-filter="all">Tất cả</button>
                <button class="filter-button" data-filter="completed">Hoàn thành</button>
                <button class="filter-button" data-filter="pending">Đang xử lý</button>
                <button class="filter-button" data-filter="failed">Thất bại</button>
                <!-- <button class="filter-button" data-filter="refunded">Đã hoàn tiền</button> -->
            </div>
            <input type="text" class="search-input" id="transaction-search" placeholder="Tìm theo Mã GD, Gói, Ngày...">
        </div>

        <div class="transactions-list" id="transactions-list-container">
            <?php if (empty($transactions)): ?>
                <div class="empty-state">
                    <h3>Chưa có giao dịch nào</h3>
                    <p>Lịch sử giao dịch của bạn sẽ được hiển thị tại đây.</p>
                    <a href="<?php echo $base_url; ?>/pages/purchase/package.php" class="buy-now-btn">Mua Tài Khoản Ngay</a>
                </div>
            <?php else: ?>
                <?php foreach ($transactions as $txn): ?>
                    <?php
                        $status_class = 'status-' . $txn['status'];
                        // Chuẩn bị các giá trị tìm kiếm (chữ thường)
                        $search_terms = strtolower(
                            $txn['transaction_code'] . ' ' .
                            $txn['package_name'] . ' ' .
                            $txn['payment_method'] . ' ' .
                            format_datetime_display($txn['date']) . ' ' .
                            number_format($txn['amount'], 0, '', '') // Tìm theo số tiền không có dấu phẩy
                        );
                    ?>
                    <div class="transaction-card <?php echo $status_class; ?>" data-status="<?php echo $txn['status']; ?>" data-search-terms="<?php echo htmlspecialchars($search_terms); ?>" data-id="<?php echo $txn['id']; ?>">

                        <!-- Cột 1: Mã GD & Ngày -->
                        <div class="card-section">
                            <span class="label">Mã giao dịch</span>
                            <span class="value transaction-code"><?php echo htmlspecialchars($txn['transaction_code']); ?></span>
                            <span class="label" style="margin-top: 0.5rem;">Ngày tạo</span>
                            <span class="value"><?php echo format_datetime_display($txn['date']); ?></span>
                        </div>

                        <!-- Cột 2: Thông tin Gói & Số tiền -->
                        <div class="card-section">
                            <span class="label">Gói dịch vụ</span>
                            <span class="value"><?php echo htmlspecialchars($txn['package_name']); ?> (SL: <?php echo $txn['quantity']; ?>)</span>
                             <span class="label" style="margin-top: 0.5rem;">Tỉnh/Thành</span>
                             <span class="value"><?php echo htmlspecialchars($txn['province']); ?></span>
                             <span class="label" style="margin-top: 0.5rem;">Số tiền</span>
                            <span class="value amount"><?php echo number_format($txn['amount'], 0, ',', '.'); ?> đ</span>
                        </div>

                        <!-- Cột 3: Phương thức & Trạng thái -->
                         <div class="card-section">
                            <span class="label">Phương thức TT</span>
                            <span class="value"><?php echo htmlspecialchars($txn['payment_method']); ?></span>
                            <span class="label" style="margin-top: 0.5rem;">Trạng thái</span>
                            <span class="badge-status <?php echo $status_class; ?>">
                                <?php
                                    switch ($txn['status']) {
                                        case 'completed': echo 'Hoàn thành'; break;
                                        case 'pending': echo 'Đang xử lý'; break;
                                        case 'failed': echo 'Thất bại'; break;
                                        case 'refunded': echo 'Đã hoàn tiền'; break;
                                        default: echo ucfirst($txn['status']); break;
                                    }
                                ?>
                            </span>
                        </div>

                         <!-- Cột 4: Hành động -->
                         <div class="card-actions">
                              <button type="button" class="btn-action btn-details" title="Xem chi tiết giao dịch">
                                  <i class="fas fa-eye"></i> Chi tiết
                              </button>

                              <?php // Chỉ cho upload nếu đang chờ và chưa upload ?>
                              <button type="button"
                                      class="btn-action btn-upload-proof <?php echo $txn['payment_proof_uploaded'] ? 'uploaded' : ''; ?>"
                                      title="<?php echo $txn['payment_proof_uploaded'] ? 'Đã tải lên bằng chứng' : 'Tải lên bằng chứng thanh toán'; ?>"
                                      <?php echo ($txn['status'] !== 'pending' || $txn['payment_proof_uploaded']) ? 'disabled' : ''; ?>
                                      onclick="<?php echo !$txn['payment_proof_uploaded'] && $txn['status'] === 'pending' ? 'triggerUpload(\'' . $txn['id'] . '\')' : ''; ?>"
                              >
                                  <i class="fas <?php echo $txn['payment_proof_uploaded'] ? 'fa-check-circle' : 'fa-upload'; ?>"></i>
                                  <?php echo $txn['payment_proof_uploaded'] ? 'Đã Upload' : 'Upload TT'; ?>
                              </button>
                               <input type="file" id="file-input-<?php echo $txn['id']; ?>" class="hidden-file-input" accept="image/*" onchange="handleFileSelect(this, '<?php echo $txn['id']; ?>')">


                              <?php // Chỉ cho xuất hóa đơn nếu đã hoàn thành và có hóa đơn ?>
                              <button type="button"
                                      class="btn-action btn-invoice"
                                      title="Xuất hóa đơn điện tử"
                                      <?php echo ($txn['status'] !== 'completed' || !$txn['invoice_available']) ? 'disabled' : ''; ?>
                                      onclick="<?php echo ($txn['status'] === 'completed' && $txn['invoice_available']) ? 'downloadInvoice(\'' . $txn['id'] . '\')' : ''; ?>"
                                      >
                                  <i class="fas fa-file-invoice-dollar"></i> Hóa đơn
                              </button>
                         </div>

                    </div><!-- /.transaction-card -->
                <?php endforeach; ?>
            <?php endif; ?>
        </div><!-- /.transactions-list -->

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-button');
    const searchInput = document.getElementById('transaction-search');
    const transactionCards = document.querySelectorAll('.transaction-card');
    const transactionsListContainer = document.getElementById('transactions-list-container');
    const emptyStateHTML = `
        <div class="empty-state">
            <h3>Không tìm thấy giao dịch</h3>
            <p>Không có giao dịch nào khớp với tiêu chí lọc hoặc tìm kiếm của bạn.</p>
        </div>`;

    // --- Hàm Lọc và Tìm kiếm ---
    function filterAndSearchTransactions() {
        const activeFilter = document.querySelector('.filter-button.active').getAttribute('data-filter');
        const searchTerm = searchInput.value.toLowerCase().trim();
        let matchFound = false;

        transactionCards.forEach(card => {
            const status = card.getAttribute('data-status');
            const searchTerms = card.getAttribute('data-search-terms');

            const statusMatch = (activeFilter === 'all' || status === activeFilter);
            const searchMatch = (searchTerm === '' || searchTerms.includes(searchTerm));

            if (statusMatch && searchMatch) {
                card.style.display = 'grid'; // Hiện card (dùng grid vì display mặc định là grid)
                matchFound = true;
            } else {
                card.style.display = 'none'; // Ẩn card
            }
        });

        // Hiển thị trạng thái trống
        const currentEmptyState = transactionsListContainer.querySelector('.empty-state');
        if (!matchFound && !currentEmptyState) {
            transactionsListContainer.insertAdjacentHTML('beforeend', emptyStateHTML);
        } else if (matchFound && currentEmptyState) {
            currentEmptyState.remove();
        }
    }

    // --- Event Listener cho Nút Lọc ---
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterAndSearchTransactions();
        });
    });

    // --- Event Listener cho Ô Tìm kiếm ---
    searchInput.addEventListener('input', filterAndSearchTransactions);


    // --- Event Listener cho Nút Xem Chi Tiết (Placeholder) ---
    transactionsListContainer.addEventListener('click', function(event) {
        if (event.target.closest('.btn-details')) {
            const card = event.target.closest('.transaction-card');
            const transactionId = card.getAttribute('data-id');
            // Thay thế bằng logic thực tế (mở modal, hiển thị div ẩn,...)
            alert('Xem chi tiết giao dịch ID: ' + transactionId + '\n' +
                  'Mã GD: ' + card.querySelector('.transaction-code').textContent + '\n' +
                  'Gói: ' + card.querySelectorAll('.value')[1].textContent + '\n' + // Chỉ là ví dụ lấy text
                  'Số tiền: ' + card.querySelector('.amount').textContent + '\n' +
                  'Trạng thái: ' + card.querySelector('.badge-status').textContent.trim() + '\n' +
                  'Ngày: ' + card.querySelectorAll('.value')[3].textContent
                 );
        }
    });

});

// --- Hàm Placeholder cho Upload và Invoice ---
function triggerUpload(transactionId) {
    // Kích hoạt input file ẩn tương ứng
    const fileInput = document.getElementById(`file-input-${transactionId}`);
    if (fileInput) {
        fileInput.click(); // Mở hộp thoại chọn file
    } else {
        console.error('File input not found for transaction:', transactionId);
    }
}

function handleFileSelect(inputElement, transactionId) {
     const file = inputElement.files[0];
     if (file) {
        console.log('File selected for transaction:', transactionId, file.name, file.type, file.size);
        // --- Logic Upload thực tế sẽ ở đây ---
        // 1. Kiểm tra loại file, kích thước
        // 2. Tạo FormData
        // 3. Gửi AJAX request lên server để upload
        // 4. Xử lý kết quả trả về (thành công/lỗi)
        // 5. Cập nhật giao diện (ví dụ: đổi nút thành "Đã Upload")
        alert(`Giả lập: Đã chọn file "${file.name}" cho GD #${transactionId}. \nSẵn sàng để upload lên server.`);

         // Ví dụ cập nhật nút sau khi chọn file (chưa upload thực)
         const uploadButton = inputElement.closest('.transaction-card').querySelector('.btn-upload-proof');
         if(uploadButton){
             // uploadButton.classList.add('uploaded');
             // uploadButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang upload...'; // Ví dụ
             // uploadButton.disabled = true;
         }

     } else {
         console.log('No file selected for transaction:', transactionId);
     }
      // Reset input để có thể chọn lại cùng file nếu cần
      inputElement.value = null;
}


function downloadInvoice(transactionId) {
    // --- Logic Tạo và Tải Hóa Đơn thực tế sẽ ở đây ---
    // 1. Gửi yêu cầu AJAX lên server với transactionId
    // 2. Server kiểm tra quyền, tạo file hóa đơn (PDF,...)
    // 3. Server trả về link tải hoặc trực tiếp file
    alert('Chức năng Xuất Hóa Đơn cho GD #' + transactionId + ' chưa được cài đặt.');
    // Ví dụ chuyển hướng đến link tải (nếu server trả về link)
    // window.location.href = `/generate_invoice.php?txn_id=${transactionId}`;
}

</script>

<?php
// --- Include Footer ---
include $project_root_path . '/includes/footer.php';
?>