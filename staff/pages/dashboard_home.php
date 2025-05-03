<?php
// Get statistics
$stats = [
    'products' => $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'orders' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'],
    'customers' => $conn->query("SELECT COUNT(*) as count FROM user_accounts WHERE user_type='customer'")->fetch_assoc()['count'],
    'unread_messages' => $conn->query("SELECT COUNT(*) as count FROM messages WHERE status='unread'")->fetch_assoc()['count']
];

// Get recent orders
$recent_orders = $conn->query("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    JOIN user_accounts u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Statistics Cards -->
    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-[#c41c1c]">
        <h3 class="text-lg font-semibold text-gray-700">Total Products</h3>
        <p class="text-3xl font-bold text-[#c41c1c]"><?php echo $stats['products']; ?></p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-[#c41c1c]">
        <h3 class="text-lg font-semibold text-gray-700">Total Orders</h3>
        <p class="text-3xl font-bold text-[#c41c1c]"><?php echo $stats['orders']; ?></p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-[#c41c1c]">
        <h3 class="text-lg font-semibold text-gray-700">Total Customers</h3>
        <p class="text-3xl font-bold text-[#c41c1c]"><?php echo $stats['customers']; ?></p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-[#c41c1c]">
        <h3 class="text-lg font-semibold text-gray-700">Unread Messages</h3>
        <p class="text-3xl font-bold text-[#c41c1c]"><?php echo $stats['unread_messages']; ?></p>
    </div>
</div>

<!-- Recent Orders -->
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4 text-[#c41c1c]">Recent Orders</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-[#c41c1c]/5">
                    <th class="px-4 py-2 text-left text-[#c41c1c]">Order ID</th>
                    <th class="px-4 py-2 text-left text-[#c41c1c]">Customer</th>
                    <th class="px-4 py-2 text-left text-[#c41c1c]">Status</th>
                    <th class="px-4 py-2 text-left text-[#c41c1c]">Amount</th>
                    <th class="px-4 py-2 text-left text-[#c41c1c]">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                <tr class="border-b hover:bg-[#c41c1c]/5">
                    <td class="px-4 py-2">#<?php echo $order['id']; ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-sm 
                            <?php echo match($order['status']) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            }; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                    <td class="px-4 py-2">Rs.<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td class="px-4 py-2"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
