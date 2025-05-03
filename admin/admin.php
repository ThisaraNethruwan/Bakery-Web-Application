<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin") {
    // Redirect to login page if not logged in or not an admin
    header("Location: index.php");
    exit;
}

// Include database connection
include_once('db_connect.php');

// Get recent user activity
$activity_query = "SELECT u.name, u.user_type, u.created_at 
                  FROM user_accounts u 
                  WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  ORDER BY u.created_at DESC LIMIT 5";
$activity_result = $conn->query($activity_query);

// Include sidebar which contains HTML start tags
include_once('slidebar.php');
?>

<!-- Main Content -->
<div class="main-content flex-1">
    <?php include_once('header.php'); ?>

    <!-- Charts and Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <!-- Recent Activity -->
        <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold">Recent Account Activity</h3>
                <a href="account_management.php" class="text-red-600 text-sm font-medium hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($activity_result && $activity_result->num_rows > 0): ?>
                            <?php while ($row = $activity_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                                <span class="text-sm font-medium text-gray-600">
                                                    <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                                </span>
                                            </div>
                                            <span class="font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $row['user_type'] == 'admin' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['user_type'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-sm text-gray-500 text-center">No recent activity</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    

<?php
// Close the database connection
closeConnection($conn);
?>