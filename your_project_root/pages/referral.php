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
    header('Location: ' . $base_url . '/login.php'); // Chuyển hướng về login
    exit;
}

// --- User Info ---
$user_username = $_SESSION['username'] ?? 'Người dùng';
$user_id = $_SESSION['user_id'];

// --- KIỂM TRA TRẠNG THÁI NGƯỜI GIỚI THIỆU (GIẢ LẬP) ---
$is_referrer = true; // <-- **ĐẶT LÀ true ĐỂ DEMO TRANG DASHBOARD**

// --- DỮ LIỆU GIỚI THIỆU (GIẢ LẬP NẾU LÀ REFERRER) ---
$referral_code = null;
$referral_link = null;
$referral_stats = [ 'clicks' => 0, 'signups' => 0, 'pending_commission' => 0, 'paid_commission' => 0 ];
$referral_history = [];

// --- Include Header ---
include $project_root_path . '/includes/header.php';

// ====> THÊM DÒNG NÀY ĐỂ INCLUDE FILE HÀM <====
include $project_root_path . '/config/functions.php'; // Sửa thành '/config/'


if ($is_referrer) {
    // Lấy mã giới thiệu (ví dụ: từ CSDL)
    $referral_code = 'REF' . $user_id . 'XYZ'; // Tạo mã giả lập
    $referral_link = $base_url . '/register.php?ref=' . urlencode($referral_code); // Link đăng ký với mã ref

    // Lấy dữ liệu thống kê (ví dụ: từ CSDL)
    $referral_stats = [
        'clicks' => 152,
        'signups' => 25, // Số người đăng ký thành công qua link
        'pending_commission' => 550000, // Hoa hồng chờ thanh toán
        'paid_commission' => 1200000, // Hoa hồng đã thanh toán
    ];

    // Lấy lịch sử giới thiệu (ví dụ: từ CSDL)
     $referral_history = [
         ['user' => 'User A', 'signup_date' => '2024-07-10', 'package' => 'Gói 1 Năm', 'commission' => 100000, 'status' => 'paid'],
         ['user' => 'User B', 'signup_date' => '2024-07-12', 'package' => 'Gói 3 Tháng', 'commission' => 30000, 'status' => 'pending'],
         ['user' => 'User C', 'signup_date' => '2024-07-15', 'package' => 'Gói 6 Tháng', 'commission' => 50000, 'status' => 'pending'],
     ];
}

?>

<style>
    /* ... CSS giữ nguyên như trước ... */
    /* Kế thừa style chung */
    .content-wrapper { padding-top: 1rem; } /* Giảm padding top mặc định */

    /* --- Phần Đăng Ký Làm Người Giới Thiệu --- */
    .referral-signup-section {
        background: white;
        padding: 2.5rem;
        border-radius: var(--rounded-lg);
        text-align: center;
        max-width: 700px;
        margin: 2rem auto;
        border: 1px solid var(--gray-200);
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }
    .referral-signup-section h3 {
        font-size: 1.75rem; /* --font-size-xl hoặc lớn hơn */
        font-weight: var(--font-semibold);
        color: var(--primary-600);
        margin-bottom: 1rem;
    }
    .referral-signup-section p {
        color: var(--gray-600);
        margin-bottom: 2rem;
        line-height: 1.6;
    }
    .btn-signup-referral {
        padding: 0.8rem 2rem;
        background-color: var(--primary-500);
        color: white;
        border: none;
        border-radius: var(--rounded-md);
        font-weight: var(--font-semibold);
        cursor: pointer;
        transition: background-color 0.2s ease;
        font-size: var(--font-size-base);
    }
    .btn-signup-referral:hover { background-color: var(--primary-600); }

    /* --- Phần Dashboard Người Giới Thiệu --- */
    .referral-dashboard { } /* Container chung */

    /* Link giới thiệu */
    .referral-link-section {
        background: linear-gradient(135deg, var(--primary-500), var(--primary-700));
        color: white;
        padding: 2rem;
        border-radius: var(--rounded-lg);
        margin-bottom: 2rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2);
    }
    .referral-link-section h3 {
        font-size: var(--font-size-lg);
        margin-bottom: 1rem;
        font-weight: var(--font-semibold);
    }
    .referral-link-display {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.75rem 1rem;
        border-radius: var(--rounded-md);
        max-width: 500px; /* Giới hạn độ rộng */
        margin: 0 auto; /* Căn giữa */
    }
    .referral-link-display input[type="text"] {
        flex-grow: 1;
        border: none;
        background: transparent;
        color: white;
        font-size: var(--font-size-base);
        font-family: monospace; /* Dùng font monospace cho link */
        outline: none;
        padding: 0.2rem 0;
    }
    .btn-copy-link {
        padding: 0.4rem 0.8rem;
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary-600);
        border: none;
        border-radius: var(--rounded-md);
        cursor: pointer;
        font-size: var(--font-size-xs);
        font-weight: var(--font-medium);
        white-space: nowrap;
        transition: background 0.2s ease;
    }
    .btn-copy-link:hover { background: white; }
    .copy-feedback { /* Thông báo đã copy */
        display: inline-block;
        margin-left: 0.5rem;
        font-size: var(--font-size-xs);
        color: var(--primary-100);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .copy-feedback.show { opacity: 1; }


    /* Stats Grid - Kế thừa hoặc định nghĩa lại */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Điều chỉnh minmax nếu cần */
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    /* Stat Card - Kế thừa hoặc định nghĩa lại */
    .stat-card {
        padding: 1.5rem;
        background: white;
        border-radius: var(--rounded-md);
        border: 1px solid var(--gray-200);
        transition: transform 0.2s ease;
        text-align: center; /* Căn giữa nội dung card */
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
    .stat-card .icon {
        font-size: 1.75rem; /* Kích thước icon lớn hơn */
        margin-bottom: 0.75rem;
        color: var(--primary-500); /* Màu icon mặc định */
    }
    /* Màu icon cụ thể nếu muốn */
    .stat-card .icon.clicks { color: var(--primary-500); }
    .stat-card .icon.signups { color: var(--badge-green-text); }
    .stat-card .icon.pending { color: var(--badge-yellow-text); }
    .stat-card .icon.paid { color: var(--badge-blue-text); }

    .stat-card h4 { /* Dùng h4 thay vì h3 */
        color: var(--gray-600);
        font-size: var(--font-size-sm);
        font-weight: var(--font-medium);
        margin-bottom: 0.5rem;
    }
    .stat-card .value {
        font-size: 1.75rem; /* Cỡ chữ giá trị */
        font-weight: var(--font-semibold);
        color: var(--gray-900);
    }
    .stat-card .value.currency { /* Định dạng tiền tệ */
        font-size: 1.5rem; /* Nhỏ hơn chút */
    }

    /* --- Lịch Sử Giới Thiệu --- */
    .referral-history {
        background: white;
        border-radius: var(--rounded-lg);
        padding: 1.5rem;
        border: 1px solid var(--gray-200);
    }
    .referral-history h3 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-semibold);
        color: var(--gray-800);
        margin-bottom: 1rem;
    }
    /* Style cho bảng lịch sử (Tương tự bảng transactions) */
    .referral-history-table { width: 100%; border-collapse: collapse; }
    .referral-history-table th, .referral-history-table td { padding: 0.8rem 1rem; text-align: left; border-bottom: 1px solid var(--gray-200); font-size: var(--font-size-sm); }
    .referral-history-table th { background-color: var(--gray-50); font-weight: var(--font-semibold); color: var(--gray-600); }
    .referral-history-table tr:last-child td { border-bottom: none; }
    .referral-history-table td.commission { font-weight: var(--font-medium); }
    /* Sử dụng lại các class status badge từ transactions.css hoặc style.css */
    .status-badge { padding: 0.3rem 0.8rem; border-radius: var(--rounded-full); font-size: 0.8rem; display: inline-block; font-weight: var(--font-medium); text-align: center; min-width: 80px; }
    .status-completed { background: var(--badge-green-bg); color: var(--badge-green-text); }
    .status-pending { background: var(--badge-yellow-bg); color: var(--badge-yellow-text); }
    /* Thêm các class status khác nếu cần */

    /* Responsive */
    @media (max-width: 768px) {
        .content-wrapper { padding: 1rem !important; }
        .referral-signup-section { padding: 1.5rem; }
        .referral-link-section { padding: 1.5rem; }
        .referral-link-display { flex-direction: column; gap: 0.8rem; padding: 1rem; }
        .referral-link-display input[type="text"] { text-align: center; }
        .stats-grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; } /* Điều chỉnh grid stats */
        .stat-card { padding: 1rem; }
        .stat-card .value { font-size: 1.5rem; }
        .stat-card .value.currency { font-size: 1.3rem; }
        .referral-history { padding: 1rem; }
        .referral-history-table th, .referral-history-table td { padding: 0.6rem 0.5rem; }
        /* Ẩn bớt cột lịch sử nếu cần */
        .referral-history-table th:nth-child(3),
        .referral-history-table td:nth-child(3) { display: none; }
    }
</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php include $project_root_path . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="content-wrapper">
        <h2 class="text-2xl font-semibold mb-5">Chương Trình Giới Thiệu</h2>

        <?php if ($is_referrer): // --- HIỂN THỊ NẾU LÀ NGƯỜI GIỚI THIỆU --- ?>

            <div class="referral-dashboard">

                <!-- Phần Link Giới Thiệu -->
                <section class="referral-link-section">
                     <h3>Link Giới Thiệu Của Bạn</h3>
                    <div class="referral-link-display">
                        <input type="text" id="referral-link-input" value="<?php echo htmlspecialchars($referral_link); ?>" readonly>
                        <button class="btn-copy-link" id="copy-link-button">
                            <i class="fas fa-copy" style="margin-right: 4px;"></i> Sao chép
                        </button>
                        <span class="copy-feedback" id="copy-feedback">Đã sao chép!</span>
                    </div>
                    <p style="font-size: var(--font-size-sm); margin-top: 1rem; opacity: 0.8;">
                        Chia sẻ liên kết này để nhận hoa hồng khi có người đăng ký và mua gói.
                    </p>
                </section>

                <!-- Phần Thống Kê -->
                <section class="stats-grid">
                     <div class="stat-card">
                        <i class="icon fas fa-mouse-pointer clicks"></i>
                        <h4>Lượt nhấp vào link</h4>
                        <p class="value"><?php echo number_format($referral_stats['clicks']); ?></p>
                    </div>
                    <div class="stat-card">
                        <i class="icon fas fa-user-check signups"></i>
                        <h4>Đăng ký thành công</h4>
                        <p class="value"><?php echo number_format($referral_stats['signups']); ?></p>
                    </div>
                    <div class="stat-card">
                        <i class="icon fas fa-wallet pending"></i>
                        <h4>Hoa hồng chờ duyệt</h4>
                        <p class="value currency"><?php echo format_currency($referral_stats['pending_commission']); // Sử dụng hàm format_currency ?></p>
                    </div>
                    <div class="stat-card">
                         <i class="icon fas fa-hand-holding-usd paid"></i>
                        <h4>Hoa hồng đã nhận</h4>
                        <p class="value currency"><?php echo format_currency($referral_stats['paid_commission']); // Sử dụng hàm format_currency ?></p>
                    </div>
                </section>

                <!-- Phần Lịch Sử Giới Thiệu (Ví dụ) -->
                <section class="referral-history">
                    <h3>Lịch Sử Giới Thiệu Thành Công</h3>
                     <div class="table-wrapper" style="overflow-x: auto;">
                         <table class="referral-history-table">
                             <thead>
                                 <tr>
                                     <th>Người được giới thiệu</th>
                                     <th>Ngày đăng ký</th>
                                     <th>Gói đã mua</th>
                                     <th>Hoa hồng</th>
                                     <th style="text-align: center;">Trạng thái</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php if (!empty($referral_history)): ?>
                                     <?php foreach ($referral_history as $item): ?>
                                          <?php // Sử dụng hàm từ functions.php
                                               // Chuyển 'paid' thành 'completed' cho hàm helper nếu cần
                                               $display_status_key = ($item['status'] === 'paid') ? 'completed' : $item['status'];
                                               $status_display = get_transaction_status_display($display_status_key);
                                          ?>
                                         <tr>
                                             <td><?php echo htmlspecialchars($item['user']); ?></td>
                                             <td><?php echo htmlspecialchars($item['signup_date']); ?></td>
                                             <td><?php echo htmlspecialchars($item['package']); ?></td>
                                             <td class="commission"><?php echo format_currency($item['commission']); // Sử dụng hàm format_currency ?></td>
                                             <td class="status" style="text-align: center;">
                                                  <span class="status-badge <?php echo $status_display['class']; ?>">
                                                      <?php echo ($item['status'] === 'paid') ? 'Đã trả' : 'Chờ duyệt'; // Text riêng cho trạng thái hoa hồng ?>
                                                 </span>
                                             </td>
                                         </tr>
                                     <?php endforeach; ?>
                                 <?php else: ?>
                                     <tr>
                                         <td colspan="5" style="text-align: center; padding: 2rem;">Chưa có lượt giới thiệu thành công nào.</td>
                                     </tr>
                                 <?php endif; ?>
                             </tbody>
                         </table>
                    </div>
                    <!-- Thêm phân trang nếu cần -->
                </section>

            </div> <!-- /.referral-dashboard -->

        <?php else: // --- HIỂN THỊ NẾU CHƯA LÀ NGƯỜI GIỚI THIỆU --- ?>

             <section class="referral-signup-section">
                <h3>Trở thành Đối Tác Giới Thiệu Của Chúng Tôi!</h3>
                <p>
                    Bạn yêu thích dịch vụ của chúng tôi? Hãy chia sẻ với bạn bè và nhận hoa hồng hấp dẫn
                    cho mỗi lượt giới thiệu thành công. Đăng ký ngay để nhận link giới thiệu và bắt đầu kiếm tiền!
                </p>
                <form action="/path/to/process_referral_signup.php" method="POST">
                    <!-- Có thể thêm các trường thông tin nếu cần, ví dụ: tài khoản ngân hàng -->
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                    <button type="submit" class="btn-signup-referral">
                        <i class="fas fa-user-plus" style="margin-right: 8px;"></i> Đăng Ký Làm Người Giới Thiệu
                    </button>
                </form>
                 <p style="font-size: var(--font-size-sm); color: var(--gray-500); margin-top: 1.5rem;">
                    Việc đăng ký là hoàn toàn miễn phí. <a href="/referral-terms" style="color: var(--primary-500);">Xem điều khoản chương trình</a>.
                </p>
            </section>

        <?php endif; ?>

    </main> <!-- /.content-wrapper -->
</div> <!-- /.dashboard-wrapper -->

<!-- JavaScript cho chức năng Sao chép -->
<script>
    /* ... JavaScript giữ nguyên như trước ... */
    document.addEventListener('DOMContentLoaded', function() {
    const copyButton = document.getElementById('copy-link-button');
    const linkInput = document.getElementById('referral-link-input');
    const copyFeedback = document.getElementById('copy-feedback');

    if (copyButton && linkInput && copyFeedback) {
        copyButton.addEventListener('click', function() {
            // Chọn text trong input
            linkInput.select();
            linkInput.setSelectionRange(0, 99999); // For mobile devices

            try {
                // Thử copy bằng API Clipboard hiện đại
                navigator.clipboard.writeText(linkInput.value)
                    .then(() => {
                        // Hiển thị thông báo thành công
                        copyFeedback.classList.add('show');
                        setTimeout(() => {
                             copyFeedback.classList.remove('show');
                        }, 2000); // Ẩn sau 2 giây
                    })
                    .catch(err => {
                        console.error('Lỗi tự động sao chép: ', err);
                        // Fallback cho trình duyệt cũ (ít tin cậy hơn)
                        try {
                            document.execCommand('copy');
                            copyFeedback.classList.add('show');
                            setTimeout(() => { copyFeedback.classList.remove('show'); }, 2000);
                        } catch (execErr) {
                             console.error('Lỗi fallback sao chép: ', execErr);
                             alert('Không thể tự động sao chép. Vui lòng sao chép thủ công.');
                        }
                    });
            } catch (err) {
                 console.error('API Clipboard không được hỗ trợ hoặc lỗi: ', err);
                 alert('Không thể tự động sao chép. Vui lòng sao chép thủ công.');
            }

            // Bỏ chọn text sau khi copy
            window.getSelection().removeAllRanges();
        });
    }
});
</script>

<?php
// --- Include Footer ---
include $project_root_path . '/includes/footer.php';
?>