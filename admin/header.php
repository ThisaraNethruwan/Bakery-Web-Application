<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin") {
    // Redirect to login page if not logged in or not an admin
    header("Location: index.php");
    exit;
}

// Get user name from session
$user_name = $_SESSION["user_name"];

// Get the current page title
$page_title = "Dashboard"; // Default
$current_page = basename($_SERVER['PHP_SELF']);

switch($current_page) {
    case 'admin.php':
        $page_title = "Dashboard";
        break;
    case 'account_management.php':
        $page_title = "Account Management";
        break;
    case 'products.php':
        $page_title = "Products";
        break;
    case 'orders.php':
        $page_title = "Orders";
        break;
    case 'reports.php':
        $page_title = "Reports";
        break;
    case 'settings.php':
        $page_title = "Settings";
        break;
    case 'profile.php':
        $page_title = "My Profile";
        break;
    default:
        $page_title = "Dashboard";
}
?>

<!-- Main Content Header -->
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold"><?php echo $page_title; ?></h1>
        <p class="text-gray-600">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</p>
    </div>
    <div class="flex items-center space-x-4">
        <div class="relative">
            <button class="p-2 bg-white rounded-full shadow-md">
                <i class="fas fa-bell text-gray-600"></i>
            </button>
            <div class="absolute top-0 right-0 h-3 w-3 bg-red-500 rounded-full"></div>
        </div>
        <div>
            <button class="p-2 bg-white rounded-full shadow-md">
                <i class="fas fa-envelope text-gray-600"></i>
            </button>
        </div>
    </div>
</div>