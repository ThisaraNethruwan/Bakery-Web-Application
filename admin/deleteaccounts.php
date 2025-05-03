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

// Initialize variables
$errors = [];
$success_message = "";

// Handle account deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_user"])) {
    $user_id = $_POST["user_id"];

    // Validate user ID
    if (empty($user_id)) {
        $errors[] = "User ID is required";
    } else {
        // Check if the user exists
        $check_user = "SELECT id FROM user_accounts WHERE id = ?";
        $stmt = $conn->prepare($check_user);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $errors[] = "User does not exist";
        } else {
            // Delete the user
            $delete_query = "DELETE FROM user_accounts WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                $success_message = "User account deleted successfully!";
            } else {
                $errors[] = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all user accounts from the database
$query = "SELECT id, name, email, user_type, created_at FROM user_accounts";
$result = $conn->query($query);
$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Include sidebar which contains HTML start tags
include_once('slidebar.php');
?>

<!-- Main Content -->
<div class="main-content flex-1">

<?php include_once('header.php'); ?>

    <div class="py-6 px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Delete User Accounts</h2>
            <p class="text-gray-600 mt-1">Manage and delete user accounts</p>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?php echo $success_message; ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-medium">Please fix the following errors:</p>
                <ul class="mt-1 ml-5 list-disc">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>


        <!-- User Accounts Table -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Role</th>
                        <th class="px-4 py-2 text-left">Created At</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-center text-gray-500">No user accounts found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($user['id']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($user['name']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($user['user_type']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td class="px-4 py-2">
                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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