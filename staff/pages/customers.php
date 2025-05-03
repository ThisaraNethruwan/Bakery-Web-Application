<?php
// Handle customer operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reset_password') {
        $customer_id = $_POST['customer_id'];
        $new_password = bin2hex(random_bytes(8)); // Generate random password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE user_accounts SET password = ? WHERE id = ? AND user_type = 'customer'");
        $stmt->bind_param("si", $hashed_password, $customer_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Password reset successful. New password: " . $new_password;
        } else {
            $_SESSION['error_message'] = "Failed to reset password.";
        }
    } elseif ($_POST['action'] === 'reset_username') {
        $customer_id = $_POST['customer_id'];
        $new_username = $_POST['new_username'];
        
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM user_accounts WHERE email = ? AND id != ? AND user_type = 'customer'");
        $stmt->bind_param("si", $new_username, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['error_message'] = "Email already exists.";
        } else {
            $stmt = $conn->prepare("UPDATE user_accounts SET email = ? WHERE id = ? AND user_type = 'customer'");
            $stmt->bind_param("si", $new_username, $customer_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Email updated successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to update email.";
            }
        }
    }
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Build query
$query = "SELECT u.*, 
          COUNT(DISTINCT o.id) as total_orders,
          SUM(o.total_amount) as total_spent
          FROM user_accounts u
          LEFT JOIN orders o ON u.id = o.user_id
          WHERE u.user_type = 'customer'";

if ($search) {
    $search = "%$search%";
    $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
}

$query .= " GROUP BY u.id ORDER BY $sort $order";

// Execute search
$stmt = $conn->prepare($query);
if ($search) {
    $stmt->bind_param("ss", $search, $search);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6 text-primary">Customer Management</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php 
            echo htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php 
            echo htmlspecialchars($_SESSION['error_message']);
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Search Form -->
    <form method="GET" action="index.php" class="flex gap-4 mb-6">
        <input type="hidden" name="page" value="customers">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Search by name or email" 
               class="border rounded px-4 py-2 w-64 focus:border-primary focus:ring-primary">
               
        <select name="sort" class="border rounded px-4 py-2 focus:border-primary focus:ring-primary">
            <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Join Date</option>
            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name</option>
            <option value="total_orders" <?php echo $sort === 'total_orders' ? 'selected' : ''; ?>>Total Orders</option>
            <option value="total_spent" <?php echo $sort === 'total_spent' ? 'selected' : ''; ?>>Total Spent</option>
        </select>
        
        <select name="order" class="border rounded px-4 py-2 focus:border-primary focus:ring-primary">
            <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
            <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
        </select>
        
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
            Apply
        </button>
    </form>

    <!-- Customers Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-primary/5">
                    <th class="px-4 py-2">
                        <a href="<?php echo '?page=customers&sort=id&order=' . ($sort === 'id' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search); ?>" 
                           class="flex items-center text-primary">
                            ID
                            <?php if ($sort === 'id'): ?>
                                <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-4 py-2">
                        <a href="<?php echo '?page=customers&sort=name&order=' . ($sort === 'name' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search); ?>" 
                           class="flex items-center text-primary">
                            Name
                            <?php if ($sort === 'name'): ?>
                                <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-4 py-2">
                        <a href="<?php echo '?page=customers&sort=email&order=' . ($sort === 'email' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search); ?>" 
                           class="flex items-center text-primary">
                            Email
                            <?php if ($sort === 'email'): ?>
                                <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-4 py-2">
                        <a href="<?php echo '?page=customers&sort=created_at&order=' . ($sort === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search); ?>" 
                           class="flex items-center text-primary">
                            Join Date
                            <?php if ($sort === 'created_at'): ?>
                                <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-4 py-2">
                        <a href="<?php echo '?page=customers&sort=total_orders&order=' . ($sort === 'total_orders' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search); ?>" 
                           class="flex items-center text-primary">
                            Total Orders
                            <?php if ($sort === 'total_orders'): ?>
                                <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-4 py-2">
                        <a href="<?php echo '?page=customers&sort=total_spent&order=' . ($sort === 'total_spent' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search); ?>" 
                           class="flex items-center text-primary">
                            Total Spent
                            <?php if ($sort === 'total_spent'): ?>
                                <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($customer = $result->fetch_assoc()): ?>
                <tr class="hover:bg-primary/5">
                    <td class="px-4 py-2">#<?php echo $customer['id']; ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($customer['name']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($customer['email']); ?></td>
                    <td class="px-4 py-2"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                    <td class="px-4 py-2"><?php echo $customer['total_orders']; ?></td>
                    <td class="px-4 py-2">Rs.<?php echo number_format($customer['total_spent'] ?? 0, 2); ?></td>
                    <td class="px-4 py-2">
                        <button onclick="showCustomerModal(<?php echo htmlspecialchars(json_encode($customer)); ?>)"
                                class="bg-primary text-white px-2 py-1 rounded hover:bg-primary/90">
                            Manage
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        No customers found with the current filters.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Customer Management Modal -->
<div id="customerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Manage Customer</h3>
                <button onclick="closeCustomerModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Reset Username Form -->
                <form id="usernameForm" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="reset_username">
                    <input type="hidden" name="customer_id" id="usernameCustomerId">
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Current Email</label>
                        <input type="text" id="currentUsername" disabled
                               class="border rounded w-full px-3 py-2 bg-gray-100">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">New Email</label>
                        <input type="text" name="new_username" required
                               class="border rounded w-full px-3 py-2 focus:border-primary focus:ring-primary">
                    </div>
                    
                    <button type="submit"
                            class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90 w-full">
                        Update Email
                    </button>
                </form>
                
                <hr>
                
                <!-- Reset Password Form -->
                <form id="passwordForm" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="customer_id" id="passwordCustomerId">
                    
                    <p class="text-sm text-gray-600">
                        Reset password will generate a new random password. Make sure to communicate it to the customer securely.
                    </p>
                    
                    <button type="submit"
                            class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 w-full">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showCustomerModal(customer) {
    document.getElementById('usernameCustomerId').value = customer.id;
    document.getElementById('passwordCustomerId').value = customer.id;
    document.getElementById('currentUsername').value = customer.email;
    document.getElementById('customerModal').classList.remove('hidden');
}

function closeCustomerModal() {
    document.getElementById('customerModal').classList.add('hidden');
}
</script>
