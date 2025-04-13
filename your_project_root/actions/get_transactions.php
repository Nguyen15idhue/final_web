<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Lấy các tham số từ request
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : '';
$toDate = isset($_GET['toDate']) ? $_GET['toDate'] : '';

// Giả lập dữ liệu - Thay thế bằng truy vấn database thực tế
$transactions = [
    [
        'id' => '#TRX123456',
        'date' => '2025-04-12 10:15:23',
        'type' => 'Mua Gói Premium',
        'description' => 'Gói Premium 90 ngày',
        'amount' => -799000,
        'status' => 'success'
    ],
    [
        'id' => '#TRX123455',
        'date' => '2025-04-11 15:30:45',
        'type' => 'Gia Hạn Gói Basic',
        'description' => 'Gia hạn gói Basic 30 ngày',
        'amount' => -299000,
        'status' => 'pending'
    ],
    // Thêm dữ liệu mẫu khác...
];

// Mô phỏng tổng số trang
$totalPages = 5;

echo json_encode([
    'transactions' => $transactions,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'summary' => [
        'totalTransactions' => 15,
        'totalSpent' => 2897000,
        'monthlyTransactions' => 3
    ]
]);