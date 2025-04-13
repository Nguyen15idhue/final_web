<?php
// includes/mobile-top-bar.php

// Biến $base_url cần được định nghĩa ở file gọi include này
// Hoặc bạn có thể tính toán lại ở đây nếu cần, nhưng tốt nhất là truyền từ file gọi
global $base_url; // Sử dụng biến global nếu nó được định nghĩa ở phạm vi ngoài

?>
<header id="mobile-top-bar">
    <!-- Hamburger Button Moved Here -->
    <button id="hamburger-btn" class="hamburger-btn-inside-bar" aria-label="Mở menu" aria-expanded="false" aria-controls="sidebar" onclick="toggleSidebar()">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>

    <!-- (Tùy chọn) Thêm Logo hoặc Tiêu đề Trang cho Mobile -->
    <a href="<?php echo htmlspecialchars($base_url ?? '/'); ?>/pages/dashboard.php" class="mobile-logo-link">
         <!-- Có thể dùng icon hoặc text logo nhỏ -->
         <span class="mobile-logo-text">Đo đạc</span>
    </a>

    <!-- (Tùy chọn) Có thể thêm các nút action khác ở đây -->
</header>