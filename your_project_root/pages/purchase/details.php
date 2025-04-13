<?php
session_start();

// --- Base URL Configuration ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$script_dir = dirname($_SERVER['PHP_SELF']); // /pages/purchase
$base_project_dir = dirname(dirname($script_dir)); // Lùi 2 cấp để đến thư mục gốc dự án
$base_url = $protocol . $domain . ($base_project_dir === '/' || $base_project_dir === '\\' ? '' : $base_project_dir);
echo "DEBUG - Base URL in details.php: " . htmlspecialchars($base_url);
// Bạn có thể thêm die(); ở đây để dừng thực thi và chỉ xem giá trị này
// die();

// --- Project Root Path for Includes ---
$project_root_path = dirname(dirname(__DIR__));

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// --- Get Selected Package from URL ---
$selected_package_id = $_GET['package'] ?? null; // Ví dụ: 'monthly', 'annual', 'lifetime'

// --- Define Package Data (Nên lấy từ DB hoặc config trong thực tế) ---
$packages = [
    'monthly' => ['name' => 'Gói 1 Tháng', 'price' => 100000],
    'quarterly' => ['name' => 'Gói 3 Tháng', 'price' => 270000],
    'biannual' => ['name' => 'Gói 6 Tháng', 'price' => 500000],
    'annual' => ['name' => 'Gói 1 Năm', 'price' => 900000],
    'lifetime' => ['name' => 'Gói Vĩnh Viễn', 'price' => 5000000],
    // Thêm các gói khác nếu cần
];

// --- Validate Selected Package ---
// Kiểm tra cả trường hợp gói 'lifetime' vì nó không đi qua luồng thanh toán này
if (!$selected_package_id || !isset($packages[$selected_package_id]) || $selected_package_id === 'lifetime') {
    // Nếu không có package, package không hợp lệ, hoặc là gói lifetime, chuyển về trang chọn gói
    header('Location: ' . $base_url . '/pages/purchase/package.php?error=invalid_or_contact_package');
    exit;
}
$selected_package = $packages[$selected_package_id];
$base_price = $selected_package['price']; // Lấy giá gốc

// --- Define List of Provinces/Cities (Nên lấy từ DB) ---
$provinces = [
    "An Giang", "Bà Rịa - Vũng Tàu", "Bắc Giang", "Bắc Kạn", "Bạc Liêu", "Bắc Ninh",
    "Bến Tre", "Bình Định", "Bình Dương", "Bình Phước", "Bình Thuận", "Cà Mau",
    "Cần Thơ", "Cao Bằng", "Đà Nẵng", "Đắk Lắk", "Đắk Nông", "Điện Biên", "Đồng Nai",
    "Đồng Tháp", "Gia Lai", "Hà Giang", "Hà Nam", "Hà Nội", "Hà Tĩnh", "Hải Dương",
    "Hải Phòng", "Hậu Giang", "Hòa Bình", "Hưng Yên", "Khánh Hòa", "Kiên Giang",
    "Kon Tum", "Lai Châu", "Lâm Đồng", "Lạng Sơn", "Lào Cai", "Long An", "Nam Định",
    "Nghệ An", "Ninh Bình", "Ninh Thuận", "Phú Thọ", "Phú Yên", "Quảng Bình", "Quảng Nam",
    "Quảng Ngãi", "Quảng Ninh", "Quảng Trị", "Sóc Trăng", "Sơn La", "Tây Ninh", "Thái Bình",
    "Thái Nguyên", "Thanh Hóa", "Thừa Thiên Huế", "Tiền Giang", "TP Hồ Chí Minh", "Trà Vinh",
    "Tuyên Quang", "Vĩnh Long", "Vĩnh Phúc", "Yên Bái"
    // ... thêm các tỉnh thành khác
];

// --- User Info ---
$user_fullname = $_SESSION['fullname'] ?? 'Người dùng';

// --- Include Header ---
include $project_root_path . '/includes/header.php';
?>

<!-- CSS cho Trang Chi Tiết Mua Hàng -->
<style>
    .purchase-details-form {
        background-color: white;
        padding: 2rem;
        border-radius: var(--rounded-lg);
        border: 1px solid var(--gray-200);
        max-width: 600px; /* Giới hạn chiều rộng form */
        margin: 2rem auto; /* Căn giữa form */
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-weight: var(--font-medium);
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--rounded-md);
        font-size: var(--font-size-base);
        transition: border-color 0.2s ease;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
    }
    /* Style cho input[type=number] */
    input[type=number] {
        -moz-appearance: textfield; /* Firefox */
    }
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .selected-package-info {
        background-color: var(--gray-50);
        padding: 1rem 1.5rem;
        border-radius: var(--rounded-md);
        margin-bottom: 1.5rem;
        border: 1px dashed var(--gray-200);
    }
    .selected-package-info strong {
        color: var(--primary-600);
    }

    .total-price-display {
        font-size: 1.25rem;
        font-weight: var(--font-semibold);
        color: var(--gray-800);
        margin-top: 1rem;
        text-align: right;
    }
     .total-price-display span {
         color: var(--primary-600);
         font-weight: var(--font-bold);
     }

    .btn-submit {
        display: block;
        width: 100%;
        padding: 0.8rem 1.5rem;
        background-color: var(--primary-500);
        color: white;
        border: none;
        border-radius: var(--rounded-md);
        font-weight: var(--font-semibold);
        text-decoration: none;
        transition: background-color 0.2s ease;
        cursor: pointer;
        font-size: var(--font-size-base);
        text-align: center;
    }

    .btn-submit:hover {
        background-color: var(--primary-600);
    }

     @media (max-width: 768px) {
        .content-wrapper {
            padding: 1rem !important;
        }
        .purchase-details-form {
            margin-top: 1rem;
            padding: 1.5rem;
        }
    }
</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php include $project_root_path . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="content-wrapper">
        <h2 class="text-2xl font-semibold mb-4">Chi tiết mua hàng</h2>

        <!-- Action của form trỏ đến payments.php -->
        <form action="<?php echo $base_url; ?>/pages/purchase/payments.php" method="POST" class="purchase-details-form" id="details-form">
            <!-- Thông tin gói đã chọn -->
            <div class="selected-package-info">
                Bạn đang chọn: <strong><?php echo htmlspecialchars($selected_package['name']); ?></strong>
            </div>

            <!-- Input ẩn để gửi thông tin gói -->
            <input type="hidden" name="package_id" value="<?php echo htmlspecialchars($selected_package_id); ?>">
            <input type="hidden" name="package_name" value="<?php echo htmlspecialchars($selected_package['name']); ?>">
            <input type="hidden" name="base_price" id="base_price" value="<?php echo $base_price; ?>"> <!-- Giá gốc để JS tính toán -->
            <input type="hidden" name="total_price" id="total_price_hidden" value="<?php echo $base_price; ?>"> <!-- Giá tổng, sẽ được JS cập nhật -->

            <!-- Số lượng tài khoản -->
            <div class="form-group">
                <label for="quantity">Số lượng tài khoản:</label>
                <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" required>
            </div>

            <!-- Chọn Tỉnh/Thành phố -->
            <div class="form-group">
                <label for="province">Tỉnh/Thành phố sử dụng:</label>
                <select id="province" name="province" class="form-control" required>
                    <option value="" disabled selected>-- Chọn Tỉnh/Thành phố --</option>
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?php echo htmlspecialchars($province); ?>"><?php echo htmlspecialchars($province); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

             <!-- Hiển thị tổng tiền (cập nhật bằng JS) -->
            <div class="total-price-display">
                Tổng cộng: <span id="total-price-view"><?php echo number_format($base_price, 0, ',', '.'); ?>đ</span>
            </div>

            <!-- Nút chuyển đến thanh toán -->
            <div class="form-group" style="margin-top: 2rem; margin-bottom: 0;">
                <button type="submit" class="btn-submit">Chuyển đến Thanh toán</button>
            </div>
        </form>

    </main>
</div>

<!-- JavaScript để cập nhật giá tiền -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const basePrice = parseFloat(document.getElementById('base_price').value);
    const totalPriceView = document.getElementById('total-price-view');
    const totalPriceHidden = document.getElementById('total_price_hidden');

    function updateTotalPrice() {
        let quantity = parseInt(quantityInput.value);
        // Đảm bảo số lượng hợp lệ (ít nhất là 1)
        if (isNaN(quantity) || quantity < 1) {
            quantity = 1;
            quantityInput.value = 1; // Sửa lại input nếu không hợp lệ
        }

        const total = basePrice * quantity;

        // Cập nhật giá hiển thị (dùng toLocaleString để format tiền tệ VNĐ)
        totalPriceView.textContent = total.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });

        // Cập nhật giá trị input ẩn để gửi đi (giá trị số thuần túy)
        totalPriceHidden.value = total;
    }

    // Gọi hàm lần đầu khi tải trang
    updateTotalPrice();

    // Thêm sự kiện lắng nghe khi giá trị số lượng thay đổi
    quantityInput.addEventListener('input', updateTotalPrice);

    // (Tùy chọn) Ngăn chặn submit nếu chưa chọn tỉnh thành
    const form = document.getElementById('details-form');
    const provinceSelect = document.getElementById('province');
    form.addEventListener('submit', function(event) {
        if (!provinceSelect.value) {
            alert('Vui lòng chọn Tỉnh/Thành phố sử dụng.');
            event.preventDefault(); // Ngăn form gửi đi
            provinceSelect.focus();
            return; // Dừng thực thi thêm
        }
        // Cập nhật giá lần cuối trước khi submit phòng trường hợp JS lỗi hoặc người dùng sửa đổi nhanh
        updateTotalPrice();
    });
});
</script>

<?php
// --- Include Footer ---
include $project_root_path . '/includes/footer.php';
?>