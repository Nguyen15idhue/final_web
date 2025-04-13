<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Đo đạc</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Your CSS files -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <link rel="stylesheet" href="/assets/css/map-display.css">
    
    <!-- Add this style for dashboard specific layout -->
    <style>
        /* Dashboard Layout */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        .content-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }
        .content-wrapper h2 {
            margin-bottom: 20px; /* hoặc giá trị bạn muốn */
            }


        /* Stats Cards */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card .icon {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .stat-card .icon.success { color: #28a745; }
        .stat-card .icon.warning { color: #ffc107; }
        .stat-card .icon.info { color: #17a2b8; }

        .stat-card h3 {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        /* Recent Activity */
        .recent-activity {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .recent-activity h3 {
            color: #333;
            margin-bottom: 1rem;
        }

        .recent-activity p {
            color: #6c757d;
        }

        /* Responsive Layout */
        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding: 1rem;
            }
            .content-wrapper h2 {
                margin-bottom: 20px; /* hoặc giá trị bạn muốn */
                }


            .stats {
                grid-template-columns: 1fr;
            }

            /* Add padding-top to account for hamburger button */
            .content {
                padding-top: 3.5rem;
            }
        }
    </style>
</head>
<body>