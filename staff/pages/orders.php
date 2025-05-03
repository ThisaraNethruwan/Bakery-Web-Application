<?php
// Include database connection
// Assuming $conn is already established

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
    }
}

// Get search and sort parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Build query
$query = "SELECT o.*, u.name as customer_name 
          FROM orders o 
          JOIN user_accounts u ON o.user_id = u.id 
          WHERE 1=1";

$params = [];
$types = "";

if ($search) {
    $search = "%$search%";
    $query .= " AND (u.name LIKE ? OR o.delivery_location LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

if ($status_filter) {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($date_from) {
    $query .= " AND DATE(o.created_at) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if ($date_to) {
    $query .= " AND DATE(o.created_at) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$query .= " ORDER BY o.$sort $order";

// Execute search
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Define bakery location
$bakery_lat = 6.936492497391741;
$bakery_lng = 79.93596438025665;
?>

<!-- Store main layout -->
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6 text-primary">Order Management</h2>

    <!-- Search and Filter Form -->
    <form method="GET" action="index.php?page=orders" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Search by customer or address" 
               class="border rounded px-4 py-2 focus:border-primary focus:ring-primary">
        
        <select name="status" class="border rounded px-4 py-2 focus:border-primary focus:ring-primary">
            <option value="">All Statuses</option>
            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="accepted" <?php echo $status_filter === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
            <option value="ready_to_delivered" <?php echo $status_filter === 'ready_to_delivered' ? 'selected' : ''; ?>>Ready to Deliver</option>
            <option value="on_the_way" <?php echo $status_filter === 'on_the_way' ? 'selected' : ''; ?>>On the Way</option>
            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
        </select>
        
        <div class="flex gap-2">
            <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                   class="border rounded px-4 py-2 w-1/2 focus:border-primary focus:ring-primary" placeholder="From Date">
            <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                   class="border rounded px-4 py-2 w-1/2 focus:border-primary focus:ring-primary" placeholder="To Date">
        </div>

        <!-- Add hidden input for page parameter -->
        <input type="hidden" name="page" value="orders">
        
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
            Apply Filters
        </button>
    </form>

    <!-- Dashboard Layout -->
    <div class="grid grid-cols-1 gap-6 mb-6">
        <!-- Orders Table -->
        <div class="overflow-x-auto bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4 text-primary">Orders</h3>
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-primary/5">
                        <th class="px-4 py-2 text-left text-primary">
                            <a href="<?php echo '?page=orders&sort=id&order=' . ($sort === 'id' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search) . '&status=' . urlencode($status_filter) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to); ?>" 
                               class="flex items-center">
                                Order ID
                                <?php if ($sort === 'id'): ?>
                                    <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-4 py-2 text-left text-primary">
                            <a href="<?php echo '?page=orders&sort=customer_name&order=' . ($sort === 'customer_name' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search) . '&status=' . urlencode($status_filter) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to); ?>" 
                               class="flex items-center">
                                Customer
                                <?php if ($sort === 'customer_name'): ?>
                                    <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-4 py-2 text-left text-primary">
                            <a href="<?php echo '?page=orders&sort=total_amount&order=' . ($sort === 'total_amount' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search) . '&status=' . urlencode($status_filter) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to); ?>" 
                               class="flex items-center">
                                Total
                                <?php if ($sort === 'total_amount'): ?>
                                    <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-4 py-2 text-left text-primary">
                            <a href="<?php echo '?page=orders&sort=status&order=' . ($sort === 'status' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search) . '&status=' . urlencode($status_filter) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to); ?>" 
                               class="flex items-center">
                                Status
                                <?php if ($sort === 'status'): ?>
                                    <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-4 py-2 text-left text-primary">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    while ($order = $result->fetch_assoc()): 
                    ?>
                    <tr class="border-b hover:bg-primary/5">
                        <td class="px-4 py-2">#<?php echo $order['id']; ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td class="px-4 py-2">Rs.<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td class="px-4 py-2">
                            <select onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)"
                                    class="border rounded px-2 py-1 text-sm focus:border-primary focus:ring-primary
                                        <?php echo match($order['status']) {
                                            'accepted' => 'bg-blue-100 text-blue-800',
                                            'processing' => 'bg-yellow-100 text-yellow-800',
                                            'ready_to_delivered' => 'bg-indigo-100 text-indigo-800',
                                            'on_the_way' => 'bg-purple-100 text-purple-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'delivered' => 'bg-emerald-100 text-emerald-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        }; ?>">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>       
                                <option value="accepted" <?php echo $order['status'] === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="ready_to_delivered" <?php echo $order['status'] === 'ready_to_delivered' ? 'selected' : ''; ?>>Ready to Deliver</option>
                                <option value="on_the_way" <?php echo $order['status'] === 'on_the_way' ? 'selected' : ''; ?>>On the Way</option>
                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                        </td>
                        <td class="px-4 py-2">
                            <button onclick="viewOrderDetails(<?php echo htmlspecialchars(json_encode([
                                'id' => $order['id'],
                                'customer_name' => $order['customer_name'],
                                'total_amount' => $order['total_amount'],
                                'status' => $order['status'],
                                'delivery_address' => $order['delivery_location'],
                                'delivery_coordinates' => $order['delivery_coordinates'],
                                'payment_method' => $order['payment_method'],
                                'products' => $order['products'],
                                'created_at' => $order['created_at'],
                                'updated_at' => $order['updated_at']
                            ])); ?>)"
                                    class="bg-primary text-white px-2 py-1 rounded hover:bg-primary/90 mr-2 text-sm">
                                View
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if ($result->num_rows === 0): ?>
                <p class="text-gray-500 text-center py-4">No orders found with the current filters.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-primary">Order Details</h3>
                <button onclick="closeOrderModal()" class="text-gray-500 hover:text-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold mb-2 text-primary">Delivery Information</h4>
                    <p id="orderAddress" class="text-gray-600 mb-4"></p>
                    
                    <!-- Google Maps will be loaded here -->
                    <div id="map" class="w-full h-64 rounded border"></div>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-2 text-primary">Order Summary</h4>
                    <div id="orderDetails" class="space-y-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps JavaScript -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCCt2Jp-gp9MviNqYugzw4YCgIr0cdecuM&libraries=places"></script>
<script>
let map;
let marker;

function updateOrderStatus(orderId, status) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" value="${orderId}">
        <input type="hidden" name="status" value="${status}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function viewOrderDetails(order) {
    document.getElementById('orderAddress').textContent = order.delivery_address;
    document.getElementById('orderModal').classList.remove('hidden');
    
    // Parse coordinates from delivery_coordinates field
    if (order.delivery_coordinates) {
        const [lat, lng] = order.delivery_coordinates.split(',').map(coord => parseFloat(coord.trim()));
        
        // Initialize map
        const mapOptions = {
            center: { lat: lat, lng: lng },
            zoom: 15
        };
        
        map = new google.maps.Map(document.getElementById('map'), mapOptions);
        
        // Add marker
        marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map,
            title: order.delivery_address
        });
    }
    
    // Display order details
    const products = JSON.parse(order.products);
    let detailsHtml = `
        <p><strong>Order ID:</strong> #${order.id}</p>
        <p><strong>Customer:</strong> ${order.customer_name}</p>
        <p><strong>Total Amount:</strong> $${parseFloat(order.total_amount).toFixed(2)}</p>
        <p><strong>Payment Method:</strong> ${order.payment_method}</p>
        <p><strong>Status:</strong> ${order.status}</p>
        <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString()}</p>
        <h5 class="font-semibold mt-4 mb-2">Products:</h5>
    `;
    
    products.forEach(product => {
        detailsHtml += `
            <div class="border-b py-2">
                <p><strong>${product.name}</strong></p>
                <p>Quantity: ${product.quantity} × $${parseFloat(product.price).toFixed(2)}</p>
            </div>
        `;
    });
    
    document.getElementById('orderDetails').innerHTML = detailsHtml;
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.add('hidden');
}
</script>