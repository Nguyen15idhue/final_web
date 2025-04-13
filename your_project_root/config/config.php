<?php
// /assets/config/config.php

// Ngăn truy cập trực tiếp vào file config này (tùy chọn nhưng nên có)
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access Denied'); // Hoặc chuyển hướng người dùng
}

/**
 * Định nghĩa các gói tài khoản
 *
 * Key của mảng (vd: 'monthly', 'yearly') là ID duy nhất cho gói.
 * Các thuộc tính trong mỗi gói:
 * - id: (String) ID định danh gói (giống key).
 * - name: (String) Tên hiển thị của gói.
 * - duration_months: (Integer|null) Thời hạn gói tính bằng tháng. `null` cho gói vĩnh viễn.
 * - price: (Float|Integer) Giá của gói.
 * - currency: (String) Đơn vị tiền tệ (vd: 'VNĐ', '$').
 * - features: (Array) Danh sách các tính năng của gói (mỗi phần tử là một string).
 * - description: (String) Mô tả ngắn gọn về gói (tùy chọn).
 * - highlight: (Boolean) Đánh dấu gói nổi bật (vd: 'Bán chạy nhất', 'Tiết kiệm nhất').
 */
$account_packages = [
    'monthly' => [
        'id' => 'monthly',
        'name' => 'Gói 1 Tháng',
        'duration_months' => 1,
        'price' => 99000,
        'currency' => 'VNĐ',
        'features' => [
            'Truy cập đầy đủ tính năng cơ bản',
            '5 lượt đo đạc mỗi ngày',
            'Hỗ trợ qua email',
            'Cập nhật tính năng nhỏ',
        ],
        'description' => 'Linh hoạt cho nhu cầu sử dụng ngắn hạn hoặc thử nghiệm.',
        'highlight' => false,
    ],
    'quarterly' => [
        'id' => 'quarterly',
        'name' => 'Gói 3 Tháng',
        'duration_months' => 3,
        'price' => 270000, // Tiết kiệm hơn 1 tháng
        'currency' => 'VNĐ',
        'features' => [
            'Truy cập đầy đủ tính năng cơ bản',
            '10 lượt đo đạc mỗi ngày',
            'Hỗ trợ qua email & chat',
            'Cập nhật tính năng nhỏ',
            'Ưu đãi nhỏ khi gia hạn',
        ],
        'description' => 'Tiết kiệm hơn so với gói tháng, phù hợp sử dụng thường xuyên.',
        'highlight' => false,
    ],
    'half_yearly' => [
        'id' => 'half_yearly',
        'name' => 'Gói 6 Tháng',
        'duration_months' => 6,
        'price' => 500000, // Tiết kiệm hơn 3 tháng
        'currency' => 'VNĐ',
        'features' => [
            'Truy cập đầy đủ tính năng nâng cao',
            '20 lượt đo đạc mỗi ngày',
            'Hỗ trợ ưu tiên (Email, Chat, Phone)',
            'Nhận các bản cập nhật lớn',
            'Lưu trữ lịch sử đo đạc 6 tháng',
        ],
        'description' => 'Lựa chọn phổ biến, cân bằng giữa chi phí và thời gian sử dụng.',
        'highlight' => true, // Đánh dấu gói này là nổi bật
    ],
    'yearly' => [
        'id' => 'yearly',
        'name' => 'Gói 1 Năm',
        'duration_months' => 12,
        'price' => 900000, // Tiết kiệm nhất theo tháng
        'currency' => 'VNĐ',
        'features' => [
            'Truy cập đầy đủ tính năng nâng cao',
            '50 lượt đo đạc mỗi ngày',
            'Hỗ trợ VIP 24/7',
            'Nhận tất cả bản cập nhật',
            'Lưu trữ lịch sử đo đạc 1 năm',
            'Công cụ phân tích dữ liệu',
        ],
        'description' => 'Tối ưu chi phí cho người dùng cam kết và sử dụng chuyên sâu.',
        'highlight' => false,
    ],
    'lifetime' => [
        'id' => 'lifetime',
        'name' => 'Gói Vĩnh Viễn',
        'duration_months' => null, // Đánh dấu là vĩnh viễn
        'price' => 2999000,
        'currency' => 'VNĐ',
        'features' => [
            'Tất cả tính năng của gói 1 năm',
            'Không giới hạn lượt đo đạc',
            'Hỗ trợ VIP trọn đời',
            'Nhận mọi cập nhật tương lai miễn phí',
            'Lưu trữ lịch sử không giới hạn',
            'Thanh toán một lần duy nhất',
        ],
        'description' => 'Đầu tư một lần, sở hữu trọn đời mọi tính năng và cập nhật.',
        'highlight' => false,
    ],
];

?>