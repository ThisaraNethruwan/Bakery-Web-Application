<?php

include "nav.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login and signin.php");
    exit();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nishan_bakery');

// Database Connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Handle Order Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderId = mysqli_real_escape_string($conn, $_POST['order_id']);
    
    // Check if order exists, belongs to the user, and is in pending status
    $checkStmt = mysqli_prepare($conn, "SELECT created_at, status FROM orders WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($checkStmt, "ii", $orderId, $userId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) > 0) {
        $orderData = mysqli_fetch_assoc($checkResult);
        $createdTime = strtotime($orderData['created_at']);
        $currentTime = time();
        $timeDiff = $currentTime - $createdTime;
        $orderStatus = strtolower($orderData['status']);
        
        // Check if order is pending and within 40 seconds
        if ($orderStatus === 'pending' && $timeDiff <= 40) {
            // Update order status to cancelled
            $updateStmt = mysqli_prepare($conn, "UPDATE orders SET status = 'cancelled' WHERE id = ?");
            mysqli_stmt_bind_param($updateStmt, "i", $orderId);
            mysqli_stmt_execute($updateStmt);
            
            // Set success message
            $successMsg = "Order #$orderId has been cancelled successfully.";
        } else if ($orderStatus !== 'pending') {
            // Set error message for wrong status
            $errorMsg = "Order #$orderId cannot be cancelled as it has already been " . $orderData['status'] . ".";
        } 
        
    }
}

// Fetch User's Orders (newest first)
$stmt = mysqli_prepare($conn, "
    SELECT id, total_amount, payment_method, delivery_location, 
           status, created_at, products 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Close database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders - Nishan Bakery</title>
    
    <!-- External Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sweet-red': '#e53e3e',
                        'sweet-red-dark': '#c53030',
                        'sweet-pink': '#fdf2f2',
                    }
                }
            }
        }
    </script>
    
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .animate-pulse-custom {
            animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<br><br><br>
<body class="bg-gray-50">
 
    <div class="container mx-auto px-4 py-8 flex-grow">
        <!-- Page Title and Info -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-red-600 flex items-center">
                    <i class="fas fa-clipboard-list mr-3"></i> Your Orders
                </h1>
                <p class="text-gray-600 mt-2">
                    <a class="font-bold">Track and manage your orders. </a>
                    <a class="text-red-500 font-bold">You can cancel pending orders before accepted.</a>
                </p>
            </div>
            <a href="shop.php" class="mt-4 md:mt-0 flex items-center bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-all duration-300 transform hover:-translate-y-1">
                <i class="fas fa-shopping-bag mr-2"></i> Continue Shopping
            </a>
        </div>
        
        <!-- Success Message -->
        <?php if (isset($successMsg)): ?>
            <div id="successAlert" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-md">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                    <p><?php echo $successMsg; ?></p>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.getElementById('successAlert');
                    alert.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            </script>
        <?php endif; ?>
        
        <!-- Error Message -->
        <?php if (isset($errorMsg)): ?>
            <div id="errorAlert" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-md">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                    <p><?php echo $errorMsg; ?></p>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.getElementById('errorAlert');
                    alert.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            </script>
        <?php endif; ?>
        
        <!-- Orders List -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="space-y-6">
                <?php while ($order = mysqli_fetch_assoc($result)): 
                    $products = json_decode($order['products'], true);
                    $orderTime = strtotime($order['created_at']);
                    $currentTime = time();
                    $timeDiff = $currentTime - $orderTime;
                    
                    // Order is only cancellable if status is pending AND within 40 seconds
                    $status = strtolower($order['status']);
                    $nonCancellableStatuses = ['accepted', 'processing', 'ready to delivered', 'on the way', 'delivered', 'cancelled'];
                    $canCancel = ($status === 'pending') && ($timeDiff <= 40);
                    $remainingSeconds = ($canCancel) ? (40 - $timeDiff) : 0;
                ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transition-transform duration-300 hover:transform hover:scale-[1.01]">
                        <!-- Order Header -->
                        <div class="bg-gray-50 p-4 border-b flex flex-col md:flex-row justify-between items-start md:items-center">
                            <div>
                                <div class="flex items-center">
                                    <h3 class="font-bold text-lg md:text-xl text-gray-800">
                                        Order # <?php echo $order['id']; ?>
                                    </h3>
                                    
                                    <!-- Order Status Badge -->
                                    <?php
                                    $statusClasses = [
                                        'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'accepted' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'ready to delivered' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        'on the way' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        'completed' => 'bg-green-100 text-green-800 border-green-200',
                                        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                                        'delivered' => 'bg-indigo-100 text-indigo-800 border-indigo-200'
                                    ];
                                    $statusIcons = [
                                        'pending' => 'fa-clock',
                                        'accepted' => 'fa-check',
                                        'processing' => 'fa-spinner fa-spin',
                                        'ready to delivered' => 'fa-box',
                                        'on the way' => 'fa-truck',
                                        'completed' => 'fa-check-circle',
                                        'cancelled' => 'fa-times-circle',
                                        'delivered' => 'fa-truck-loading'
                                    ];
                                    $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                    $statusIcon = $statusIcons[$status] ?? 'fa-question-circle';
                                    ?>
                                    
                                    <span class="ml-3 px-3 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?> border">
                                        <i class="fas <?php echo $statusIcon; ?> mr-1"></i>
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                                
                                <p class="text-gray-500 text-sm mt-1">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            
                            <!-- Order Actions -->
                            <div class="mt-4 md:mt-0 flex items-center">
                            <?php if ($canCancel): ?>
                                <div class="relative inline-block">
                                    <div class="absolute inset-0 bg-red-300 rounded-full animate-pulse-custom"></div>
                                    <form method="POST" class="relative" onsubmit="return confirmCancel(<?php echo $order['id']; ?>)">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" name="cancel_order" id="cancel-btn-<?php echo $order['id']; ?>" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full transition-colors duration-300 flex items-center">
                                            <i class="fas fa-ban mr-2"></i> 
                                            Cancel Order 
                                        </button>
                                    </form>
                                </div>
                              
                             
                            <?php elseif (in_array($status, $nonCancellableStatuses)): ?>
                                <span class="text-gray-500 flex items-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <?php 
                                        if ($status === 'cancelled') {
                                            echo 'Order has been cancelled';
                                        } elseif ($status === 'delivered') {
                                            echo 'Order has been delivered';
                                        } else {
                                            echo 'Cannot cancel - ' . ucfirst($status);
                                        }
                                    ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-500 flex items-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Cancel period expired
                                </span>
                            <?php endif; ?>
                                
                                <!-- Order Details Toggle Button -->
                                <button onclick="toggleOrderDetails('order-<?php echo $order['id']; ?>')" class="ml-4 text-blue-600 hover:text-blue-800 flex items-center">
                                    <i class="fas fa-eye mr-1"></i> Details
                                </button>
                            </div>
                        </div>
                        
                        <!-- Order Details (Hidden by default) -->
                        <div id="order-<?php echo $order['id']; ?>" class="hidden p-4 border-b">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-bold text-gray-700 mb-2 flex items-center">
                                        <i class="fas fa-shopping-basket text-red-500 mr-2"></i> Order Items
                                    </h4>
                                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                        <?php foreach ($products as $product): ?>
                                            <div class="flex items-center border-b border-gray-100 pb-2">
                                                <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-md overflow-hidden">
                                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                         class="w-full h-full object-cover">
                                                </div>
                                                <div class="ml-3 flex-grow">
                                                    <h5 class="font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h5>
                                                    <div class="flex justify-between text-sm text-gray-600">
                                                        <span>Qty: <?php echo $product['quantity']; ?></span>
                                                        <span>Rs. <?php echo number_format($product['price'], 2); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="mb-4">
                                        <h4 class="font-bold text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Delivery Address
                                        </h4>
                                        <p class="text-gray-600">
                                            <?php echo htmlspecialchars($order['delivery_location']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h4 class="font-bold text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-credit-card text-red-500 mr-2"></i> Payment Method
                                        </h4>
                                        <p class="text-gray-600">
                                            <?php 
                                                $paymentIcon = $order['payment_method'] === 'cash' ? 'fa-money-bill-wave' : 'fa-credit-card';
                                                echo '<i class="fas ' . $paymentIcon . ' mr-1"></i> ';
                                                echo ucfirst($order['payment_method']) . ' Payment';
                                            ?>
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="font-bold text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-calculator text-red-500 mr-2"></i> Order Summary
                                        </h4>
                                        <div class="bg-gray-50 p-3 rounded">
                                            <div class="flex justify-between text-gray-600 mb-1">
                                                <span>Total Items:</span>
                                                <span><?php echo count($products); ?></span>
                                            </div>
                                            <div class="flex justify-between font-bold text-gray-800 text-lg border-t border-gray-200 pt-1 mt-1">
                                                <span>Total:</span>
                                                <span class="text-red-600">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Footer -->
                        <div class="bg-gray-50 p-3 text-sm text-gray-500 flex justify-between">
                            <span>
                                <i class="fas fa-tags mr-1"></i> 
                                Total: <span class="font-bold text-red-600">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                            </span>
                            <span>
                                <i class="fas fa-box mr-1"></i>
                                Items: <?php echo count($products); ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- No Orders View -->
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="w-24 h-24 mx-auto mb-4 flex items-center justify-center bg-red-100 text-red-500 rounded-full">
                    <i class="fas fa-receipt text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No Orders Yet</h3>
                <p class="text-gray-600 mb-6">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                <a href="shop.php" class="inline-block bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600 transition-all duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-shopping-bag mr-2"></i> Browse Products
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Toggle Order Details
        function toggleOrderDetails(id) {
            const details = document.getElementById(id);
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                details.classList.add('block');
            } else {
                details.classList.add('hidden');
                details.classList.remove('block');
            }
        }
        
        // Confirm Cancel Order
        function confirmCancel(orderId) {
            return Swal.fire({
                title: 'Cancel Order?',
                text: "This action cannot be undone after confirmation.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e53e3e',
                cancelButtonColor: '#718096',
                confirmButtonText: 'Yes, Cancel Order',
                cancelButtonText: 'No, Keep Order'
            }).then((result) => {
                return result.isConfirmed;
            });
        }
    </script>
</body>
</html>