<?php
session_start();

// Base URL configuration (Giữ nguyên từ dashboard.php)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
// Quan trọng: Điều chỉnh dirname nếu map-display.php nằm ở thư mục khác dashboard.php
// Giả sử nó nằm cùng cấp với dashboard.php trong thư mục 'pages'
$script_dir = dirname($_SERVER['PHP_SELF']);
// Nếu script_dir là '/pages', chúng ta muốn base_url trỏ đến thư mục gốc dự án
// Điều chỉnh logic này cho phù hợp với cấu trúc thư mục của bạn
$base_project_dir = dirname($script_dir); // Lùi lại một cấp từ /pages
$base_url = $protocol . $domain . ($base_project_dir === '/' ? '' : $base_project_dir); // Xử lý trường hợp gốc là '/'

// Đường dẫn gốc thực tế trên server cho includes (An toàn hơn)
$project_root_path = dirname(__DIR__); // Lùi lại một cấp từ thư mục chứa file này (ví dụ: 'pages')

// Authentication check (Giữ nguyên từ dashboard.php)
if (!isset($_SESSION['user_id'])) {
    // Chuyển hướng về trang login ở thư mục gốc dự án
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// --- Dữ liệu người dùng (Ví dụ - Lấy từ Session) ---
// Bạn có thể cần các biến này cho header hoặc sidebar
$user_fullname = $_SESSION['fullname'] ?? 'Người dùng';

// --- Bao gồm Header ---
// Sử dụng đường dẫn tuyệt đối dựa trên project_root_path
include $project_root_path . '/includes/header.php';
?>

<!-- Nhúng CSS cho trang Map Display -->
<style>
    /* --- Layout Wrapper (Kế thừa từ style.css hoặc định nghĩa lại nếu cần) --- */
    /* .dashboard-wrapper được giả định là đã định nghĩa trong style.css hoặc sidebar.css
       Nếu không, bạn cần định nghĩa lại ở đây hoặc tốt nhất là trong file CSS chung */
    /*
    .dashboard-wrapper {
        display: grid;
        grid-template-columns: auto 1fr; *//* Auto cho sidebar, phần còn lại cho content */
        /*min-height: 100vh;
    }
    */

    /* --- Content Wrapper (Có thể kế thừa từ style.css) --- */
    /*
    .content-wrapper {
        padding: 1.5rem;
    }
    */

    /* --- CSS Cụ thể cho Map Container --- */
    /* Tốt nhất nên đặt ID này vào file style.css hoặc file map.css riêng */
    #map {
        height: 70vh; /* Chiều cao map (điều chỉnh theo ý muốn, vh = viewport height) */
        width: 100%;  /* Chiếm toàn bộ chiều rộng của content-wrapper */
        border-radius: var(--rounded-md); /* Bo góc nhẹ */
        border: 1px solid var(--gray-200); /* Viền nhẹ */
        background-color: var(--gray-100); /* Màu nền chờ load */
    }

    /* --- Responsive (Điều chỉnh layout grid nếu cần thiết) --- */
    @media (max-width: 768px) {
        /* CSS trong style.css/sidebar.css đã xử lý việc stack grid layout */
        #map {
            height: 60vh; /* Giảm chiều cao trên mobile nếu muốn */
        }
        .content-wrapper {
            /* Đảm bảo padding không bị CSS inline của dashboard ghi đè */
            padding: 1rem !important;
        }
    }
</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <?php
    // Bao gồm Sidebar - Sử dụng đường dẫn tuyệt đối
    // Giả sử sidebar.php nằm trong thư mục includes
    include $project_root_path . '/includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="content-wrapper">
        <!-- Tiêu đề trang -->
        <h2 class="text-2xl font-semibold mb-6">Map Display</h2>

        <!-- Phần tử div để hiển thị bản đồ Google Map -->
        <div id="map">
            <!-- Thông báo chờ load hoặc lỗi -->
            <p style="text-align: center; padding-top: 50px; color: var(--gray-500);">
                Đang tải bản đồ...
            </p>
        </div>

        <!-- Có thể thêm các nội dung khác liên quan đến bản đồ ở đây -->
        <!-- Ví dụ: Bảng chú giải, bộ lọc... -->

    </main>
</div>

<!-- =============================================== -->
<!-- == JavaScript cho Google Maps                == -->
<!-- =============================================== -->
<script>
    // Hàm này sẽ được Google Maps API gọi sau khi tải xong
    function initMap() {
        console.log("Google Maps API Loaded. Initializing map...");

        // 1. Xác định tọa độ trung tâm ban đầu (Ví dụ: Hà Nội)
        const initialCenter = { lat: 21.028511, lng: 105.804817 };

        // 2. Lấy phần tử div chứa bản đồ
        const mapElement = document.getElementById('map');

        // 3. Kiểm tra xem phần tử có tồn tại không
        if (!mapElement) {
            console.error("Map container element with ID 'map' not found.");
            // Có thể hiển thị thông báo lỗi thân thiện hơn cho người dùng
            // mapElement.innerHTML = '<p style="color: red; text-align: center;">Lỗi: Không thể tải bản đồ.</p>';
            return;
        }

        // 4. Tạo đối tượng bản đồ mới
        try {
            const map = new google.maps.Map(mapElement, {
                zoom: 13, // Mức zoom ban đầu (số lớn hơn = gần hơn)
                center: initialCenter, // Đặt trung tâm bản đồ
                mapId: 'YOUR_MAP_ID', // Optional: Sử dụng Map ID cho Cloud-based styling
                // Các tùy chọn khác:
                // mapTypeId: 'roadmap', // 'roadmap', 'satellite', 'hybrid', 'terrain'
                // disableDefaultUI: false, // Ẩn/hiện các nút điều khiển mặc định
                // zoomControl: true,
                // mapTypeControl: false,
                // streetViewControl: false,
                // fullscreenControl: true,
            });

             // 5. (Tùy chọn) Thêm một Marker vào bản đồ
            const marker = new google.maps.Marker({
                 position: initialCenter,
                 map: map, // Chỉ định bản đồ để hiển thị marker
                 title: 'Hà Nội', // Tooltip khi hover
                 // icon: 'path/to/custom-marker.png' // Nếu muốn dùng icon tùy chỉnh
            });

            // (Tùy chọn) Thêm InfoWindow khi click vào marker
            const infowindow = new google.maps.InfoWindow({
                content: '<strong>Hà Nội</strong><br>Thủ đô Việt Nam.'
            });

            marker.addListener('click', () => {
                infowindow.open({
                    anchor: marker,
                    map,
                    shouldFocus: false, // Không tự động focus vào info window
                });
            });


            console.log("Map initialized successfully.");

        } catch (error) {
             console.error("Error initializing Google Map:", error);
             mapElement.innerHTML = '<p style="color: red; text-align: center;">Lỗi khi khởi tạo bản đồ. Vui lòng kiểm tra console.</p>';
        }
    }

    // Các hàm JavaScript khác liên quan đến bản đồ có thể đặt ở đây
    // Ví dụ: hàm thêm marker, vẽ polygon, xử lý sự kiện click trên bản đồ...

</script>

<!-- Tải Google Maps API -->
<!-- Đặt ở cuối cùng, sau hàm initMap -->
<!-- Thay YOUR_API_KEY bằng API Key của bạn -->
<!-- callback=initMap: Gọi hàm initMap khi API tải xong -->
<!-- libraries=marker: (Tùy chọn) Tải thư viện Marker nâng cao nếu cần -->
<!-- v=beta: (Tùy chọn) Sử dụng phiên bản beta nếu muốn thử tính năng mới -->
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap&libraries=marker&v=beta">
</script>


<?php
// --- Bao gồm Footer ---
// Sử dụng đường dẫn tuyệt đối
include $project_root_path . '/includes/footer.php';
?>