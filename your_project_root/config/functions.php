<?php
// includes/functions.php
// Chứa các hàm helper dùng chung cho toàn bộ dự án

if (!function_exists('get_transaction_status_display')) {
    /**
     * Lấy text và class CSS cho trạng thái giao dịch/hoa hồng.
     * Hàm này được thiết kế để trả về thông tin hiển thị phù hợp cho các trạng thái phổ biến.
     *
     * @param string $status Trạng thái đầu vào (ví dụ: 'completed', 'pending', 'failed', 'paid').
     * @return array Mảng chứa 'text' và 'class' CSS tương ứng.
     */
    function get_transaction_status_display($status) {
        switch (strtolower($status)) { // Chuyển thành chữ thường để xử lý linh hoạt hơn
            case 'completed':
            case 'paid': // Có thể coi 'paid' tương đương 'completed' về mặt hiển thị
                return ['text' => 'Hoàn thành', 'class' => 'status-completed'];
            case 'pending':
                return ['text' => 'Chờ xử lý', 'class' => 'status-pending'];
            case 'failed':
                return ['text' => 'Thất bại', 'class' => 'status-failed'];
             case 'cancelled':
                return ['text' => 'Đã hủy', 'class' => 'status-cancelled'];
            // Thêm các trạng thái khác nếu cần
            // case 'processing':
            //     return ['text' => 'Đang xử lý', 'class' => 'status-processing'];
            default:
                return ['text' => 'Không xác định', 'class' => 'status-unknown'];
        }
    }
}

// ----- Thêm các hàm helper khác bạn muốn dùng chung vào đây -----

// Ví dụ: Hàm định dạng tiền tệ (nếu bạn muốn dùng nhất quán)
if (!function_exists('format_currency')) {
    /**
     * Định dạng số thành chuỗi tiền tệ Việt Nam.
     *
     * @param float|int $amount Số tiền cần định dạng.
     * @param string $currency_symbol Ký hiệu tiền tệ (mặc định là 'đ').
     * @return string Chuỗi tiền tệ đã định dạng.
     */
    function format_currency($amount, $currency_symbol = 'đ') {
        // Kiểm tra nếu không phải là số thì trả về chuỗi rỗng hoặc giá trị mặc định
        if (!is_numeric($amount)) {
            // Có thể trả về 'N/A', '0đ', hoặc '' tùy bạn muốn
            return '0' . $currency_symbol;
        }
        // number_format(số, số chữ số thập phân, dấu ngăn cách thập phân, dấu ngăn cách hàng nghìn)
        return number_format($amount, 0, ',', '.') . $currency_symbol;
    }
}

?>