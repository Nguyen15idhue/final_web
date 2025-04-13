<script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const hamburger = document.getElementById('hamburger-btn');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
            
            // Optional: Toggle hamburger button visibility
            if (sidebar.classList.contains('open')) {
                hamburger.style.visibility = 'hidden';
            } else {
                hamburger.style.visibility = 'visible';
            }
        }

        // Optional: Close sidebar on window resize if in mobile view
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                const hamburger = document.getElementById('hamburger-btn');
                
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
                hamburger.style.visibility = 'visible';
            }
        });
    </script>
</body>
</html>