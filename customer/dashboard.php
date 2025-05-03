<?php
include "nav.php";

// Check if user is logged in and is a customer
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "customer") {
    // Redirect to login page if not logged in or not a customer
    header("Location:http://localhost/bakery/index.php");
    exit;
}
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Nishan_Bakery";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user information
$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"];
$userEmail = $_SESSION["user_email"];

// Fetch additional user details
$stmt = $conn->prepare("SELECT * FROM user_accounts WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // Handle case where user data can't be found
    $user = [
        'profile_image' => 'uploads/default-profile.png',
        'points' => 0
    ];
}
function getUserLoyaltyPoints($userId) {
    global $conn;

    $stmt = mysqli_prepare($conn, "SELECT points FROM loyalty_points WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        return $row['points'];
    }

    return 0; // Return 0 if no points are found
}

// Fetch the logged-in user's loyalty points
$userId = $_SESSION['user_id'] ?? 0; // Assuming user_id is stored in the session
$userLoyaltyPoints = getUserLoyaltyPoints($userId);

// Get total orders count
$totalOrdersStmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$totalOrdersStmt->bind_param("i", $userId);
$totalOrdersStmt->execute();
$totalOrdersResult = $totalOrdersStmt->get_result();
$totalOrders = $totalOrdersResult->fetch_assoc()['total'] ?? 0;

// Get pending orders count
$pendingOrdersStmt = $conn->prepare("SELECT COUNT(*) as pending FROM orders WHERE user_id = ? AND status = 'pending'");
$pendingOrdersStmt->bind_param("i", $userId);
$pendingOrdersStmt->execute();
$pendingOrdersResult = $pendingOrdersStmt->get_result();
$pendingOrders = $pendingOrdersResult->fetch_assoc()['pending'] ?? 0;

// Get ongoing orders count (orders that are in progress but not completed or cancelled)
$ongoingOrdersStmt = $conn->prepare("SELECT COUNT(*) as ongoing FROM orders WHERE user_id = ? AND status IN ('accepted', 'processing', 'ready_to_delivered', 'on_the_way')");
$ongoingOrdersStmt->bind_param("i", $userId);
$ongoingOrdersStmt->execute();
$ongoingOrdersResult = $ongoingOrdersStmt->get_result();
$ongoingOrders = $ongoingOrdersResult->fetch_assoc()['ongoing'] ?? 0;

// Get recent orders (last 5 days)
$fiveDaysAgo = date('Y-m-d H:i:s', strtotime('-5 days'));
$recentOrdersStmt = $conn->prepare("
    SELECT id as order_id, created_at as order_date, products, total_amount as total, status
    FROM orders 
    WHERE user_id = ? AND created_at >= ?
    ORDER BY created_at DESC
    LIMIT 5
");
$recentOrdersStmt->bind_param("is", $userId, $fiveDaysAgo);
$recentOrdersStmt->execute();
$recentOrdersResult = $recentOrdersStmt->get_result();
$recentOrders = [];

while ($row = $recentOrdersResult->fetch_assoc()) {
    // Count items from the products JSON field
    $products = json_decode($row['products'], true);
    $itemCount = count($products);
    $row['items'] = $itemCount;
    $recentOrders[] = $row;
}



// Get active orders (orders that are not delivered or cancelled)
$activeOrdersStmt = $conn->prepare("
    SELECT id as order_id, status 
    FROM orders 
    WHERE user_id = ? AND status NOT IN ('delivered', 'completed', 'cancelled')
    ORDER BY created_at DESC
    LIMIT 1
");
$activeOrdersStmt->bind_param("i", $userId);
$activeOrdersStmt->execute();
$activeOrderResult = $activeOrdersStmt->get_result();
$activeOrder = $activeOrderResult->fetch_assoc();

// Define the order progress steps in sequence
$orderSteps = ['pending', 'accepted', 'processing', 'ready_to_delivered', 'on_the_way', 'delivered'];

// Calculate the progress percentage if there's an active order
$progressPercentage = 0;
$currentStatus = '';
if ($activeOrder) {
    $currentStatus = strtolower($activeOrder['status']);
    $currentStepIndex = array_search($currentStatus, $orderSteps);
    
    if ($currentStepIndex !== false) {
        // Calculate progress percentage based on current step
        $progressPercentage = ($currentStepIndex / (count($orderSteps) - 1)) * 100;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nishan Bakery - Customer Dashboard</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- ApexCharts for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bakery-primary': '#e53e3e',
                        'bakery-secondary': '#f56565',
                        'bakery-light': '#fdf2f2',
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7fafc;
        }
        
        .dashboard-card {
            transition: all 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .order-progress-line {
            height: 3px;
            background: #e2e8f0;
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            transform: translateY(-50%);
            z-index: 1;
        }
        
        .order-progress-line-active {
            background: #e53e3e;
            transition: width 0.5s ease;
        }
        
        .order-step {
            z-index: 2;
            background: white;
            position: relative;
        }
        
        .order-step-active .step-icon {
            background-color: #e53e3e;
            color: white;
        }
        
        .order-step-completed .step-icon {
            background-color: #48bb78;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 mt-16">
        <!-- Welcome Banner -->
<div class="bg-gradient-to-r from-bakery-primary to-bakery-secondary rounded-2xl shadow-lg p-6 mb-8">
    <div class="flex flex-col md:flex-row justify-between items-center">
        <div class="text-white mb-4 md:mb-0">
            <h1 class="text-3xl md:text-4xl font-bold mb-1">Hello, <?php echo htmlspecialchars($userName); ?>!</h1>
            <p class="text-bakery-light text-lg">Welcome to your Nishan Bakery dashboard</p>
        </div>
        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-4 text-center">
            <div class="text-white text-sm font-medium">LOYALTY POINTS</div>
            <div class="text-white text-3xl font-bold"><?php echo number_format($userLoyaltyPoints); ?></div>
            <a href="cart.php" class="inline-block mt-2 text-xs bg-white text-bakery-primary font-bold py-1 px-3 rounded-full hover:bg-gray-100 transition-colors">
                Redeem Points
            </a>
        </div>
    </div>
</div>
        
        <!-- Dashboard Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Orders Card -->
            <div class="dashboard-card bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm">Total Orders</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $totalOrders; ?></h3>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-lg">
                            <i class="fas fa-shopping-bag text-indigo-500 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="cus-orders.php" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                            View all orders
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Pending Orders Card -->
            <div class="dashboard-card bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm">Pending Orders</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $pendingOrders; ?></h3>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-lg">
                            <i class="fas fa-clock text-yellow-500 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="cus-orders.php?filter=pending" class="text-sm text-yellow-600 hover:text-yellow-800 flex items-center">
                            View pending orders
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Ongoing Orders Card -->
            <div class="dashboard-card bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm">Ongoing Orders</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $ongoingOrders; ?></h3>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class="fas fa-spinner text-blue-500 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="cus-orders.php?filter=ongoing" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            View ongoing orders
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
     <!-- Order Status Progress -->
<div class="lg:col-span-2">
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Order Status Journey</h2>
        
        <?php if ($activeOrder): ?>
            <div class="mb-3 text-sm text-gray-600">
                <span class="font-medium">Order #<?php echo $activeOrder['order_id']; ?></span> is currently 
                <span class="font-semibold text-bakery-primary"><?php echo ucfirst($currentStatus); ?></span>
            </div>
            
            <div class="relative mt-8 pb-4">
                <!-- Progress Line -->
                <div class="order-progress-line"></div>
                <div class="order-progress-line order-progress-line-active" style="width: <?php echo $progressPercentage; ?>%;"></div>
                
                <!-- Steps -->
                <div class="flex justify-between relative">
                    <?php 
                    foreach ($orderSteps as $index => $step):
                        $currentStepIndex = array_search($currentStatus, $orderSteps);
                        $stepClass = "";
                        
                        if ($index < $currentStepIndex) {
                            $stepClass = "order-step-completed"; // completed steps
                        } elseif ($index == $currentStepIndex) {
                            $stepClass = "order-step-active"; // current step
                        }
                        
                        $stepIcon = $statusInfo[$step]['icon'] ?? 'fa-circle';
                    ?>
                    <div class="order-step <?php echo $stepClass; ?> flex flex-col items-center">
                        <div class="step-icon w-10 h-10 rounded-full <?php echo ($index > $currentStepIndex) ? 'bg-gray-200' : ''; ?> flex items-center justify-center mb-2">
                            <i class="fas <?php echo $stepIcon; ?> <?php echo ($index > $currentStepIndex) ? 'text-gray-500' : ''; ?>"></i>
                        </div>
                        <span class="text-xs font-medium <?php echo ($index > $currentStepIndex) ? 'text-gray-500' : ''; ?>">
                            <?php echo ucfirst(str_replace(" to delivered", "", $step)); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-100">
                <p class="text-gray-600 text-sm">
                    <span class="font-medium">Pro Tip:</span> Track your order in real-time. Updates may take a few minutes to reflect.
                </p>
            </div>
        <?php else: ?>
            <div id="no-active-orders" class="py-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-500 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">No Active Orders</h3>
                <p class="text-gray-500 mb-4">All your orders have been completed or you don't have any orders yet.</p>
                <a href="shop.php" class="inline-block bg-bakery-primary text-white font-medium py-2 px-6 rounded-lg hover:bg-bakery-secondary transition-colors">
                    Place a New Order
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
            <!-- Quick Actions -->
            <div class="lg:col-span-1 mt-8">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
                    
                    <div class="space-y-3">
                        <!-- Shop Now -->
                        <a href="shop.php" class="dashboard-card flex items-center p-4 rounded-lg bg-bakery-light hover:bg-red-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-bakery-primary flex items-center justify-center mr-3">
                                <i class="fas fa-shopping-basket text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">Order Now</h3>
                                <p class="text-xs text-gray-500">Browse our delicious products</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                        </a>
                        
                        <!-- View Orders -->
                        <a href="cus-orders.php" class="dashboard-card flex items-center p-4 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center mr-3">
                                <i class="fas fa-clipboard-list text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">View Orders</h3>
                                <p class="text-xs text-gray-500">Track your orders</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                        </a>
                        
                        <!-- Redeem Points -->
                        <a href="cart.php" class="dashboard-card flex items-center p-4 rounded-lg bg-green-50 hover:bg-green-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center mr-3">
                                <i class="fas fa-gift text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">Redeem Points</h3>
                                <p class="text-xs text-gray-500">Use your <?php echo number_format($userLoyaltyPoints); ?> loyalty points</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
     <!-- Recent Orders Section -->
<div class="bg-white rounded-xl shadow-md overflow-hidden mb-8 mx-32">
    <div class="p-6 border-b border-gray-100">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-shopping-bag mr-2 text-bakery-primary"></i>
                Recent Orders
            </h2>
            <a href="cus-orders.php" class="text-sm text-bakery-primary hover:text-bakery-secondary flex items-center transition-colors">
                View All
                <i class="fas fa-chevron-right ml-1 text-xs"></i>
            </a>
        </div>
        <p class="text-sm text-gray-500">Orders placed in the last 5 days</p>
    </div>
    
    <div class="overflow-x-auto px-4">
        <?php if (empty($recentOrders)): ?>
            <div class="p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center animate-pulse">
                    <i class="fas fa-shopping-cart text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">No Recent Orders</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">You haven't placed any orders in the last 5 days.</p>
                <a href="shop.php" class="inline-flex items-center bg-bakery-primary text-white font-medium py-2 px-6 rounded-lg hover:bg-bakery-secondary transition-colors">
                    <i class="fas fa-cart-plus mr-2"></i>
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <i class="fas fa-hashtag mr-1 text-bakery-primary"></i>
                                Order ID
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <i class="far fa-calendar-alt mr-1 text-bakery-primary"></i>
                                Date
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <i class="fas fa-boxes mr-1 text-bakery-primary"></i>
                                Items
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <i class="fas fa-rupee-sign mr-1 text-bakery-primary"></i>
                                Total
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle mr-1 text-bakery-primary"></i>
                                Status
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <i class="fas fa-tasks mr-1 text-bakery-primary"></i>
                                Action
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    // Define status colors and icons
                    $statusInfo = [
                        'pending' => [
                            'color' => 'yellow',
                            'icon' => 'fa-clock',
                            'description' => 'Awaiting confirmation'
                        ],
                        'accepted' => [
                            'color' => 'blue',
                            'icon' => 'fa-thumbs-up',
                            'description' => 'Order confirmed'
                        ],
                        'processing' => [
                            'color' => 'purple',
                            'icon' => 'fa-cogs',
                            'description' => 'Being prepared'
                        ],
                        'ready_to_delivered' => [
                            'color' => 'indigo',
                            'icon' => 'fa-box-check',
                            'description' => 'Ready for delivery'
                        ],
                        'on_the_way' => [
                            'color' => 'teal',
                            'icon' => 'fa-truck',
                            'description' => 'Out for delivery'
                        ],
                        'delivered' => [
                            'color' => 'green',
                            'icon' => 'fa-check-circle',
                            'description' => 'Successfully delivered'
                        ],
                        'completed' => [
                            'color' => 'green',
                            'icon' => 'fa-check-double',
                            'description' => 'Order completed'
                        ],
                        'cancelled' => [
                            'color' => 'red',
                            'icon' => 'fa-times-circle',
                            'description' => 'Order cancelled'
                        ]
                    ];
                    
                    foreach ($recentOrders as $order): 
                        $status = strtolower($order['status']);
                        $statusData = $statusInfo[$status] ?? [
                            'color' => 'gray',
                            'icon' => 'fa-question-circle',
                            'description' => 'Unknown status'
                        ];
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">#<?php echo $order['order_id']; ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center">
                                    <i class="far fa-clock mr-1 text-gray-400"></i>
                                    <?php echo date('M d, g:i A', strtotime($order['order_date'])); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center">
                                    <span class="bg-gray-100 text-gray-700 text-xs font-medium px-2 py-0.5 rounded-full mr-1">
                                        <?php echo $order['items']; ?>
                                    </span>
                                    item<?php echo $order['items'] > 1 ? 's' : ''; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <div class="flex items-center">
                                    <i class="fas fa-rupee-sign mr-1 text-gray-500 text-xs"></i>
                                    <?php echo number_format($order['total'], 2); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="status-badge bg-<?php echo $statusData['color']; ?>-100 text-<?php echo $statusData['color']; ?>-800 ring-1 ring-<?php echo $statusData['color']; ?>-200">
                                    <i class="fas <?php echo $statusData['icon']; ?> mr-1"></i>
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                                <p class="text-xs text-gray-500 mt-1 ml-1"><?php echo $statusData['description']; ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="cus-orders.php?id=<?php echo $order['order_id']; ?>" class="text-bakery-primary hover:text-bakery-secondary hover:underline font-medium inline-flex items-center">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="p-4 text-center text-sm text-gray-500 border-t border-gray-100">
                <i class="fas fa-info-circle mr-1 text-bakery-primary"></i>
                Showing latest orders. Check "View All" for your complete order history.
            </div>
        <?php endif; ?>
    </div>
</div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Example of dynamically setting the order progress based on the latest order
            // This is just a demo - you'd need to get the actual status from your orders
            
            // Animation for the recommendation boxes
            const cards = document.querySelectorAll('.dashboard-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('transition-opacity', 'duration-500', 'opacity-100');
                }, index * 100);
            });
            
            // You could add more interactive elements here like:
            // - Status updates in real-time
            // - Charts showing order history
            // - Notifications for new offers
        });
        // Add this inside your existing DOMContentLoaded event listener
const checkOrderStatus = () => {
    // In a real implementation, you would make an AJAX call to check order status
    // For demonstration, we'll simulate with a function
    
    const checkStatusChange = () => {
        fetch('check_order_status.php')  // You'd need to create this endpoint
            .then(response => response.json())
            .then(data => {
                if (data.status === 'delivered' || data.status === 'completed') {
                    // Order completed notification
                    showOrderCompletedMessage(data.order_id);
                } else if (data.status_changed) {
                    // Refresh the page to update the order journey
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error checking order status:', error));
    };
    
    // Check every 30 seconds
    setInterval(checkStatusChange, 30000);
};

const showOrderCompletedMessage = (orderId) => {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-white p-4 rounded-lg shadow-lg border-l-4 border-green-500 z-50 animate-slide-in';
    notification.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0 pt-0.5">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-gray-900">Order #${orderId} Completed</h3>
                <p class="mt-1 text-sm text-gray-500">Your order has been successfully delivered!</p>
                <div class="mt-2">
                    <button type="button" class="text-sm font-medium text-green-600 hover:text-green-500" onclick="window.location.reload()">
                        Refresh Dashboard
                    </button>
                </div>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    // Add to document
    document.body.appendChild(notification);
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        notification.classList.add('animate-slide-out');
        setTimeout(() => notification.remove(), 500);
    }, 10000);
};

// Add these CSS animations to your style section
document.head.insertAdjacentHTML('beforeend', `
<style>
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .animate-slide-in {
        animation: slideIn 0.5s forwards;
    }
    
    .animate-slide-out {
        animation: slideOut 0.5s forwards;
    }
</style>

`);

// Start checking for order status changes
checkOrderStatus();
    </script>
</body>

</html>