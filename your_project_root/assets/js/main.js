// File: /assets/js/main.js (hoặc tương tự)

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const body = document.body;

    if (sidebar && overlay) {
        const isOpen = sidebar.classList.contains('open');

        // Toggle class trên sidebar và overlay
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');

        // Chỉ ngăn cuộn body trên mobile
        if (window.innerWidth < 1024) {
            if (!isOpen) { // Đang mở sidebar
                body.classList.add('sidebar-open-mobile'); // Dùng class thay vì style trực tiếp
            } else { // Đang đóng sidebar
                body.classList.remove('sidebar-open-mobile');
            }
        } else {
             // Nếu đang ở desktop, đảm bảo body không bị khóa cuộn
             body.classList.remove('sidebar-open-mobile');
        }

    } else {
        console.error("Sidebar or Sidebar Overlay element not found!");
    }
}

// --- Các Event Listener khác (Escape, Resize) giữ nguyên như trước ---

// (Tùy chọn) Đóng sidebar khi nhấn phím Escape
document.addEventListener('keydown', function(event) {
    const sidebar = document.getElementById('sidebar');
    if (event.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
        toggleSidebar();
    }
});

// (Tùy chọn) Xử lý khi resize cửa sổ
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const body = document.body;

     if (window.innerWidth >= 1024) {
        // Nếu là màn hình desktop
         if (sidebar && sidebar.classList.contains('open')) {
             // Nếu sidebar đang mở (từ mobile chuyển lên), đóng nó đi
             toggleSidebar(); // Gọi hàm để reset đúng cách
         }
         // Đảm bảo body không bị khóa cuộn trên desktop
         body.classList.remove('sidebar-open-mobile');

     }
     // Không cần xử lý gì thêm khi resize xuống mobile,
     // trạng thái ẩn/hiện được quản lý bởi class 'open' và CSS
});