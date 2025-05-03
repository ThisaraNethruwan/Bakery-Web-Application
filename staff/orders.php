<?php
require_once('components/header.php');
require_once('../admin/db_connect.php');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        header("Location: orders.php?success=1");
        exit;
    }
}

require_once('components/sidebar.php');
?>

<!-- Main Content -->
<div class="flex-1 p-8">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Order Management</h2>
        </div>

        <!-- Filters -->
        <div class="p-6 border-b border-gray-200">
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search orders..." 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="date" value="<?php echo $date_filter; ?>" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    // Build query based on filters
                    $sql = "SELECT o.*, c.name as customer_name, c.address, c.latitude, c.longitude 
                            FROM orders o 
                            JOIN customers c ON o.customer_id = c.id";
                    
                    $where_conditions = [];
                    if ($search) {
                        $where_conditions[] = "(o.id LIKE '%$search%' OR c.name LIKE '%$search%')";
                    }
                    if ($status_filter) {
                        $where_conditions[] = "o.status = '$status_filter'";
                    }
                    if ($date_filter) {
                        $where_conditions[] = "DATE(o.order_date) = '$date_filter'";
                    }
                    
                    if (!empty($where_conditions)) {
                        $sql .= " WHERE " . implode(" AND ", $where_conditions);
                    }
                    
                    $sql .= " ORDER BY o.order_date DESC";
                    
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0):
                        while ($order = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td class="px-6 py-4">#<?php echo $order['id']; ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td class="px-6 py-4">
                                <form action="" method="POST" class="inline">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" 
                                            class="border border-gray-300 rounded px-2 py-1">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td class="px-6 py-4">$<?php echo number_format($order['total'], 2); ?></td>
                            <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            <td class="px-6 py-4">
                                <button onclick="showOrderDetails(<?php echo htmlspecialchars(json_encode($order)); ?>)" 
                                        class="text-blue-600 hover:text-blue-800 mr-2">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="showOnMap(<?php echo $order['latitude']; ?>, <?php echo $order['longitude']; ?>)" 
                                        class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No orders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Map Modal -->
    <div id="mapModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-4/5 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Delivery Location</h3>
                <button onclick="closeMapModal()" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="map" class="h-96 w-full rounded-lg"></div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-4/5 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Order Details</h3>
                <button onclick="closeOrderDetailsModal()" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="orderDetails" class="space-y-4">
                <!-- Order details will be populated here -->
            </div>
        </div>
    </div>
</div>

<script>
let map;
let marker;

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: { lat: 0, lng: 0 }
    });
    marker = new google.maps.Marker({
        map: map
    });
}

function showOnMap(lat, lng) {
    document.getElementById('mapModal').classList.remove('hidden');
    const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
    map.setCenter(position);
    marker.setPosition(position);
}

function closeMapModal() {
    document.getElementById('mapModal').classList.add('hidden');
}

function showOrderDetails(order) {
    const modal = document.getElementById('orderDetailsModal');
    const detailsDiv = document.getElementById('orderDetails');
    
    detailsDiv.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="font-semibold">Order ID:</p>
                <p>#${order.id}</p>
            </div>
            <div>
                <p class="font-semibold">Customer:</p>
                <p>${order.customer_name}</p>
            </div>
            <div>
                <p class="font-semibold">Delivery Address:</p>
                <p>${order.address}</p>
            </div>
            <div>
                <p class="font-semibold">Total Amount:</p>
                <p>$${parseFloat(order.total).toFixed(2)}</p>
            </div>
            <div>
                <p class="font-semibold">Order Date:</p>
                <p>${new Date(order.order_date).toLocaleDateString()}</p>
            </div>
            <div>
                <p class="font-semibold">Status:</p>
                <p>${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</p>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').classList.add('hidden');
}

// Initialize map when the page loads
window.addEventListener('load', initMap);
</script>

<?php
$conn->close();
?>
