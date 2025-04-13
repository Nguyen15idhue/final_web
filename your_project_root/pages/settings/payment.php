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
$user_fullname = $_SESSION['fullname'] ?? 'Người dùng'; // Lấy từ session để hiển thị nếu cần

// --- Lấy thông tin thanh toán hiện tại từ DB (Giả lập) ---
// Trong thực tế, bạn sẽ truy vấn bảng `user_payment_details` hoặc tương tự dựa vào $user_id
$bank_name = '';        // Ví dụ: 'Vietcombank'
$account_holder = ''; // Ví dụ: 'NGUYEN VAN A'
$account_number = ''; // Ví dụ: '0123456789'
$bank_branch = '';      // Ví dụ: 'Chi nhánh Thang Long' (Tùy chọn)

// --- Giả lập truy vấn DB để lấy dữ liệu hiện có ---
// $stmt = $pdo->prepare("SELECT bank_name, account_holder, account_number, bank_branch FROM user_payment_details WHERE user_id = ?");
// $stmt->execute([$user_id]);
// $payment_info = $stmt->fetch(PDO::FETCH_ASSOC);
// if ($payment_info) {
//     $bank_name = $payment_info['bank_name'];
//     $account_holder = $payment_info['account_holder'];
//     $account_number = $payment_info['account_number'];
//     $bank_branch = $payment_info['bank_branch'];
// }


// --- Xử lý Form POST để cập nhật thông tin ---
$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Bảo mật: Thêm CSRF token check ---
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) { die('Lỗi CSRF!'); }

    // --- Lấy và làm sạch dữ liệu ---
    $new_bank_name = trim(htmlspecialchars($_POST['bank_name'] ?? ''));
    $new_account_holder = trim(htmlspecialchars($_POST['account_holder'] ?? ''));
    $new_account_number = trim(htmlspecialchars(preg_replace('/[^0-9]/', '', $_POST['account_number'] ?? ''))); // Chỉ giữ số
    $new_bank_branch = trim(htmlspecialchars($_POST['bank_branch'] ?? ''));

    // --- Validate dữ liệu ---
    if (empty($new_bank_name)) {
        $error_message = "Vui lòng nhập tên ngân hàng.";
    } elseif (empty($new_account_holder)) {
        $error_message = "Vui lòng nhập tên chủ tài khoản.";
    } elseif (empty($new_account_number)) {
         $error_message = "Vui lòng nhập số tài khoản.";
    } elseif (!ctype_digit($new_account_number)) { // Kiểm tra xem có phải toàn số không
        $error_message = "Số tài khoản chỉ được chứa chữ số.";
    } else {
        // --- Cập nhật hoặc Thêm mới vào CSDL (Giả lập) ---
        // Kiểm tra xem user đã có thông tin thanh toán chưa
        // Nếu có -> UPDATE, nếu chưa -> INSERT
        // $stmt_check = $pdo->prepare("SELECT user_id FROM user_payment_details WHERE user_id = ?");
        // $stmt_check->execute([$user_id]);
        // $exists = $stmt_check->fetch();

        // if ($exists) {
        //     $stmt_update = $pdo->prepare("UPDATE user_payment_details SET bank_name = ?, account_holder = ?, account_number = ?, bank_branch = ? WHERE user_id = ?");
        //     $result = $stmt_update->execute([$new_bank_name, $new_account_holder, $new_account_number, $new_bank_branch, $user_id]);
        // } else {
        //     $stmt_insert = $pdo->prepare("INSERT INTO user_payment_details (user_id, bank_name, account_holder, account_number, bank_branch) VALUES (?, ?, ?, ?, ?)");
        //     $result = $stmt_insert->execute([$user_id, $new_bank_name, $new_account_holder, $new_account_number, $new_bank_branch]);
        // }

        // Giả lập thành công
        $update_successful = true;

        if ($update_successful) {
            // Gán lại biến để hiển thị giá trị mới trên form
            $bank_name = $new_bank_name;
            $account_holder = $new_account_holder;
            $account_number = $new_account_number;
            $bank_branch = $new_bank_branch;

            $success_message = "Cập nhật thông tin thanh toán thành công!";
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
$csrf_token = $_SESSION['csrf_token'] ?? 'dummy_csrf_token_payment'; // Nên tạo token thật

// --- Include Header ---
include $project_root_path . '/includes/header.php';
?>

<!-- CSS cho Trang Thông Tin Thanh Toán -->
<style>
    /* Kế thừa các style từ profile.php nếu chúng đã được định nghĩa chung */
    /* Hoặc copy các style cần thiết vào đây */
    .payment-settings-form {
        background-color: white;
        padding: 2rem;
        border-radius: var(--rounded-lg);
        border: 1px solid var(--gray-200);
        max-width: 700px;
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
        grid-template-columns: 150px 1fr;
        align-items: center;
        gap: 1rem;
    }

    .form-group label {
        font-weight: var(--font-medium);
        color: var(--gray-700);
        text-align: right;
        padding-right: 1rem;
    }
     .form-group.submit-group {
          grid-template-columns: 1fr;
          text-align: right;
          padding-left: calc(150px + 1rem);
     }

    .form-control {
        width: 100%;
        padding: 0.6rem 0.9rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--rounded-md);
        font-size: var(--font-size-base);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
    }

    .form-text { /* Style cho text hướng dẫn nhỏ */
         grid-column: 2 / -1; /* Chiếm cột input */
         font-size: var(--font-size-xs);
         color: var(--gray-500);
         margin-top: -0.75rem; /* Kéo lên gần input hơn */
         margin-bottom: 1rem; /* Tạo khoảng cách trước field tiếp theo */
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
    .alert {
        padding: 0.9rem 1.25rem;
        margin-bottom: 1.5rem;
        border-radius: var(--rounded-md);
        font-size: var(--font-size-sm);
        border: 1px solid transparent;
    }
    .alert-success { color: var(--badge-green-text); background-color: var(--badge-green-bg); border-color: var(--primary-200); }
    .alert-error { color: var(--badge-red-text); background-color: var(--badge-red-bg); border-color: #fecaca; }
    .alert strong { font-weight: var(--font-semibold); }

     @media (max-width: 768px) {
        .content-wrapper { padding: 1rem !important; }
        .payment-settings-form { margin-top: 1rem; padding: 1.5rem; max-width: 100%; }
        .form-group { grid-template-columns: 1fr; gap: 0.5rem; }
        .form-group label { text-align: left; padding-right: 0; margin-bottom: 0; }
         .form-group.submit-group { padding-left: 0; text-align: left; }
         .form-text { grid-column: 1 / -1; margin-top: 0.25rem; } /* Điều chỉnh text hướng dẫn */
    }
</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php include $project_root_path . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="content-wrapper">
        <h2 class="text-2xl font-semibold mb-6">Thông tin thanh toán</h2>
        <p class="text-gray-600 mb-6">Cập nhật thông tin tài khoản ngân hàng của bạn để nhận thanh toán.</p>

        <!-- Form Cài đặt Thanh toán -->
        <form action="" method="POST" class="payment-settings-form">
            <!-- Input ẩn CSRF token -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <!-- Hiển thị thông báo -->
            <?php if ($success_message): ?>
                <div class="alert alert-success"><strong>Thành công!</strong> <?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error"><strong>Lỗi!</strong> <?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Phần Thông Tin Tài Khoản Ngân Hàng -->
            <div class="form-section">
                <h3>Tài khoản ngân hàng nhận tiền</h3>

                 <div class="form-group">
                    <label for="bank_name">Tên ngân hàng:</label>
                    <input type="text" id="bank_name" name="bank_name" class="form-control" value="<?php echo htmlspecialchars($bank_name); ?>" placeholder="Ví dụ: Vietcombank, Techcombank,..." required>
                 </div>

                <div class="form-group">
                    <label for="account_holder">Tên chủ tài khoản:</label>
                    <input type="text" id="account_holder" name="account_holder" class="form-control" value="<?php echo htmlspecialchars($account_holder); ?>" placeholder="NGUYEN VAN A" style="text-transform: uppercase;" required>
                     <small class="form-text">Viết hoa không dấu, giống như trên thẻ/tài khoản của bạn.</small>
                 </div>

                <div class="form-group">
                    <label for="account_number">Số tài khoản:</label>
                    <input type="text" id="account_number" name="account_number" inputmode="numeric" pattern="[0-9]*" class="form-control" value="<?php echo htmlspecialchars($account_number); ?>" placeholder="Nhập chính xác số tài khoản" required>
                </div>

                <div class="form-group">
                    <label for="bank_branch">Chi nhánh (Tùy chọn):</label>
                    <input type="text" id="bank_branch" name="bank_branch" class="form-control" value="<?php echo htmlspecialchars($bank_branch); ?>" placeholder="Ví dụ: Chi nhánh Thăng Long">
                </div>

            </div>

            <!-- Nút Lưu Thay Đổi -->
            <div class="form-group submit-group" style="margin-bottom: 0;"> <!-- Bỏ margin bottom cho nhóm cuối -->
                <button type="submit" class="btn-submit">Lưu thông tin thanh toán</button>
            </div>

        </form>

        <!-- Ghi chú thêm -->
         <p class="text-center text-gray-500 text-sm mt-6">
             Thông tin này được sử dụng để chúng tôi thực hiện thanh toán cho bạn (ví dụ: hoa hồng giới thiệu, hoàn tiền).
             Hãy đảm bảo thông tin chính xác.
         </p>

    </main>
</div>

<!-- JavaScript (Nếu cần) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Thêm logic JS ở đây nếu cần
    // Ví dụ: Chuyển tên chủ tài khoản thành chữ hoa khi người dùng nhập
    const accountHolderInput = document.getElementById('account_holder');
    if (accountHolderInput) {
        accountHolderInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

     // Ví dụ: Ngăn nhập ký tự không phải số vào số tài khoản
     const accountNumberInput = document.getElementById('account_number');
     if(accountNumberInput){
         accountNumberInput.addEventListener('input', function(e){
              // Thay thế mọi ký tự không phải số bằng chuỗi rỗng
              this.value = this.value.replace(/[^0-9]/g, '');
         });
     }
});
</script>

<?php
// --- Include Footer ---
include $project_root_path . '/includes/footer.php';
?>