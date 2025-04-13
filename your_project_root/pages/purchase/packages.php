<?php
session_start();

// --- Base URL Configuration ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
// File này nằm trong /pages/purchase/ => cần lùi lại 2 cấp để đến gốc dự án
$script_dir = dirname($_SERVER['PHP_SELF']); // Should be /pages/purchase
$base_project_dir = dirname(dirname($script_dir)); // Lùi 2 cấp
$base_url = $protocol . $domain . ($base_project_dir === '/' || $base_project_dir === '\\' ? '' : $base_project_dir);

// --- Project Root Path for Includes ---
$project_root_path = dirname(dirname(__DIR__)); // Lùi 2 cấp từ thư mục chứa file này (purchase)

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . '/login.php'); // Chuyển hướng về login ở gốc
    exit;
}

// --- User Info (Example) ---
$user_username = $_SESSION['username'] ?? 'Người dùng';

// ===============================================
// == ĐỊNH NGHĨA DỮ LIỆU CÁC GÓI ==
// ===============================================
// Trong thực tế, dữ liệu này nên được lấy từ cơ sở dữ liệu hoặc file cấu hình
$all_packages = [
    'monthly' => [
        'id' => 'monthly', // Mã định danh dùng cho URL và logic
        'name' => 'Gói 1 Tháng',
        'price' => 100000,
        'duration_text' => '/ tháng',
        'features' => [
            ['icon' => 'fa-check', 'text' => 'Truy cập đầy đủ tính năng', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Hỗ trợ cơ bản', 'available' => true],
            ['icon' => 'fa-check', 'text' => '10 lượt đo đạc / ngày', 'available' => true],
            ['icon' => 'fa-times', 'text' => 'Không ưu tiên hỗ trợ', 'available' => false],
        ],
        'recommended' => false,
        'button_text' => 'Chọn Gói'
    ],
    'quarterly' => [
        'id' => 'quarterly',
        'name' => 'Gói 3 Tháng',
        'price' => 270000,
        'duration_text' => '/ 3 tháng',
        // 'savings_text' => '(Tiết kiệm 10%)', // Tùy chọn thêm text tiết kiệm
        'features' => [
            ['icon' => 'fa-check', 'text' => 'Truy cập đầy đủ tính năng', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Hỗ trợ cơ bản', 'available' => true],
            ['icon' => 'fa-check', 'text' => '15 lượt đo đạc / ngày', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Ưu tiên hỗ trợ thấp', 'available' => true],
        ],
        'recommended' => false,
        'button_text' => 'Chọn Gói'
    ],
    'biannual' => [
        'id' => 'biannual',
        'name' => 'Gói 6 Tháng',
        'price' => 500000,
        'duration_text' => '/ 6 tháng',
        // 'savings_text' => '(Tiết kiệm ~17%)',
        'features' => [
            ['icon' => 'fa-check', 'text' => 'Truy cập đầy đủ tính năng', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Hỗ trợ tiêu chuẩn', 'available' => true],
            ['icon' => 'fa-check', 'text' => '25 lượt đo đạc / ngày', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Ưu tiên hỗ trợ trung bình', 'available' => true],
        ],
        'recommended' => false,
        'button_text' => 'Chọn Gói'
    ],
    'annual' => [
        'id' => 'annual',
        'name' => 'Gói 1 Năm',
        'price' => 900000,
        'duration_text' => '/ năm',
        // 'savings_text' => '(Tiết kiệm 25%)',
        'features' => [
            ['icon' => 'fa-check', 'text' => 'Truy cập đầy đủ tính năng', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Hỗ trợ ưu tiên', 'available' => true],
            ['icon' => 'fa-check', 'text' => '50 lượt đo đạc / ngày', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Ưu tiên hỗ trợ cao', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Truy cập sớm tính năng mới', 'available' => true],
        ],
        'recommended' => true, // Đánh dấu gói này là phổ biến
        'button_text' => 'Chọn Gói'
    ],
    'lifetime' => [
        'id' => 'lifetime',
        'name' => 'Gói Vĩnh Viễn',
        'price' => 5000000,
        'duration_text' => '/ trọn đời',
        'features' => [
            ['icon' => 'fa-check', 'text' => 'Truy cập đầy đủ tính năng', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Hỗ trợ VIP trọn đời', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Không giới hạn lượt đo đạc', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Ưu tiên hỗ trợ cao nhất', 'available' => true],
            ['icon' => 'fa-check', 'text' => 'Mọi cập nhật trong tương lai', 'available' => true],
        ],
        'recommended' => false,
        'button_text' => 'Liên hệ mua' // Text khác cho gói này
    ],
];

// --- Include Header ---
include $project_root_path . '/includes/header.php';
?>

<!-- CSS cho Trang Gói Tài Khoản -->
<style>
    /* --- Layout Wrapper (Giả sử đã có trong CSS chung) --- */
    /* .dashboard-wrapper { ... } */
    /* .content-wrapper { ... } */

    /* --- Grid Container cho các Gói --- */
    .packages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    /* --- Styling cho Từng Card Gói --- */
    .package-card {
        background-color: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--rounded-lg);
        padding: 2rem;
        display: flex;
        flex-direction: column;
        text-align: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .package-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    /* Tiêu đề Gói */
    .package-card h3 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-semibold);
        color: var(--gray-800);
        margin-bottom: 0.75rem;
    }

    /* Giá Gói */
    .package-price {
        font-size: 1.75rem;
        font-weight: var(--font-bold);
        color: var(--primary-600);
        margin-bottom: 0.5rem; /* Giảm margin dưới giá */
    }
    .package-price .duration {
        font-size: var(--font-size-sm);
        font-weight: var(--font-normal);
        color: var(--gray-500);
    }
    /* Text tiết kiệm (tùy chọn) */
    .package-savings {
        font-size: var(--font-size-xs);
        color: var(--primary-600);
        margin-bottom: 1.5rem; /* Đặt margin dưới text tiết kiệm */
        display: block; /* Để nó chiếm 1 dòng riêng */
        min-height: 1.2em; /* Giữ khoảng trống ngay cả khi không có text */
    }


    /* Danh sách Tính năng */
    .package-features {
        list-style: none;
        padding: 0;
        margin-bottom: 2rem;
        text-align: left;
        flex-grow: 1; /* Quan trọng: Đẩy nút xuống */
    }

    .package-features li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        color: var(--gray-700);
        font-size: var(--font-size-sm);
    }

    .package-features li i {
        width: 1.1em; /* Đảm bảo icon có không gian */
        text-align: center;
        /* Màu sắc được đặt trong vòng lặp PHP */
    }
     .package-features li i.fa-check { color: var(--primary-500); }
     .package-features li i.fa-times { color: var(--gray-400); }


    /* Nút Chọn Gói */
    .btn-select-package {
        display: inline-block;
        width: 100%;
        padding: 0.75rem 1.5rem;
        background-color: var(--primary-500);
        color: white;
        border: none;
        border-radius: var(--rounded-md);
        font-weight: var(--font-semibold);
        text-decoration: none;
        transition: background-color 0.2s ease;
        cursor: pointer;
        margin-top: auto; /* Đảm bảo nút luôn ở dưới cùng */
    }

    .btn-select-package:hover {
        background-color: var(--primary-600);
    }
    /* Nút "Liên hệ mua" có thể có style khác nếu muốn */
    .btn-select-package.contact {
        background-color: var(--gray-600);
    }
    .btn-select-package.contact:hover {
         background-color: var(--gray-700);
    }

    /* --- Styling cho Gói Đề Xuất --- */
    .package-card.recommended {
        border-color: var(--primary-500);
        border-width: 2px;
        position: relative;
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.15);
    }
    .package-card.recommended:hover {
         box-shadow: 0 8px 25px rgba(34, 197, 94, 0.2);
    }

    .recommended-badge {
        position: absolute;
        top: -1px;
        left: 50%;
        transform: translateX(-50%) translateY(-50%);
        background-color: var(--primary-500);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: var(--rounded-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-semibold);
        z-index: 1;
    }

    /* --- Responsive --- */
    @media (max-width: 768px) {
        .packages-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
         .content-wrapper {
            padding: 1rem !important;
        }
        .package-card {
            padding: 1.5rem;
        }
        .package-price {
            font-size: 1.5rem;
        }
    }
</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php include $project_root_path . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="content-wrapper">
        <h2 class="text-2xl font-semibold mb-4">Mua Gói Tài Khoản</h2>
        <p class="text-gray-600 mb-6">Chọn gói phù hợp với nhu cầu sử dụng của bạn.</p>

        <!-- Grid chứa các gói (Tạo bằng vòng lặp PHP) -->
        <div class="packages-grid">

            <?php foreach ($all_packages as $package): ?>
                <?php
                    // Xác định class cho card (thêm 'recommended' nếu cần)
                    $card_classes = 'package-card';
                    if ($package['recommended']) {
                        $card_classes .= ' recommended';
                    }
                    // Tạo URL cho trang chi tiết
                    $details_url = $base_url . '/pages/purchase/details.php?package=' . htmlspecialchars($package['id']);
                    // Xác định class cho nút bấm (thêm 'contact' nếu là nút liên hệ)
                    $button_classes = 'btn-select-package';
                    $is_contact_button = ($package['button_text'] === 'Liên hệ mua'); // Check if it's the contact button
                    if ($is_contact_button) {
                         $button_classes .= ' contact';
                         // Change URL if it's a contact button (optional)
                         // $details_url = $base_url . '/contact.php'; // Example: Redirect to contact page
                    }
                ?>
                <div class="<?php echo $card_classes; ?>">
                    <?php if ($package['recommended']): ?>
                        <div class="recommended-badge">Phổ biến</div>
                    <?php endif; ?>

                    <h3><?php echo htmlspecialchars($package['name']); ?></h3>

                    <div class="package-price">
                        <?php echo number_format($package['price'], 0, ',', '.'); ?>đ
                        <span class="duration"><?php echo htmlspecialchars($package['duration_text']); ?></span>
                    </div>

                    <!-- Hiển thị text tiết kiệm nếu có -->
                    <span class="package-savings">
                        <?php echo isset($package['savings_text']) ? htmlspecialchars($package['savings_text']) : ' '; //   to keep space ?>
                    </span>


                    <ul class="package-features">
                        <?php foreach ($package['features'] as $feature): ?>
                            <li>
                                <i class="fas <?php echo htmlspecialchars($feature['icon']); ?>" aria-hidden="true"></i>
                                <span><?php echo htmlspecialchars($feature['text']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Nút bấm với link chính xác -->
                    <?php // Chỉ tạo link đến details.php nếu không phải nút liên hệ ?>
                    <a href="<?php echo $details_url; ?>" class="<?php echo $button_classes; ?>">
                        <?php echo htmlspecialchars($package['button_text']); ?>
                    </a>
                </div>
            <?php endforeach; ?>

        </div> <!-- /.packages-grid -->

    </main>
</div>

<!-- JavaScript (Nếu cần) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add JS logic here if needed
});
</script>

<?php
// --- Include Footer ---
include $project_root_path . '/includes/footer.php';
?>