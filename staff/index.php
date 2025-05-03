<?php
require_once 'config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#c41c1c',
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-white">
    <!-- Include the sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content with margin to account for fixed sidebar -->
    <div class="ml-64 p-8 bg-gray-50 min-h-screen">
        <?php
        switch ($page) {
            case 'products':
                include 'pages/products.php';
                break;
            case 'orders':
                include 'pages/orders.php';
                break;
            case 'offers':
                include 'pages/offers.php';
                break;
            case 'customers':
                include 'pages/customers.php';
                break;
            case 'messages':
                include 'pages/messages.php';
                break;
            case 'blogs':
                include 'pages/blogs.php';
                break;
            default:
                include 'pages/dashboard_home.php';
        }
        ?>
    </div>
</body>
</html>