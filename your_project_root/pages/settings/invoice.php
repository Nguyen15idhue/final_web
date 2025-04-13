<?php
session_start();

// --- Base URL Configuration ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$script_dir = dirname($_SERVER['PHP_SELF']); // /pages/settings
$base_project_dir = dirname(dirname($script_dir)); // Lùi 2 cấp
$base_url = $protocol . $domain . ($base_project_dir === '/' || $base_project_dir === '\\' ? '' : $base_project_dir);

// --- Project Root Path for Includes ---
$project_root_path = dirname(dirname(__DIR__)); // Lùi 2 cấp từ thư mục chứa file này (settings)

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// --- User Info ---
$user_id = $_SESSION['user_id'];
$user_username = $_SESSION['username'] ?? 'Người dùng';

// --- Lấy thông tin xuất hóa đơn hiện tại từ DB (Giả lập) ---
// Trong thực tế, bạn sẽ truy vấn bảng `user_invoice_details` hoặc tương tự dựa vào $user_id
$invoice_company_name = ''; // Tên công ty/đơn vị
$invoice_tax_id = '';       // Mã số thuế
$invoice_address = '';      // Địa chỉ đăng ký kinh doanh
$invoice_buyer_name = $user_username; // Mặc định là tên người dùng, có thể cho sửa
$invoice_email = '';        // Email nhận hóa đơn (có thể khác email tài khoản)

// --- Giả lập truy vấn DB ---
// $stmt = $pdo->prepare("SELECT company_name, tax_id, address, buyer_name, email FROM user_invoice_details WHERE user_id = ?");
// $stmt->execute([$user_id]);
// $invoice_info = $stmt->fetch(PDO::FETCH_ASSOC);
// if ($invoice_info) {
//     $invoice_company_name = $invoice_info['company_name'];
//     $invoice_tax_id = $invoice_info['tax_id'];
//     $invoice_address = $invoice_info['address'];
//     $invoice_buyer_name = $invoice_info['buyer_name'] ?: $user_username; // Ưu tiên tên đã lưu, nếu rỗng thì lấy tên user
//     $invoice_email = $invoice_info['email'];
// }

// --- Xử lý Form POST để cập nhật thông tin ---
$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Bảo mật: Thêm CSRF token check ---
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) { die('Lỗi CSRF!'); }

    // --- Lấy và làm sạch dữ liệu ---
    $new_company_name = trim(htmlspecialchars($_POST['company_name'] ?? ''));
    $new_tax_id = trim(htmlspecialchars(preg_replace('/[^\d-]/', '', $_POST['tax_id'] ?? ''))); // Chỉ giữ số và dấu gạch ngang
    $new_address = trim(htmlspecialchars($_POST['address'] ?? ''));
    $new_buyer_name = trim(htmlspecialchars($_POST['buyer_name'] ?? $user_username)); // Lấy tên user nếu không nhập
    $new_email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));

    // --- Validate dữ liệu ---
    if (empty($new_company_name)) {
        $error_message = "Vui lòng nhập Tên đơn vị/công ty.";
    } elseif (empty($new_tax_id)) {
        $error_message = "Vui lòng nhập Mã số thuế.";
        // Thêm validation chi tiết hơn cho MST nếu cần (ví dụ: độ dài)
    } elseif (empty($new_address)) {
        $error_message = "Vui lòng nhập Địa chỉ đăng ký kinh doanh.";
    } elseif (!empty($new_email) && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
         $error_message = "Địa chỉ Email nhận hóa đơn không hợp lệ.";
    }
     else {
        // --- Cập nhật hoặc Thêm mới vào CSDL (Giả lập) ---
        // Tương tự trang payment, kiểm tra tồn tại rồi UPDATE hoặc INSERT
        // $stmt_check = $pdo->prepare("SELECT user_id FROM user_invoice_details WHERE user_id = ?");
        // ... (execute and fetch) ...
        // if ($exists) {
        //     $stmt_update = $pdo->prepare("UPDATE user_invoice_details SET company_name=?, tax_id=?, address=?, buyer_name=?, email=? WHERE user_id=?");
        //     $result = $stmt_update->execute([...]);
        // } else {
        //     $stmt_insert = $pdo->prepare("INSERT INTO user_invoice_details (user_id, company_name, ...) VALUES (?, ?, ...)");
        //     $result = $stmt_insert->execute([...]);
        // }

        // Giả lập thành công
        $update_successful = true;

        if ($update_successful) {
            // Gán lại biến để hiển thị giá trị mới trên form
            $invoice_company_name = $new_company_name;
            $invoice_tax_id = $new_tax_id;
            $invoice_address = $new_address;
            $invoice_buyer_name = $new_buyer_name;
            $invoice_email = $new_email;

            $success_message = "Cập nhật thông tin xuất hóa đơn thành công!";
        } else {
            $error_message = "Đã xảy ra lỗi khi cập nhật. Vui lòng thử lại.";
        }
    }
    // --- Bảo mật: Tạo lại CSRF token ---
    // $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
} else {
    // --- Bảo mật: Tạo CSRF token khi tải trang (GET) ---
    // if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
}
$csrf_token = $_SESSION['csrf_token'] ?? 'dummy_csrf_token_invoice'; // Nên tạo token thật

// --- Include Header ---
include $project_root_path . '/includes/header.php';
?>

<!-- CSS cho Trang Thông Tin Xuất Hóa Đơn -->
<style>
    /* Kế thừa/Copy style từ các trang settings khác */
    .invoice-settings-form {
        background-color: white;
        padding: 2rem;
        border-radius: var(--rounded-lg);
        border: 1px solid var(--gray-200);
        max-width: 750px; /* Có thể cần rộng hơn chút */
        margin: 1rem auto;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .form-section h3 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-semibold);
        color: var(--gray-800);
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
        display: grid;
        /* Điều chỉnh cột label nếu cần */
        grid-template-columns: 180px 1fr; /* Tăng nhẹ cột label */
        align-items: start; /* Căn label lên trên nếu dùng textarea */
        gap: 1rem;
    }
     .form-group.align-center { /* Class để căn giữa cho input thường */
         align-items: center;
     }

    .form-group label {
        font-weight: var(--font-medium);
        color: var(--gray-700);
        text-align: right;
        padding-right: 1rem;
        padding-top: 0.6rem; /* Căn giữa với input */
    }
    .form-group textarea + label { /* Căn label lên trên cho textarea */
         padding-top: 0;
    }
     .form-group.submit-group {
          grid-template-columns: 1fr;
          text-align: right;
          padding-left: calc(180px + 1rem); /* Điều chỉnh padding */
     }


    .form-control {
        width: 100%;
        padding: 0.6rem 0.9rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--rounded-md);
        font-size: var(--font-size-base);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
     textarea.form-control {
         min-height: 80px; /* Chiều cao tối thiểu cho textarea */
         line-height: 1.5;
     }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
    }

    .form-text {
         grid-column: 2 / -1;
         font-size: var(--font-size-xs);
         color: var(--gray-500);
         margin-top: -0.75rem;
         margin-bottom: 1rem;
    }

    .btn-submit {
        display: inline-block;
        padding: 0.7rem 1.8rem;
        background-color: var(--primary-500);
        color: white;
        border: none;
        border-radius: var(--rounded-md);
        font-weight: var(--font-semibold);
        text-decoration: none;
        transition: background-color 0.2s ease;
        cursor: pointer;
        font-size: var(--font-size-base);
    }
    .btn-submit:hover { background-color: var(--primary-600); }

    /* Alert Messages */
    .alert { padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; border-radius: var(--rounded-md); font-size: var(--font-size-sm); border: 1px solid transparent; }
    .alert-success { color: var(--badge-green-text); background-color: var(--badge-green-bg); border-color: var(--primary-200); }
    .alert-error { color: var(--badge-red-text); background-color: var(--badge-red-bg); border-color: #fecaca; }
    .alert strong { font-weight: var(--font-semibold); }

     @media (max-width: 768px) {
        .content-wrapper { padding: 1rem !important; }
        .invoice-settings-form { margin-top: 1rem; padding: 1.5rem; max-width: 100%; }
        .form-group { grid-template-columns: 1fr; gap: 0.5rem; align-items: start; }
        .form-group label { text-align: left; padding-right: 0; margin-bottom: 0; padding-top: 0; }
         .form-group.submit-group { padding-left: 0; text-align: left; }
         .form-text { grid-column: 1 / -1; margin-top: 0.25rem; }
    }
</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php include $project_root_path . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="content-wrapper">
        <h2 class="text-2xl font-semibold mb-6">Thông tin xuất hóa đơn</h2>
        <p class="text-gray-600 mb-6">Cung cấp thông tin chính xác để nhận hóa đơn điện tử (VAT) cho các giao dịch của bạn.</p>

        <!-- Form Cài đặt Xuất Hóa Đơn -->
        <form action="" method="POST" class="invoice-settings-form">
            <!-- Input ẩn CSRF token -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <!-- Hiển thị thông báo -->
            <?php if ($success_message): ?>
                <div class="alert alert-success"><strong>Thành công!</strong> <?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error"><strong>Lỗi!</strong> <?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Phần Thông Tin Công Ty/Đơn Vị -->
            <div class="form-section">
                <h3>Thông tin đơn vị nhận hóa đơn</h3>

                 <div class="form-group align-center">
                    <label for="company_name">Tên đơn vị/Công ty:</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo htmlspecialchars($invoice_company_name); ?>" placeholder="Tên đầy đủ theo giấy phép kinh doanh" required>
                 </div>

                <div class="form-group align-center">
                    <label for="tax_id">Mã số thuế:</label>
                    <input type="text" id="tax_id" name="tax_id" class="form-control" value="<?php echo htmlspecialchars($invoice_tax_id); ?>" placeholder="Mã số thuế của công ty/cá nhân kinh doanh" required>
                 </div>

                <div class="form-group"> <!-- align-items: start (mặc định) tốt hơn cho textarea -->
                    <label for="address">Địa chỉ đăng ký:</label>
                    <textarea id="address" name="address" class="form-control" rows="3" placeholder="Địa chỉ đầy đủ theo giấy phép kinh doanh" required><?php echo htmlspecialchars($invoice_address); ?></textarea>
                     <small class="form-text">Ghi rõ số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố.</small>
                 </div>
            </div>

             <!-- Phần Thông Tin Người Mua (Tùy chọn) -->
            <div class="form-section">
                <h3>Thông tin bổ sung (Tùy chọn)</h3>
                <div class="form-group align-center">
                    <label for="buyer_name">Tên người mua hàng:</label>
                    <input type="text" id="buyer_name" name="buyer_name" class="form-control" value="<?php echo htmlspecialchars($invoice_buyer_name); ?>" placeholder="Để trống nếu giống tên tài khoản">
                     <small class="form-text">Họ tên người trực tiếp mua hàng (nếu khác thông tin tài khoản).</small>
                 </div>
                  <div class="form-group align-center">
                    <label for="email">Email nhận hóa đơn:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($invoice_email); ?>" placeholder="Địa chỉ email để nhận hóa đơn điện tử">
                     <small class="form-text">Để trống nếu muốn gửi về email chính của tài khoản.</small>
                 </div>
            </div>


            <!-- Nút Lưu Thay Đổi -->
            <div class="form-group submit-group" style="margin-bottom: 0;">
                <button type="submit" class="btn-submit">Lưu thông tin hóa đơn</button>
            </div>

        </form>

          <!-- Ghi chú thêm -->
         <p class="text-center text-gray-500 text-sm mt-6">
             Hóa đơn điện tử sẽ được gửi đến email bạn cung cấp (hoặc email tài khoản nếu để trống) sau khi giao dịch hoàn tất.
         </p>

    </main>
</div>

<!-- JavaScript (Nếu cần) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Thêm logic JS ở đây nếu cần
    // Ví dụ: kiểm tra định dạng MST phía client
});
</script>

<?php
// --- Include Footer ---
include $project_root_path . '/includes/footer.php';
?>