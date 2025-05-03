<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit;
}

// Include database connection
include_once('db_connect.php');

// Fetch current user data
$user_id = $_SESSION["user_id"];
$query = "SELECT name, email, phone, profile_image, user_type, created_at FROM user_accounts WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Include sidebar which contains HTML start tags
include_once('slidebar.php');
?>

<!-- Main Content -->
<div class="main-content flex-1">

<?php include_once('header.php'); ?>

    <div class="py-6 px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Profile Details</h2>
            <p class="text-gray-600 mt-1">View your profile information</p>
        </div>


        <!-- Profile Details Card -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Profile Picture -->
                <div class="flex justify-center md:justify-start">
                    <div class="w-48 h-48 rounded-full overflow-hidden border-4 border-gray-200">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-500">
                                <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Profile Information -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($user['name']); ?></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                        <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($user['phone']); ?></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">User Role</label>
                        <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($user['user_type']); ?></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Created At</label>
                        <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($user['created_at']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Button -->
            <div class="mt-8 flex justify-end">
                <a href="updateprofile.php" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition-colors">
                    Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Close the database connection
closeConnection($conn);
?>