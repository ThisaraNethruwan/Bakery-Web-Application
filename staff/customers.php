<?php
require_once('components/header.php');
require_once('../admin/db_connect.php');

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $customer_id = $_POST['customer_id'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    
    $sql = "UPDATE customers SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_password, $customer_id);
    
    if ($stmt->execute()) {
        header("Location: customers.php?success=1");
        exit;
    }
}

require_once('components/sidebar.php');
?>

<!-- Main Content -->
<div class="flex-1 p-8">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Customer Management</h2>
        </div>

        <!-- Search -->
        <div class="p-6 border-b border-gray-200">
            <form action="" method="GET" class="flex space-x-4">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search customers..." 
                       class="flex-1 border border-gray-300 rounded-lg px-4 py-2">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Search
                </button>
            </form>
        </div>

        <!-- Customers Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    // Build query based on search
                    $sql = "SELECT c.*, 
                            COUNT(o.id) as total_orders,
                            MAX(o.order_date) as last_order_date
                            FROM customers c
                            LEFT JOIN orders o ON c.id = o.customer_id";
                    
                    if ($search) {
                        $sql .= " WHERE c.name LIKE '%$search%' OR c.email LIKE '%$search%' OR c.phone LIKE '%$search%'";
                    }
                    
                    $sql .= " GROUP BY c.id ORDER BY total_orders DESC";
                    
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0):
                        while ($customer = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-gray-600">
                                            <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                                        </span>
                                    </div>
                                    <?php echo htmlspecialchars($customer['name']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td class="px-6 py-4"><?php echo $customer['total_orders']; ?></td>
                            <td class="px-6 py-4">
                                <?php 
                                echo $customer['last_order_date'] 
                                    ? date('M d, Y', strtotime($customer['last_order_date'])) 
                                    : 'No orders';
                                ?>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="showCustomerDetails(<?php echo htmlspecialchars(json_encode($customer)); ?>)"
                                        class="text-blue-600 hover:text-blue-800 mr-2">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="showResetPasswordModal(<?php echo $customer['id']; ?>)"
                                        class="text-yellow-600 hover:text-yellow-800">
                                    <i class="fas fa-key"></i>
                                </button>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No customers found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div id="customerDetailsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Customer Details</h3>
            <button onclick="closeCustomerDetailsModal()" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="customerDetails" class="space-y-4">
            <!-- Customer details will be populated here -->
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Reset Password</h3>
            <button onclick="closeResetPasswordModal()" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="resetPasswordForm" action="" method="POST" class="space-y-4">
            <input type="hidden" name="customer_id" id="resetPasswordCustomerId">
            <div>
                <label class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="new_password" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                       minlength="8">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="confirm_password" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                       minlength="8">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeResetPasswordModal()"
                        class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <button type="submit" name="reset_password"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showCustomerDetails(customer) {
    const modal = document.getElementById('customerDetailsModal');
    const detailsDiv = document.getElementById('customerDetails');
    
    detailsDiv.innerHTML = `
        <div class="space-y-3">
            <div>
                <p class="text-sm font-medium text-gray-500">Name</p>
                <p class="mt-1">${customer.name}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Email</p>
                <p class="mt-1">${customer.email}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Phone</p>
                <p class="mt-1">${customer.phone}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Address</p>
                <p class="mt-1">${customer.address}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Orders</p>
                <p class="mt-1">${customer.total_orders}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Last Order</p>
                <p class="mt-1">${customer.last_order_date ? new Date(customer.last_order_date).toLocaleDateString() : 'No orders'}</p>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeCustomerDetailsModal() {
    document.getElementById('customerDetailsModal').classList.add('hidden');
}

function showResetPasswordModal(customerId) {
    document.getElementById('resetPasswordCustomerId').value = customerId;
    document.getElementById('resetPasswordModal').classList.remove('hidden');
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
    document.getElementById('resetPasswordForm').reset();
}

// Password confirmation validation
document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
    const password = this.querySelector('input[name="new_password"]').value;
    const confirm = this.querySelector('input[name="confirm_password"]').value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>

<?php
$conn->close();
?>
