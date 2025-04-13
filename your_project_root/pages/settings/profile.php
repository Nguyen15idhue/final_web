<?php
session_start();

// --- Base URL Configuration ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
// File này nằm trong /pages/settings/ => cần lùi lại 2 cấp để đến gốc dự án
$script_dir = dirname($_SERVER['PHP_SELF']); // Should be /pages/settings
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
$user_fullname = $_SESSION['fullname'] ?? 'Người dùng chưa cập nhật';
// --- Lấy thông tin khác từ DB (Giả lập) ---
// Trong thực tế, bạn sẽ truy vấn CSDL dựa vào $user_id
$user_email = $_SESSION['user_email'] ?? 'email@example.com'; // Lấy từ session hoặc DB
$user_phone = $_SESSION['user_phone'] ?? '';             // Lấy từ session hoặc DB
$user_username = $_SESSION['username'] ?? 'user' . $user_id; // Lấy từ session hoặc DB (thường là tên đăng nhập)

// --- Xử lý Form POST để cập nhật thông tin ---
$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Bảo mật: Thêm CSRF token check ở đây ---
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     die('Lỗi CSRF!');
    // }

    // --- Lấy và làm sạch dữ liệu ---
    $new_fullname = trim(htmlspecialchars($_POST['fullname'] ?? ''));
    $new_email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $new_phone = trim(htmlspecialchars(preg_replace('/[^0-9\s\-+()]/', '', $_POST['phone'] ?? ''))); // Chỉ giữ số, khoảng trắng, -, +, ()

    // --- Validate dữ liệu ---
    if (empty($new_fullname)) {
        $error_message = "Vui lòng nhập họ và tên.";
    } elseif (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Vui lòng nhập địa chỉ email hợp lệ.";
    } else {
        // --- Cập nhật vào CSDL (Giả lập) ---
        // Trong thực tế, bạn sẽ thực hiện câu lệnh UPDATE ở đây
        // $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
        // $result = $stmt->execute([$new_fullname, $new_email, $new_phone, $user_id]);

        // Giả lập thành công
        $update_successful = true; // Đặt là false để test lỗi

        if ($update_successful) {
            // Cập nhật lại thông tin trong session
            $_SESSION['fullname'] = $new_fullname;
            $_SESSION['user_email'] = $new_email; // Cập nhật nếu bạn lưu email trong session
            $_SESSION['user_phone'] = $new_phone; // Cập nhật nếu bạn lưu phone trong session

            // Gán lại biến để hiển thị giá trị mới ngay lập tức trên form
            $user_fullname = $new_fullname;
            $user_email = $new_email;
            $user_phone = $new_phone;

            $success_message = "Cập nhật thông tin cá nhân thành công!";
        } else {
            $error_message = "Đã xảy ra lỗi khi cập nhật thông tin. Vui lòng thử lại.";
        }
    }
    // --- Bảo mật: Tạo lại CSRF token sau khi xử lý POST ---
    // $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
} else {
    // --- Bảo mật: Tạo CSRF token khi tải trang lần đầu (GET) ---
    // if (empty($_SESSION['csrf_token'])) {
    //     $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    // }
}
// --- Biến CSRF Token (Ví dụ đơn giản, nên dùng thư viện) ---
$csrf_token = $_SESSION['csrf_token'] ?? 'dummy_csrf_token'; // Nên tạo token thật

// --- Include Header ---
include $project_root_path . '/includes/header.php';
?>

<!-- CSS cho Trang Thông Tin Cá Nhân -->
<style>
    .profile-settings-form {
        background-color: white;
        padding: 2rem;
        border-radius: var(--rounded-lg);
        border: 1px solid var(--gray-200);
        max-width: 700px; /* Tăng chiều rộng form */
        margin: 1rem auto; /* Giảm margin top */
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--gray-100);
    }
     .form-section:last-child {
         border-bottom: none;
         margin-bottom: 0;
         padding-bottom: 0;
     }

    .form-section h3 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-semibold);
        color: var(--gray-800);
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.25rem; /* Giảm khoảng cách nhóm */
        display: grid; /* Dùng grid để căn chỉnh label và input */
        grid-template-columns: 150px 1fr; /* Cột label cố định, cột input linh hoạt */
        align-items: center; /* Căn giữa theo chiều dọc */
        gap: 1rem;
    }

    .form-group label {
        font-weight: var(--font-medium);
        color: var(--gray-700);
        text-align: right; /* Căn phải label */
        padding-right: 1rem;
    }
    /* Cho input không có label (ví dụ nút submit) */
     .form-group.no-label {
         grid-template-columns: 1fr; /* Chỉ còn 1 cột */
         padding-left: calc(150px + 1rem); /* Thụt lề bằng cột label + gap */
     }
     /* Hoặc căn nút sang phải */
     .form-group.submit-group {
          grid-template-columns: 1fr;
          text-align: right;
          padding-left: calc(150px + 1rem); /* Thụt lề */
     }


    .form-control {
        width: 100%;
        padding: 0.6rem 0.9rem; /* Điều chỉnh padding input */
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
    .form-control[disabled] {
        background-color: var(--gray-100);
        cursor: not-allowed;
        opacity: 0.7;
    }
    .form-control-plaintext {
         padding: 0.6rem 0; /* Padding giống input nhưng không có border */
         font-size: var(--font-size-base);
         color: var(--gray-700);
         /* display: inline-block; */
    }

    /* Password change link */
    .password-change-link a {
        color: var(--primary-600);
        text-decoration: none;
        font-weight: var(--font-medium);
    }
     .password-change-link a:hover {
         text-decoration: underline;
     }

    .btn-submit {
        display: inline-block; /* Để nút không chiếm full width */
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

    .btn-submit:hover {
        background-color: var(--primary-600);
    }

    /* Alert Messages */
    .alert {
        padding: 0.9rem 1.25rem;
        margin-bottom: 1.5rem;
        border-radius: var(--rounded-md);
        font-size: var(--font-size-sm);
        border-width: 1px;
        border-style: solid;
    }
    .alert-success {
        color: var(--badge-green-text);
        background-color: var(--badge-green-bg);
        border-color: var(--primary-200);
    }
    .alert-error {
         color: var(--badge-red-text);
        background-color: var(--badge-red-bg);
        border-color: #fecaca; /* Màu viền đỏ nhạt hơn */
    }
    .alert strong { font-weight: var(--font-semibold); }


     @media (max-width: 768px) {
        .content-wrapper {
            padding: 1rem !important;
        }
        .profile-settings-form {
            margin-top: 1rem;
            padding: 1.5rem;
            max-width: 100%;
        }
        .form-group {
            grid-template-columns: 1fr; /* Stack label trên input */
            gap: 0.5rem; /* Giảm gap */
        }
        .form-group label {
            text-align: left; /* Căn trái label */
            padding-right: 0;
            margin-bottom: 0;
        }
        .form-group.no-label, .form-group.submit-group {
             padding-left: 0; /* Bỏ thụt lề */
             text-align: left; /* Căn trái nút submit */
        }
    }
</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php include $project_root_path . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="content-wrapper">
        <h2 class="text-2xl font-semibold mb-6">Thông tin cá nhân</h2>

        <!-- Form Cài đặt -->
        <form action="" method="POST" class="profile-settings-form">
            <!-- Input ẩn CSRF token -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <!-- Hiển thị thông báo -->
            <?php if ($success_message): ?>
                <div class="alert alert-success"><strong>Thành công!</strong> <?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error"><strong>Lỗi!</strong> <?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Phần Thông Tin Cơ Bản -->
            <div class="form-section">
                <h3>Thông tin cơ bản</h3>
                <div class="form-group">
                    <label for="user_id">User ID:</label>
                    <span class="form-control-plaintext"><?php echo htmlspecialchars($user_id); ?></span>
                </div>
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                     <span class="form-control-plaintext"><?php echo htmlspecialchars($user_username); ?></span>
                    <!-- Hoặc input disabled: -->
                    <!-- <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($user_username); ?>" disabled> -->
                </div>
                <div class="form-group">
                    <label for="fullname">Họ và Tên:</label>
                    <input type="text" id="fullname" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user_fullname); ?>" required>
                </div>
                 <div class="form-group">
                    <label for="email">Địa chỉ Email:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" required>
                </div>
            </div>

             <!-- Phần Thông Tin Liên Hệ (Tùy chọn) -->
            <div class="form-section">
                <h3>Thông tin liên hệ (Tùy chọn)</h3>
                <div class="form-group">
                    <label for="phone">Số điện thoại:</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_phone); ?>" placeholder="Nhập số điện thoại của bạn">
                </div>
                <!-- Thêm các trường địa chỉ nếu cần -->
                <!--
                <div class="form-group">
                    <label for="address">Địa chỉ:</label>
                    <textarea id="address" name="address" class="form-control" rows="3" placeholder="Nhập địa chỉ..."></textarea>
                </div>
                -->
            </div>

             <!-- Phần Bảo mật -->
            <div class="form-section">
                <h3>Bảo mật</h3>
                 <div class="form-group">
                    <label>Mật khẩu:</label>
                    <div class="password-change-link">
                         <!-- Link đến trang đổi mật khẩu riêng -->
                         <a href="<?php echo $base_url; ?>/pages/settings/change-password.php">Đổi mật khẩu</a>
                    </div>
                </div>
                 <!-- Thêm cài đặt 2FA nếu có -->
            </div>


            <!-- Nút Lưu Thay Đổi -->
            <div class="form-group submit-group">
                <button type="submit" class="btn-submit">Lưu thay đổi</button>
            </div>

        </form>

    </main>
</div>

<!-- JavaScript (Nếu cần cho validation client-side hoặc tương tác khác) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Thêm logic JS ở đây nếu cần
    // Ví dụ: Kiểm tra định dạng email client-side
});
</script>

<?php
// --- Include Footer ---
include $project_root_path . '/includes/footer.php';
?>