<?php
require_once('components/header.php');
require_once('../admin/db_connect.php');

// Get recent orders
$orders_query = "SELECT o.*, c.name as customer_name 
                FROM orders o 
                JOIN customers c ON o.customer_id = c.id 
                ORDER BY o.order_date DESC LIMIT 5";
$orders_result = $conn->query($orders_query);

// Get product count
$products_query = "SELECT COUNT(*) as total FROM products";
$products_result = $conn->query($products_query);
$products_count = $products_result->fetch_assoc()['total'];

// Get pending messages count
$messages_query = "SELECT COUNT(*) as total FROM messages WHERE status = 'unread'";
$messages_result = $conn->query($messages_query);
$messages_count = $messages_result->fetch_assoc()['total'];

// Get active offers count
$offers_query = "SELECT COUNT(*) as total FROM offers WHERE end_date >= CURDATE()";
$offers_result = $conn->query($offers_query);
$offers_count = $offers_result->fetch_assoc()['total'];

require_once('components/sidebar.php');
?>

<!-- Main Content -->
<div class="flex-1 p-8">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Products Stats -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-bread-slice text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Total Products</h3>
                    <p class="text-2xl font-semibold"><?php echo $products_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Orders Stats -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-shopping-cart text-green-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Today's Orders</h3>
                    <p class="text-2xl font-semibold"><?php echo rand(5, 20); ?></p>
                </div>
            </div>
        </div>

        <!-- Messages Stats -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-envelope text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Unread Messages</h3>
                    <p class="text-2xl font-semibold"><?php echo $messages_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Active Offers -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-gift text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Active Offers</h3>
                    <p class="text-2xl font-semibold"><?php echo $offers_count; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="mt-8">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Recent Orders</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">#<?php echo $order['id']; ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $order['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                                  ($order['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                   'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">$<?php echo number_format($order['total'], 2); ?></td>
                                    <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td class="px-6 py-4">
                                        <a href="orders.php?id=<?php echo $order['id']; ?>" 
                                           class="text-red-600 hover:text-red-800">View Details</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No recent orders</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
        <a href="products.php?action=add" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all">
            <div class="flex flex-col items-center">
                <div class="p-3 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-plus text-blue-600"></i>
                </div>
                <h3 class="text-gray-700 font-medium">Add Product</h3>
            </div>
        </a>

        <a href="offers.php?action=add" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all">
            <div class="flex flex-col items-center">
                <div class="p-3 bg-green-100 rounded-full mb-4">
                    <i class="fas fa-gift text-green-600"></i>
                </div>
                <h3 class="text-gray-700 font-medium">Create Offer</h3>
            </div>
        </a>

        <a href="messages.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all">
            <div class="flex flex-col items-center">
                <div class="p-3 bg-yellow-100 rounded-full mb-4">
                    <i class="fas fa-envelope text-yellow-600"></i>
                </div>
                <h3 class="text-gray-700 font-medium">View Messages</h3>
            </div>
        </a>

        <a href="blogs.php?action=add" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all">
            <div class="flex flex-col items-center">
                <div class="p-3 bg-purple-100 rounded-full mb-4">
                    <i class="fas fa-blog text-purple-600"></i>
                </div>
                <h3 class="text-gray-700 font-medium">Add Blog Post</h3>
            </div>
        </a>
    </div>
</div>

<?php
$conn->close();
?>
