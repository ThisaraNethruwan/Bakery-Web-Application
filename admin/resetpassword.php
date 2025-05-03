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
$email = $new_password = $confirm_password = "";
$errors = [];
$success_message = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty($_POST["email"])) {
        $errors[] = "Email is required";
    } else {
        $email = trim($_POST["email"]);
        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } else {
            // Check if email exists in the database
            $check_email = "SELECT id FROM user_accounts WHERE email = ?";
            $stmt = $conn->prepare($check_email);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                $errors[] = "Email does not exist";
            }
            $stmt->close();
        }
    }

    // Validate new password
    if (empty($_POST["new_password"])) {
        $errors[] = "New password is required";
    } else {
        $new_password = $_POST["new_password"];
        // Check if password is strong enough
        if (strlen($new_password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
    }

    // Validate confirm password
    if (empty($_POST["confirm_password"])) {
        $errors[] = "Confirm password is required";
    } else {
        $confirm_password = $_POST["confirm_password"];
        // Check if passwords match
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
    }

    // If no errors, reset the password
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        $update_query = "UPDATE user_accounts SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            $success_message = "Password reset successfully!";
            // Reset form fields
            $email = $new_password = $confirm_password = "";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        $stmt->close();
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
            <h2 class="text-2xl font-semibold text-gray-800">Reset User Password</h2>
            <p class="text-gray-600 mt-1">Reset the password for a user account</p>
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

        <!-- Reset Password Form -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                            placeholder="Enter user's email address">
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" id="new_password" name="new_password"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                            placeholder="Enter new password">
                        <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                            placeholder="Confirm new password">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition-colors">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Close the database connection
closeConnection($conn);
?>