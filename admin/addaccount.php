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
$name = $email = $password = $confirm_password = $user_type = $contact_no = "";
$profile_picture = "";
$errors = [];
$success_message = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    if (empty($_POST["name"])) {
        $errors[] = "Name is required";
    } else {
        $name = trim($_POST["name"]);
    }

    if (empty($_POST["email"])) {
        $errors[] = "Email is required";
    } else {
        $email = trim($_POST["email"]);
        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } else {
            // Check if email already exists
            $check_email = "SELECT id FROM user_accounts WHERE email = ?";
            $stmt = $conn->prepare($check_email);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = "Email already exists";
            }
            $stmt->close();
        }
    }

    if (empty($_POST["password"])) {
        $errors[] = "Password is required";
    } else {
        $password = $_POST["password"];
        // Check if password is strong enough
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
    }

    if (empty($_POST["confirm_password"])) {
        $errors[] = "Confirm password is required";
    } else {
        $confirm_password = $_POST["confirm_password"];
        // Check if passwords match
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
    }

    if (empty($_POST["user_type"])) {
        $errors[] = "User role is required";
    } else {
        $user_type = $_POST["user_type"];
        // Validate user type
        if (!in_array($user_type, ["admin", "staff"])) {
            $errors[] = "Invalid user role";
        }
    }
    
    // Validate contact number
    if (empty($_POST["contact_no"])) {
        $errors[] = "Contact number is required";
    } else {
        $contact_no = trim($_POST["contact_no"]);
        // Basic validation for contact number (can be customized based on your requirements)
        if (!preg_match("/^[0-9+\-\s()]{8,20}$/", $contact_no)) {
            $errors[] = "Invalid contact number format";
        }
    }
    
    // Handle profile picture upload
    $profile_picture_path = "";
    if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
        $allowed_types = ["image/jpeg", "image/jpg", "image/png"];
        $max_size = 2 * 1024 * 1024; // 2MB

        // Check file type
        if (!in_array($_FILES["profile_picture"]["type"], $allowed_types)) {
            $errors[] = "Only JPG, JPEG, and PNG files are allowed";
        }
        
        // Check file size
        if ($_FILES["profile_picture"]["size"] > $max_size) {
            $errors[] = "File size must be less than 2MB";
        }
        
        if (empty($errors)) {
            // Create uploads directory if it doesn't exist
            $upload_dir = "uploads/profile_pictures/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
            $file_name = uniqid() . "." . $file_extension;
            $profile_picture_path = $upload_dir . $file_name;
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture_path)) {
                $errors[] = "Failed to upload profile picture";
                $profile_picture_path = "";
            }
        }
    }

    // If no errors, insert new user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $created_by = $_SESSION["user_id"];
        
        // Updated INSERT query to match your table structure
        $insert_query = "INSERT INTO user_accounts (name, email, password, user_type, phone, profile_image, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssss", $name, $email, $hashed_password, $user_type, $contact_no, $profile_picture_path);
        
        if ($stmt->execute()) {
            $success_message = "New account created successfully!";
            // Reset form fields
            $name = $email = $password = $confirm_password = $user_type = $contact_no = "";
        } else {
            $errors[] = "Error: " . $stmt->error;
            
            // If user creation failed, delete uploaded file if exists
            if (!empty($profile_picture_path) && file_exists($profile_picture_path)) {
                unlink($profile_picture_path);
            }
        }
        $stmt->close();
    } else {
        // If there are validation errors, delete uploaded file if exists
        if (!empty($profile_picture_path) && file_exists($profile_picture_path)) {
            unlink($profile_picture_path);
        }
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
            <h2 class="text-2xl font-semibold text-gray-800">Add New Account</h2>
            <p class="text-gray-600 mt-1">Create a new staff or admin account</p>
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

        <div class="bg-white rounded-lg shadow-md p-8">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                            placeholder="Enter full name">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                            placeholder="Enter email address">
                    </div>

                    <!-- Contact Number -->
                    <div>
                        <label for="contact_no" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input type="text" id="contact_no" name="contact_no" value="<?php echo htmlspecialchars($contact_no); ?>"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                            placeholder="Enter contact number">
                    </div>

                    <!-- Profile Picture -->
                    <div>
                        <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-1">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture"
                            class="w-full text-gray-700 py-2"
                            accept=".jpg, .jpeg, .png">
                        <p class="text-xs text-gray-500 mt-1">Upload JPG, JPEG, or PNG (max 2MB)</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" name="password"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                            placeholder="Enter password">
                        <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                            placeholder="Confirm password">
                    </div>

                    <!-- User Role -->
                    <div>
                        <label for="user_type" class="block text-sm font-medium text-gray-700 mb-1">User Role</label>
                        <select id="user_type" name="user_type"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200">
                            <option value="" <?php echo empty($user_type) ? 'selected' : ''; ?>>Select a role</option>
                            <option value="staff" <?php echo $user_type === 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo $user_type === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition-colors">
                        Create Account
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Tips Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">Tips for Account Creation</h3>
            <ul class="space-y-2 text-blue-700">
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Administrator accounts have full access to all system features</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Staff accounts can manage products and orders but cannot access system settings</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Use strong, unique passwords for each account</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>All account activity is logged for security purposes</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Upload profile pictures that clearly show the person's face for identification</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php
// Close the database connection
closeConnection($conn);
?>