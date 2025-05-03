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

// Get user information
$user_id = $_SESSION["user_id"];

// Database connection
include_once('db_connect.php');

// Fetch profile image and user role from the user_accounts table
$profile_query = "SELECT name, email, phone, profile_image, user_type, created_at FROM user_accounts WHERE id = ?";
$stmt = $conn->prepare($profile_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile_result = $stmt->get_result();
$user_data = $profile_result->fetch_assoc();
$profile_image = $user_data['profile_image'] ?? 'accounticon.jpg'; // Default image if not found
$user_name = $user_data['name'];
$user_role = $user_data['user_type'];
$user_email = $user_data['email'];
$stmt->close();

// Don't close the connection here as it may be needed in the including file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nishan Bakery - Admin Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Import -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { 
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f7fa;
        }
        .sidebar { 
            width: 260px; 
            height: 100vh; 
            background: linear-gradient(135deg, #ca1212, #cf142a);
            color: white; 
            position: fixed;
            transition: all 0.3s ease;
            overflow-y: auto;
            z-index: 1000;
        }
        .main-content { 
            margin-left: 260px; 
            padding: 20px;
            transition: all 0.3s ease;
        }
        .profile-img { 
            width: 100px; 
            height: 100px; 
            border-radius: 50%; 
            border: 4px solid rgba(255, 255, 255, 0.2);
            object-fit: cover;
        }
        .nav-item {
            transition: all 0.2s ease;
            border-radius: 8px;
            margin-bottom: 5px;
        }
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }
        
        /* Media query for responsive sidebar */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            .sidebar.open {
                width: 260px;
            }
            .main-content {
                margin-left: 0;
            }
            .main-content.shifted {
                margin-left: 260px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Component -->
    <div class="sidebar" id="sidebar">
        <div class="p-6 text-center">
            <div class="flex justify-center">
                <div class="relative">
                    <!-- Profile Picture -->
                    <img src="<?php echo !empty($profile_image) ? htmlspecialchars($profile_image) : 'accounticon.jpg'; ?>" 
                         alt="Profile" class="profile-img">
                    <!-- Online Status Indicator -->
                    <div class="absolute bottom-1 right-1 bg-green-500 h-4 w-4 rounded-full border-2 border-white"></div>
                </div>
            </div>
            <h2 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($user_name); ?></h2>
            <p class="text-sm text-gray-200 mb-1"><?php echo ucfirst(htmlspecialchars($user_role)); ?></p>
            <p class="text-xs text-gray-300"><?php echo htmlspecialchars($user_email); ?></p>
        </div>
        
        <nav class="mt-6 px-4">
            <div class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?> px-4 py-3">
                <a href="admin.php" class="flex items-center">
                    <i class="fas fa-tachometer-alt w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
            </div>
            
            
            <!-- Account Management Section -->
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-gray-300 uppercase">Account Management</p>
            </div>
            
            <div class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'addaccount.php' ? 'active' : ''; ?> px-4 py-3">
                <a href="addaccount.php" class="flex items-center">
                    <i class="fas fa-user-plus w-6"></i>
                    <span class="ml-2">Add Account</span>
                </a>
            </div>
            
            <div class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'deleteaccounts.php' ? 'active' : ''; ?> px-4 py-3">
                <a href="deleteaccounts.php" class="flex items-center">
                    <i class="fas fa-user-minus w-6"></i>
                    <span class="ml-2">Delete Accounts</span>
                </a>
            </div>
            
            <div class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'resetpassword.php' ? 'active' : ''; ?> px-4 py-3">
                <a href="resetpassword.php" class="flex items-center">
                    <i class="fas fa-key w-6"></i>
                    <span class="ml-2">Reset Password</span>
                </a>
            </div>
            
            
            <div class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'viewprofile.php' ? 'active' : ''; ?> px-4 py-3">
                <a href="viewprofile.php" class="flex items-center">
                    <i class="fas fa-user w-6"></i>
                    <span class="ml-2">My Profile</span>
                </a>
            </div>
            
            <div class="nav-item px-4 py-3 mt-6">
                <a href="javascript:void(0);" onclick="confirmLogout();" class="flex items-center text-yellow-200">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-2">Logout</span>
                </a>
            </div>
        </nav>
    </div>
    
    <!-- Toggle button for mobile -->
    <div class="fixed top-4 left-4 z-50 lg:hidden">
        <button id="sidebarToggle" class="bg-red-600 text-white p-2 rounded-md">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <!-- Main content container (to be filled by including pages) -->
    <div class="main-content" id="mainContent">
        <!-- Page content will be placed here -->
    </div>
    
    <script>
        // Mobile sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    mainContent.classList.toggle('shifted');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isMobile = window.innerWidth <= 768;
                const isOutsideSidebar = !sidebar.contains(event.target);
                const isNotToggleButton = !sidebarToggle.contains(event.target);
                
                if (isMobile && isOutsideSidebar && isNotToggleButton && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    mainContent.classList.remove('shifted');
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('open');
                    mainContent.classList.remove('shifted');
                }
            });
        });
        
        // Function to handle logout with confirmation
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                // Clear session data
                <?php if (isset($_SESSION)): ?>
                // You can add PHP session destruction code server-side
                <?php endif; ?>
                
                // Redirect to login page
                window.location.href = 'http://localhost/bakery/index.php';
            }
        }
    </script>
</body>
</html>