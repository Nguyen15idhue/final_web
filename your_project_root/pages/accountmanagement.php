<?php
session_start();

// --- Base URL và Path (Cần điều chỉnh cho phù hợp) ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
// File này nằm trong /pages/ => cần lùi lại 1 cấp để đến gốc dự án
$script_dir = dirname($_SERVER['PHP_SELF']); // Should be /pages
$base_project_dir = dirname($script_dir); // Lùi 1 cấp
$base_url = $protocol . $domain . ($base_project_dir === '/' || $base_project_dir === '\\' ? '' : $base_project_dir);
$project_root_path = dirname(__DIR__); // Lùi 1 cấp từ /pages

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    // Chuyển hướng về login (giả sử login ở gốc)
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// --- Include Header ---
// Giả sử header.php nằm trong thư mục includes ở gốc dự án
include $project_root_path . '/includes/header.php';

// ===============================================
// == DỮ LIỆU TÀI KHOẢN GIẢ LẬP ==
// ===============================================
// Trong thực tế, bạn sẽ lấy dữ liệu này từ cơ sở dữ liệu dựa trên $_SESSION['user_id']
// Thêm các trường mới: username, password (nên mã hóa/che), stations, start_date
$accounts = [
    [
        'id' => '12345',
        'username' => 'user_premium_1', // Tên tài khoản/đăng nhập
        'password' => '**********', // Mật khẩu (nên che hoặc có cơ chế hiển thị an toàn)
        'package_name' => 'Gói Nâng Cao',
        'duration_days' => 90,
        'status' => 'active', // 'active', 'expired', 'pending'
        'start_date' => '2024-04-14', // Ngày bắt đầu
        'end_date' => '2025-07-12', // Ngày kết thúc
        'stations' => ['Trạm A', 'Trạm B', 'Trạm C', 'Trạm D', 'Trạm E'], // Danh sách trạm
        'pending_info' => null,
    ],
    [
        'id' => '12344',
        'username' => 'user_basic_1',
        'password' => '**********',
        'package_name' => 'Gói Cơ Bản',
        'duration_days' => 30,
        'status' => 'expired',
        'start_date' => '2025-02-10',
        'end_date' => '2025-03-12',
        'stations' => ['Trạm X', 'Trạm Y'],
        'pending_info' => null,
    ],
    [
        'id' => '12346',
        'username' => 'user_corp_1',
        'password' => '**********',
        'package_name' => 'Gói Doanh Nghiệp',
        'duration_days' => 180,
        'status' => 'pending',
        'start_date' => null, // Chưa có ngày bắt đầu vì đang chờ
        'end_date' => null, // Chưa có ngày kết thúc
        'stations' => ['Trạm Z', 'Trạm W'],
        'pending_info' => 'Đang chờ xác nhận thanh toán. Thời gian chờ ước tính: 2 giờ',
    ],
    // Thêm tài khoản khác nếu cần
];

// Hàm tính toán ngày còn lại/quá hạn (ví dụ)
function calculate_days_diff($end_date_str) {
    if (!$end_date_str) return ['remaining' => null, 'expired' => null];
    try {
        $end_date = new DateTime($end_date_str);
        $now = new DateTime();
        $interval = $now->diff($end_date);
        $days = (int)$interval->format('%r%a'); // %r gives sign, %a total days

        if ($days >= 0) {
            return ['remaining' => $days, 'expired' => null];
        } else {
            return ['remaining' => null, 'expired' => abs($days)];
        }
    } catch (Exception $e) {
        return ['remaining' => null, 'expired' => null]; // Lỗi nếu ngày không hợp lệ
    }
}

// Hàm định dạng ngày
function format_date_display($date_str) {
    if (!$date_str) return 'N/A';
    try {
        $date = new DateTime($date_str);
        return $date->format('d-m-Y'); // Định dạng dd-mm-yyyy
    } catch (Exception $e) {
        return 'N/A';
    }
}
?>

<!-- CSS cho Trang Quản Lý Tài Khoản -->
<style>
    :root {
        /* Thêm các biến màu nếu chưa có trong style.css */
        --blue-500: #2196F3;
        --blue-600: #1976D2;
        --green-500: #4CAF50;
        --green-600: #388E3C;
        --green-bg-light: #e8f5e9;
        --green-text-dark: #2e7d32;
        --red-bg-light: #ffebee;
        --red-text-dark: #c62828;
        --orange-bg-light: #fff3e0;
        --orange-text-dark: #ef6c00;
    }

    /* --- Content Wrapper (Cần có trong CSS chung) --- */
    /* .content-wrapper { padding: 1.5rem; } */

    /* --- Header Thông tin phụ (Tùy chọn) --- */
    .account-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-200);
        flex-wrap: wrap; /* Cho phép xuống dòng trên mobile */
        gap: 1rem;
    }
    .account-page-header h2 {
         margin: 0; /* Reset margin của h2 */
         font-size: 1.75rem; /* --font-size-2xl */
         font-weight: var(--font-semibold);
    }

    /* --- Filter & Search Section --- */
    .filter-search-section {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap; /* Xuống dòng trên mobile */
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
        margin-right: 0.5rem; /* Khoảng cách giữa các tab */
    }
     .filter-tabs button:last-child { margin-right: 0;}

    .filter-tabs button.active {
        background-color: var(--blue-500); /* Màu xanh dương làm màu active */
        color: white;
        border-color: var(--blue-500);
    }
     .filter-tabs button:hover:not(.active) {
         background-color: var(--gray-100);
     }

    .search-input {
        padding: 0.55rem 1rem; /* Hơi cao hơn nút 1 chút */
        border: 1px solid var(--gray-300);
        border-radius: var(--rounded-md);
        font-size: var(--font-size-sm);
        min-width: 250px; /* Chiều rộng tối thiểu */
        flex-grow: 1; /* Cho phép ô search co giãn */
    }
     .search-input:focus {
        outline: none;
        border-color: var(--blue-500);
        box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
    }

    /* --- Grid Danh Sách Tài Khoản --- */
    .accounts-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem; /* Khoảng cách giữa các card */
    }

    /* --- Card Tài Khoản --- */
    .account-card {
        background-color: white;
        border-radius: var(--rounded-lg);
        border: 1px solid var(--gray-200);
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        display: grid;
        /* Responsive grid: 1 cột trên mobile, nhiều cột hơn khi đủ rộng */
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem 1.5rem; /* Khoảng cách dọc và ngang */
        align-items: start; /* Căn các mục lên trên */
    }
    /* Thêm border top màu mè theo status */
     .account-card.status-active { border-top: 3px solid var(--green-500); }
     .account-card.status-expired { border-top: 3px solid var(--red-text-dark); }
     .account-card.status-pending { border-top: 3px solid var(--orange-text-dark); }


    /* --- Các khu vực trong Card --- */
    .card-section {
        display: flex;
        flex-direction: column;
        gap: 0.3rem; /* Khoảng cách nhỏ giữa các dòng trong section */
    }
    .card-section strong { /* Tiêu đề nhỏ của section */
        font-weight: var(--font-semibold);
        color: var(--gray-800);
        font-size: 0.95rem; /* Hơi lớn hơn text thường */
        margin-bottom: 0.25rem;
        display: block;
    }
    .card-section p, .card-section span, .card-section ul {
        font-size: var(--font-size-sm);
        color: var(--gray-600);
        margin: 0;
        line-height: 1.5;
    }
    .card-section .password-field {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .card-section .password-field span { font-family: monospace; letter-spacing: 1px;}
    .card-section .password-field button {
        background: none; border: none; padding: 0; cursor: pointer; color: var(--blue-500); font-size: 0.8rem;
    }

    /* Danh sách trạm */
    .station-list {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 60px; /* Giới hạn chiều cao ban đầu */
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    .station-list.expanded {
        max-height: 500px; /* Chiều cao đủ lớn khi mở rộng */
    }
    .station-list li {
        background-color: var(--gray-100);
        padding: 0.2rem 0.5rem;
        border-radius: var(--rounded);
        margin-bottom: 0.3rem;
        display: inline-block; /* Hiển thị các trạm trên cùng dòng nếu đủ chỗ */
        margin-right: 0.3rem;
    }
    .toggle-stations {
        font-size: var(--font-size-xs);
        color: var(--blue-500);
        cursor: pointer;
        text-decoration: underline;
        margin-top: 0.3rem;
        display: inline-block;
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
    .status-active { background-color: var(--green-bg-light); color: var(--green-text-dark); }
    .status-expired { background-color: var(--red-bg-light); color: var(--red-text-dark); }
    .status-pending { background-color: var(--orange-bg-light); color: var(--orange-text-dark); }

    /* Khu vực Hành động */
    .card-actions {
        grid-column: -1 / -2; /* Đặt ở cột cuối cùng */
        justify-self: end; /* Căn phải */
        align-self: start; /* Căn trên */
        display: flex;
        flex-direction: column; /* Xếp nút dọc */
        gap: 0.5rem;
        align-items: flex-end; /* Căn các nút sang phải */
    }
    .btn-action {
        padding: 0.4rem 1rem;
        border: none;
        border-radius: var(--rounded-md);
        cursor: pointer;
        font-size: var(--font-size-sm);
        font-weight: var(--font-medium);
        transition: background-color 0.2s ease;
        text-decoration: none; /* Cho thẻ <a> */
        display: inline-block;
        text-align: center;
        min-width: 100px; /* Chiều rộng tối thiểu cho nút */
    }
    .btn-view { background-color: var(--blue-500); color: white; }
    .btn-view:hover { background-color: var(--blue-600); }
    .btn-renew { background-color: var(--green-500); color: white; }
    .btn-renew:hover { background-color: var(--green-600); }

    /* Trạng thái trống */
    .empty-state { text-align: center; padding: 3rem; color: var(--gray-500); background-color: white; border-radius: var(--rounded-lg); }
    .empty-state h3 { color: var(--gray-700); margin-bottom: 0.5rem; }
    .empty-state p { margin-bottom: 1.5rem; }
    .buy-now-btn { /* Style lại nút mua */
        display: inline-block; padding: 0.75rem 1.5rem; background: var(--primary-500); color: white; text-decoration: none; border-radius: var(--rounded-md); transition: background 0.3s ease; font-weight: var(--font-semibold);
    }
    .buy-now-btn:hover { background: var(--primary-600); }

    /* Responsive cho Card */
    @media (max-width: 992px) { /* Điều chỉnh breakpoint nếu cần */
        .account-card {
             /* Có thể giữ nhiều cột hoặc chuyển về 1 cột tùy ý */
             grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
         .card-actions {
            grid-column: auto; /* Reset vị trí cột */
            justify-self: start; /* Căn trái trên màn hình nhỏ hơn */
            align-self: end; /* Căn cuối section */
            flex-direction: row; /* Xếp nút ngang */
            margin-top: 1rem; /* Thêm khoảng cách trên */
            width: 100%; /* Chiếm hết chiều rộng */
            justify-content: flex-start; /* Căn nút sang trái */
         }
    }
     @media (max-width: 576px) {
         .account-card {
            grid-template-columns: 1fr; /* Chỉ 1 cột trên mobile */
            padding: 1rem;
        }
        .card-actions {
            flex-direction: column; /* Lại xếp dọc trên mobile nhỏ */
            align-items: stretch; /* Nút chiếm hết chiều rộng */
        }
        .btn-action { width: 100%; } /* Nút full width */
        .filter-search-section { flex-direction: column; align-items: stretch;}
        .filter-tabs { display: flex; flex-wrap: wrap; justify-content: center;}
        .filter-tabs button { flex-grow: 1; text-align: center; margin-bottom: 0.5rem;}
        .search-input { min-width: unset; width: 100%; }
        .account-page-header h2 {font-size: 1.5rem;}

     }

</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php // Giả sử sidebar.php nằm trong thư mục includes ở gốc
          include $project_root_path . '/includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="content-wrapper" style="padding: 1.5rem;"> <!-- Thêm padding trực tiếp nếu cần -->

        <div class="account-page-header">
             <h2>Quản Lý Tài Khoản</h2>
             <!-- Có thể thêm các thông tin khác hoặc nút hành động chung ở đây -->
        </div>


        <div class="filter-search-section">
            <div class="filter-tabs">
                <button class="filter-button active" data-filter="all">Tất cả</button>
                <button class="filter-button" data-filter="active">Đang hoạt động</button>
                <button class="filter-button" data-filter="expired">Hết hạn</button>
                <button class="filter-button" data-filter="pending">Đang xử lý</button>
            </div>
            <input type="text" class="search-input" id="account-search" placeholder="Tìm kiếm theo ID, Tên TK, Tên trạm...">
        </div>

        <div class="accounts-list" id="accounts-list-container">
            <?php if (empty($accounts)): ?>
                <div class="empty-state">
                    <h3>Chưa có tài khoản nào</h3>
                    <p>Bạn chưa đăng ký hoặc mua tài khoản nào. Hãy bắt đầu ngay!</p>
                    <a href="<?php echo $base_url; ?>/pages/purchase/package.php" class="buy-now-btn">Mua Tài Khoản Ngay</a>
                </div>
            <?php else: ?>
                <?php foreach ($accounts as $account): ?>
                    <?php
                        $status_class = 'status-' . $account['status']; // Ví dụ: status-active
                        $days_diff = calculate_days_diff($account['end_date']);
                        $account_id_display = $account['id']; // Hoặc 'Premium Account #'.$account['id']
                        $max_stations_visible = 3; // Số lượng trạm hiển thị ban đầu
                        $total_stations = count($account['stations']);
                        $needs_toggle = $total_stations > $max_stations_visible;
                    ?>
                    <div class="account-card <?php echo $status_class; ?>" data-status="<?php echo $account['status']; ?>" data-search-terms="<?php echo htmlspecialchars(strtolower($account['id'] . ' ' . $account['username'] . ' ' . implode(' ', $account['stations']))); ?>">
                        <!-- Section 1: Thông tin cơ bản -->
                        <div class="card-section">
                            <strong>Tài khoản #<?php echo htmlspecialchars($account_id_display); ?></strong>
                            <p title="Tên đăng nhập">TK: <?php echo htmlspecialchars($account['username']); ?></p>
                            <div class="password-field">
                                MK: <span data-password="<?php echo htmlspecialchars($account['password']); ?>">**********</span>
                                <button type="button" class="toggle-password" aria-label="Hiện/Ẩn mật khẩu">Hiện</button>
                            </div>
                             <p>Gói: <?php echo htmlspecialchars($account['package_name']); ?> (<?php echo $account['duration_days']; ?> ngày)</p>
                        </div>

                        <!-- Section 2: Trạng thái & Ngày -->
                        <div class="card-section">
                             <strong>Trạng thái & Thời hạn</strong>
                            <p>
                                <span class="badge-status <?php echo $status_class; ?>">
                                    <?php
                                        switch ($account['status']) {
                                            case 'active': echo 'Đang hoạt động'; break;
                                            case 'expired': echo 'Đã hết hạn'; break;
                                            case 'pending': echo 'Đang xử lý'; break;
                                            default: echo ucfirst($account['status']); break;
                                        }
                                    ?>
                                </span>
                            </p>
                             <p>Bắt đầu: <?php echo format_date_display($account['start_date']); ?></p>
                             <p>Kết thúc: <?php echo format_date_display($account['end_date']); ?></p>
                            <?php if ($account['status'] === 'active' && $days_diff['remaining'] !== null): ?>
                                <p>Còn lại: <?php echo $days_diff['remaining']; ?> ngày</p>
                            <?php elseif ($account['status'] === 'expired' && $days_diff['expired'] !== null): ?>
                                <p style="color: var(--red-text-dark);">Quá hạn: <?php echo $days_diff['expired']; ?> ngày</p>
                             <?php elseif ($account['status'] === 'pending' && $account['pending_info']): ?>
                                <p style="color: var(--orange-text-dark);"><?php echo htmlspecialchars($account['pending_info']); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Section 3: Danh sách trạm -->
                        <div class="card-section">
                            <strong>Danh sách Trạm (<?php echo $total_stations; ?>)</strong>
                            <ul class="station-list" id="stations-<?php echo $account['id']; ?>">
                                <?php foreach (array_slice($account['stations'], 0, $max_stations_visible) as $station): ?>
                                    <li><?php echo htmlspecialchars($station); ?></li>
                                <?php endforeach; ?>
                                <?php // Add hidden stations for expansion ?>
                                <?php if ($needs_toggle): ?>
                                    <?php foreach (array_slice($account['stations'], $max_stations_visible) as $station): ?>
                                        <li style="display: none;"><?php echo htmlspecialchars($station); ?></li>
                                     <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                             <?php if ($needs_toggle): ?>
                                <span class="toggle-stations" data-target="#stations-<?php echo $account['id']; ?>">Hiện thêm</span>
                            <?php endif; ?>
                        </div>

                         <!-- Section 4: Hành động -->
                         <div class="card-actions">
                              <button type="button" class="btn-action btn-view" data-account-id="<?php echo $account['id']; ?>">Xem chi tiết</button>
                              <?php if ($account['status'] !== 'pending'): // Chỉ hiện nút gia hạn nếu không phải đang chờ ?>
                                <a href="<?php echo $base_url; ?>/pages/purchase/renew.php?account_id=<?php echo $account['id']; ?>" class="btn-action btn-renew">Gia hạn</a>
                             <?php endif; ?>
                         </div>

                    </div><!-- /.account-card -->
                <?php endforeach; ?>
            <?php endif; ?>
        </div><!-- /.accounts-list -->

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-button');
    const searchInput = document.getElementById('account-search');
    const accountCards = document.querySelectorAll('.account-card');
    const accountsListContainer = document.getElementById('accounts-list-container');
    const emptyStateHTML = `
        <div class="empty-state">
            <h3>Không tìm thấy tài khoản</h3>
            <p>Không có tài khoản nào khớp với tiêu chí lọc hoặc tìm kiếm của bạn.</p>
        </div>`;

    // --- Hàm Lọc và Tìm kiếm ---
    function filterAndSearchAccounts() {
        const activeFilter = document.querySelector('.filter-button.active').getAttribute('data-filter');
        const searchTerm = searchInput.value.toLowerCase().trim();
        let matchFound = false;

        accountCards.forEach(card => {
            const status = card.getAttribute('data-status');
            const searchTerms = card.getAttribute('data-search-terms'); // Lấy dữ liệu search đã chuẩn bị sẵn

            const statusMatch = (activeFilter === 'all' || status === activeFilter);
            const searchMatch = (searchTerm === '' || searchTerms.includes(searchTerm));

            if (statusMatch && searchMatch) {
                card.style.display = ''; // Hiện card
                matchFound = true;
            } else {
                card.style.display = 'none'; // Ẩn card
            }
        });

        // Hiển thị trạng thái trống nếu không tìm thấy kết quả
        const currentEmptyState = accountsListContainer.querySelector('.empty-state');
        if (!matchFound && !currentEmptyState) {
            accountsListContainer.insertAdjacentHTML('beforeend', emptyStateHTML);
        } else if (matchFound && currentEmptyState) {
            currentEmptyState.remove();
        } else if (!matchFound && currentEmptyState) {
            // Already showing empty state, do nothing
        }

    }

    // --- Event Listener cho Nút Lọc ---
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterAndSearchAccounts();
        });
    });

    // --- Event Listener cho Ô Tìm kiếm ---
    searchInput.addEventListener('input', filterAndSearchAccounts);

    // --- Event Listener cho Hiện/Ẩn Mật khẩu ---
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const passwordSpan = this.previousElementSibling; // Lấy thẻ span chứa mật khẩu
            const actualPassword = passwordSpan.getAttribute('data-password');
            if (passwordSpan.textContent === '**********') {
                passwordSpan.textContent = actualPassword;
                this.textContent = 'Ẩn';
            } else {
                passwordSpan.textContent = '**********';
                this.textContent = 'Hiện';
            }
        });
    });

    // --- Event Listener cho Hiện/Ẩn Danh sách Trạm ---
    document.querySelectorAll('.toggle-stations').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetSelector = this.getAttribute('data-target');
            const stationList = document.querySelector(targetSelector);
            if (stationList) {
                 const hiddenItems = stationList.querySelectorAll('li[style*="display: none"]');
                if (stationList.classList.contains('expanded')) {
                    // Thu gọn
                    stationList.classList.remove('expanded');
                    hiddenItems.forEach(item => item.style.display = 'none'); // Ẩn lại
                    this.textContent = 'Hiện thêm';
                } else {
                    // Mở rộng
                    stationList.classList.add('expanded');
                    hiddenItems.forEach(item => item.style.display = 'inline-block'); // Hiện ra
                    this.textContent = 'Ẩn bớt';
                }
            }
        });
    });


    // --- Event Listener cho Nút Xem Chi Tiết (Placeholder) ---
    document.querySelectorAll('.btn-view').forEach(button => {
        button.addEventListener('click', function() {
            const accountId = this.getAttribute('data-account-id');
            // Thay thế bằng logic thực tế (ví dụ: mở modal, chuyển trang)
            alert('Xem chi tiết tài khoản #' + accountId);
            // window.location.href = `<?php echo $base_url; ?>/pages/account_details.php?id=${accountId}`;
        });
    });

     // --- Event Listener cho Nút Gia Hạn (Chuyển trang) ---
     // Đã xử lý bằng thẻ <a> với href đúng

});
</script>

<?php
// --- Include Footer ---
include $project_root_path . '/includes/footer.php';
?>