<?php
session_start();

// --- Base URL Configuration ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$script_dir = dirname($_SERVER['PHP_SELF']); // /pages/purchase
$base_project_dir = dirname(dirname($script_dir)); // Lùi 2 cấp
$base_url = $protocol . $domain . ($base_project_dir === '/' || $base_project_dir === '\\' ? '' : $base_project_dir);

// --- Project Root Path for Includes ---
$project_root_path = dirname(dirname(__DIR__));

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// --- Nhận dữ liệu từ POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     // Nếu không phải POST, chuyển về trang chọn gói
    header('Location: ' . $base_url . '/pages/purchase/package.php?error=invalid_access');
    exit;
}

// Lấy và làm sạch dữ liệu đầu vào
$package_id = $_POST['package_id'] ?? null;
$package_name = htmlspecialchars($_POST['package_name'] ?? 'Không xác định'); // Dùng htmlspecialchars ở đây cũng được
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$province = htmlspecialchars($_POST['province'] ?? 'Chưa chọn');
$total_price_from_form = filter_input(INPUT_POST, 'total_price', FILTER_VALIDATE_FLOAT);

// --- Define Package Data Again for Verification (QUAN TRỌNG) ---
// Dữ liệu này phải khớp với dữ liệu ở package.php và details.php
$packages = [
    'monthly' => ['name' => 'Gói 1 Tháng', 'price' => 100000],
    'quarterly' => ['name' => 'Gói 3 Tháng', 'price' => 270000],
    'biannual' => ['name' => 'Gói 6 Tháng', 'price' => 500000],
    'annual' => ['name' => 'Gói 1 Năm', 'price' => 900000],
    'lifetime' => ['name' => 'Gói Vĩnh Viễn', 'price' => 5000000], // Giữ lại để check id
    // Thêm các gói khác nếu cần
];

// --- Validate Inputs & Verify Price ---
// Kiểm tra các giá trị nhận được có hợp lệ không
if (
    !$package_id ||
    !isset($packages[$package_id]) ||
    $package_id === 'lifetime' || // Không cho phép thanh toán gói lifetime qua đây
    $quantity === false || // filter_input trả về false nếu không hợp lệ
    $province === 'Chưa chọn' ||
    $total_price_from_form === false || // filter_input trả về false nếu không hợp lệ
    $total_price_from_form <= 0
) {
     // Dữ liệu không hợp lệ
     // Gửi lại package_id để trang details có thể tải lại đúng gói
    header('Location: ' . $base_url . '/pages/purchase/details.php?package=' . urlencode($package_id ?? '') . '&error=invalid_data');
    exit;
}

// **Xác thực lại giá tiền phía Server**
$base_price = $packages[$package_id]['price'];
$expected_total_price = $base_price * $quantity;

// So sánh giá tính toán và giá gửi từ form (cho phép sai số nhỏ nếu cần)
$price_tolerance = 0.01; // Ví dụ: cho phép sai số 0.01 VND
if (abs($expected_total_price - $total_price_from_form) > $price_tolerance) {
    // Giá không khớp -> có thể đã bị sửa đổi -> Lỗi
     // Gửi lại package_id để trang details có thể tải lại đúng gói và báo lỗi
     header('Location: ' . $base_url . '/pages/purchase/details.php?package=' . urlencode($package_id) . '&error=price_mismatch');
     exit;
     // Hoặc ghi log và hiển thị lỗi chi tiết hơn
     // error_log("Price mismatch for user {$_SESSION['user_id']}. Expected: {$expected_total_price}, Received: {$total_price_from_form}");
     // die("Lỗi: Giá tiền không hợp lệ.");
}

// Sử dụng giá đã xác thực từ server
$verified_total_price = $expected_total_price;

// --- Thông tin Ngân hàng và VietQR ---
// !!! THAY THẾ BẰNG THÔNG TIN THẬT CỦA BẠN !!!
define('BANK_ID', '970418');      // Ví dụ: VietinBank BIN
define('ACCOUNT_NO', '112233445566'); // Số tài khoản thật
define('ACCOUNT_NAME', 'NGUYEN VAN A'); // Tên chủ tài khoản thật
define('BANK_NAME', 'VietinBank'); // Tên ngân hàng để hiển thị
// Template VietQR chuẩn
define('QR_TEMPLATE', '00020101021238570010A00000072701270006%s0115%s0208QRIBFTTA530370454%.0f5802VN62%d%s6304');

// Tạo nội dung chuyển khoản (Ngắn gọn, dễ nhập, chứa thông tin định danh)
// Ví dụ: USER123 MUA GOI annualx1
$user_id_for_desc = $_SESSION['user_id']; // Lấy User ID
$package_id_for_desc = strtoupper($package_id);
$order_description = "USER{$user_id_for_desc} MUA GOI {$package_id_for_desc}x{$quantity}";
// Xử lý cho vào QR Payload (xóa dấu, viết hoa, bỏ khoảng trắng, giới hạn độ dài nếu cần)
$qr_description_raw = preg_replace('/[^A-Z0-9]/', '', strtoupper(str_replace(' ', '', $order_description)));
// Giới hạn độ dài mô tả cho QR nếu cần (VietQR có giới hạn tổng payload)
$qr_description = substr($qr_description_raw, 0, 50); // Ví dụ giới hạn 50 ký tự
// Format tham số 08 cho VietQR (mô tả)
$qr_description_param = '08' . str_pad(strlen($qr_description), 2, '0', STR_PAD_LEFT) . $qr_description;

// Format tham số 62 cho VietQR (tên chủ tài khoản)
$account_name_param = '00' . str_pad(strlen(ACCOUNT_NAME), 2, '0', STR_PAD_LEFT) . ACCOUNT_NAME;


// Tạo payload VietQR (Dùng %.0f cho số tiền để đảm bảo là số nguyên)
$qr_payload = sprintf(
    QR_TEMPLATE,
    BANK_ID,                     // %s: Bank BIN
    ACCOUNT_NO,                  // %s: Account Number
    $verified_total_price,       // %.0f: Amount (đảm bảo là số nguyên dạng float)
    strlen($account_name_param), // %d: Length of Account Name Parameter (bao gồm cả 00xx)
    str_replace(' ','%20', $account_name_param), // %s: Account Name Param URL Encoded (00xx...)
    $qr_description_param        // %s: Description parameter (08xx...)
);

// --- Hàm tính CRC16 cho VietQR (Giữ nguyên) ---
function crc16($data) {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($data); $i++) {
        $crc ^= ord($data[$i]) << 8;
        for ($j = 0; $j < 8; $j++) {
            $crc = ($crc & 0x8000) ? ($crc << 1) ^ 0x1021 : $crc << 1;
        }
    }
    return strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
}

$crc_value = crc16($qr_payload); // Tính CRC
$final_qr_payload = $qr_payload . $crc_value; // Payload hoàn chỉnh cho QR Code

// --- User Info ---
$user_username = $_SESSION['username'] ?? 'Người dùng';

// --- Include Header ---
include $project_root_path . '/includes/header.php';
?>

<!-- CSS cho Trang Thanh Toán -->
<style>
    .payment-summary, .payment-qr-section {
        background-color: white;
        padding: 2rem;
        border-radius: var(--rounded-lg);
        border: 1px solid var(--gray-200);
        margin-bottom: 2rem;
    }

    .payment-summary h3, .payment-qr-section h3 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-semibold);
        color: var(--gray-800);
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        padding-bottom: 0.75rem;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center; /* Căn giữa nếu text dài */
        margin-bottom: 0.75rem;
        font-size: var(--font-size-base);
        color: var(--gray-700);
        gap: 1rem; /* Khoảng cách giữa label và value */
        flex-wrap: wrap; /* Cho phép xuống dòng nếu không đủ chỗ */
    }
    .summary-item span:first-child { flex-shrink: 0;} /* Không co label */
    .summary-item strong {
        font-weight: var(--font-semibold); /* Đậm hơn medium */
        color: var(--gray-900);
        text-align: right; /* Căn phải giá trị */
    }
    .summary-total {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-300);
        font-size: 1.25rem; /* --font-size-xl */
        font-weight: var(--font-bold);
        color: var(--primary-600);
    }

    .payment-qr-section {
        text-align: center;
    }

    #qrcode {
        width: 250px; /* Kích thước QR */
        height: 250px;
        margin: 1rem auto 1.5rem auto; /* Căn giữa QR */
        border: 5px solid white; /* Khung trắng quanh QR */
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex; /* Để căn giữa placeholder nếu JS chưa chạy */
        align-items: center;
        justify-content: center;
        background-color: var(--gray-100); /* Nền chờ */
    }
    #qrcode img { /* Style cho thẻ img do thư viện JS tạo ra */
        display: block;
        width: 100% !important;
        height: 100% !important;
        object-fit: contain; /* Đảm bảo QR không bị méo */
    }

    .bank-details p {
        margin-bottom: 0.5rem;
        color: var(--gray-600);
        font-size: var(--font-size-sm); /* Chữ nhỏ hơn chút */
    }
     .bank-details strong {
         color: var(--gray-800);
         font-weight: var(--font-semibold);
     }
     .bank-details code {
        background-color: var(--gray-100);
        padding: 0.2em 0.5em;
        border-radius: var(--rounded-sm);
        font-family: monospace;
        color: var(--gray-700);
        cursor: pointer;
        border: 1px solid var(--gray-200);
        display: inline-block; /* Để có padding */
        margin-left: 5px;
        position: relative; /* Cho tooltip nếu muốn */
     }
    .bank-details code:hover::after {
        content: 'Sao chép';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: var(--gray-800);
        color: white;
        padding: 2px 6px;
        border-radius: var(--rounded-sm);
        font-size: 0.7rem;
        white-space: nowrap;
        margin-bottom: 4px;
    }


    .payment-instructions {
        margin-top: 1.5rem;
        font-size: var(--font-size-sm);
        color: var(--gray-500);
        line-height: 1.6;
    }


     @media (max-width: 768px) {
        .content-wrapper {
            padding: 1rem !important;
        }
        .payment-summary, .payment-qr-section {
            padding: 1.5rem;
        }
         #qrcode {
            width: 200px;
            height: 200px;
        }
        .payment-container {
             grid-template-columns: 1fr; /* Stack 2 cột trên mobile */
        }
    }

</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php include $project_root_path . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="content-wrapper">
        <h2 class="text-2xl font-semibold mb-6">Thanh toán đơn hàng</h2>

        <div class="payment-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">

            <!-- Cột Tóm tắt đơn hàng -->
            <section class="payment-summary">
                <h3>Thông tin đơn hàng</h3>
                <div class="summary-item">
                    <span>Gói dịch vụ:</span>
                    <strong><?php echo htmlspecialchars($package_name); ?></strong>
                </div>
                <div class="summary-item">
                    <span>Số lượng:</span>
                    <strong><?php echo $quantity; ?> tài khoản</strong>
                </div>
                <div class="summary-item">
                    <span>Tỉnh/Thành phố:</span>
                    <strong><?php echo $province; ?></strong>
                </div>
                <div class="summary-item summary-total">
                    <span>Tổng thanh toán:</span>
                    <strong><?php echo number_format($verified_total_price, 0, ',', '.'); ?> đ</strong>
                </div>
                 <!-- Có thể thêm mã đơn hàng ở đây nếu bạn tạo mã đơn hàng -->
                 <!--
                 <div class="summary-item" style="margin-top: 1rem; font-size: var(--font-size-sm);">
                    <span>Mã đơn hàng:</span>
                    <strong>YOUR_ORDER_ID_HERE</strong>
                 </div>
                 -->
            </section>

            <!-- Cột Mã QR và Hướng dẫn -->
            <section class="payment-qr-section">
                <h3>Quét mã để thanh toán</h3>
                <p style="font-size: var(--font-size-sm); color: var(--gray-600); margin-bottom: 1rem;">Sử dụng ứng dụng ngân hàng hoặc ví điện tử hỗ trợ VietQR.</p>
                <!-- Div để hiển thị QR Code -->
                <div id="qrcode">
                     <p style="font-size: var(--font-size-sm); color: var(--gray-500);">Đang tạo mã QR...</p>
                </div>

                <div class="bank-details">
                    <p><strong>Thông tin chuyển khoản thủ công:</strong></p>
                    <p>Ngân hàng: <strong><?php echo defined('BANK_NAME') ? BANK_NAME : BANK_ID; ?></strong></p>
                    <p>Số tài khoản: <strong id="account-number"><?php echo ACCOUNT_NO; ?></strong> <code title="Sao chép số tài khoản" data-copy-target="#account-number">Copy</code></p>
                    <p>Chủ tài khoản: <strong><?php echo ACCOUNT_NAME; ?></strong></p>
                    <p>Số tiền: <strong id="payment-amount"><?php echo number_format($verified_total_price, 0, ',', '.'); ?> đ</strong> <code title="Sao chép số tiền" data-copy-target="#payment-amount">Copy</code></p>
                    <p>Nội dung: <strong id="payment-description"><?php echo htmlspecialchars($order_description); ?></strong> <code title="Sao chép nội dung" data-copy-target="#payment-description">Copy</code></p>
                </div>

                <p class="payment-instructions">
                    <strong>Lưu ý:</strong> Vui lòng nhập <strong>chính xác</strong> nội dung chuyển khoản để hệ thống tự động xử lý.
                    Sau khi chuyển khoản thành công, tài khoản của bạn sẽ được kích hoạt (thường trong vòng vài phút).
                </p>
            </section>

        </div>

    </main>
</div>

<!-- Nhúng thư viện qrcode.min.js (Tải về hoặc dùng CDN) -->
<!-- Ví dụ dùng CDN: -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecJvqEspN2r0ZWCkUranLMijLqtDbBeel7NDGceUPGURAuBrwnqJBZAumCiHgNZeVScBMA/5PkqG8UAg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- Hoặc tải về và đặt link đúng: -->
<!-- <script src="<?php echo $base_url; ?>/assets/js/qrcode.min.js"></script> -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Tạo QR Code ---
    const qrCodeElement = document.getElementById('qrcode');
    const qrData = '<?php echo addslashes($final_qr_payload); // Dùng addslashes để tránh lỗi JS nếu payload có ký tự đặc biệt ?>';

    if (qrCodeElement && qrData && typeof QRCode !== 'undefined') {
        try {
            qrCodeElement.innerHTML = ''; // Xóa placeholder
            new QRCode(qrCodeElement, {
                text: qrData,
                width: 250, // Nên đồng bộ với CSS
                height: 250,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M // Mức sửa lỗi M (Medium)
            });
            // console.log("QR Code Generated for payload:", qrData);
        } catch (error) {
            console.error("Error generating QR Code:", error);
            qrCodeElement.innerHTML = '<p style="color: red;">Lỗi tạo mã QR.</p>';
        }
    } else {
         console.error("QR Code element, data, or QRCode library not found.");
         if(qrCodeElement) qrCodeElement.innerHTML = '<p style="color: red;">Không thể tải thư viện QR.</p>';
    }

    // --- Chức năng Copy ---
    const copyButtons = document.querySelectorAll('.bank-details code[data-copy-target]');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetSelector = this.getAttribute('data-copy-target');
            const targetElement = document.querySelector(targetSelector);
            if (targetElement) {
                const textToCopy = targetElement.innerText.replace(/đ|\.|,/g, '').trim(); // Lấy text, bỏ đơn vị tiền tệ, dấu chấm phẩy nếu là số tiền
                 navigator.clipboard.writeText(textToCopy)
                    .then(() => {
                        // Tạm thời thay đổi text của nút copy
                        const originalText = this.innerText;
                        this.innerText = 'Đã chép!';
                        this.style.backgroundColor = 'var(--primary-100)';
                        setTimeout(() => {
                            this.innerText = originalText;
                             this.style.backgroundColor = ''; // Reset background
                        }, 1500); // Hiện trong 1.5 giây
                    })
                    .catch(err => {
                        console.error('Lỗi sao chép: ', err);
                        alert('Không thể tự động sao chép. Vui lòng chọn và sao chép thủ công.');
                    });
            }
        });
    });

    // --- (Tùy chọn) Logic kiểm tra trạng thái thanh toán bằng AJAX ---
    // Bạn có thể thêm vào đây một hàm gọi định kỳ (setInterval) để kiểm tra
    // xem user_id này đã thanh toán cho đơn hàng này chưa (dựa vào webhook hoặc kiểm tra DB)
    // và tự động chuyển hướng người dùng đến trang thành công nếu đã thanh toán.
    /*
    const checkPaymentStatus = () => {
        fetch('/api/check-payment-status.php?order_id=YOUR_ORDER_ID') // Thay bằng API của bạn
            .then(response => response.json())
            .then(data => {
                if (data.status === 'paid') {
                    window.location.href = '<?php echo $base_url; ?>/purchase/success.php'; // Chuyển đến trang thành công
                } else {
                    console.log('Payment status:', data.status);
                }
            })
            .catch(error => console.error('Error checking payment status:', error));
    };
    // Gọi kiểm tra sau 10 giây và lặp lại mỗi 30 giây chẳng hạn
    setTimeout(() => {
        checkPaymentStatus(); // Kiểm tra lần đầu
        setInterval(checkPaymentStatus, 30000); // Kiểm tra mỗi 30 giây
    }, 10000);
    */

});
</script>

<?php
// --- Include Footer ---
include $project_root_path . '/includes/footer.php';
?>