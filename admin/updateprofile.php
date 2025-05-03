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

// Initialize variables
$name = $email = $contact_no = "";
$profile_picture = "";
$errors = [];
$success_message = "";

// Fetch current user data
$user_id = $_SESSION["user_id"];
$query = "SELECT name, email, phone, profile_image FROM user_accounts WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
    $name = $user["name"];
    $email = $user["email"];
    $contact_no = $user["phone"];
    $profile_picture = $user["profile_image"];
}

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
            // Check if email already exists (excluding the current user)
            $check_email = "SELECT id FROM user_accounts WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($check_email);
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = "Email already exists";
            }
            $stmt->close();
        }
    }

    if (empty($_POST["contact_no"])) {
        $errors[] = "Contact number is required";
    } else {
        $contact_no = trim($_POST["contact_no"]);
        // Basic validation for contact number
        if (!preg_match("/^[0-9+\-\s()]{8,20}$/", $contact_no)) {
            $errors[] = "Invalid contact number format";
        }
    }

    // Handle profile picture upload
    $profile_picture_path = $profile_picture; // Keep existing picture if no new file is uploaded
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
                $profile_picture_path = $profile_picture; // Revert to existing picture
            }
        }
    }

    // If no errors, update profile
    if (empty($errors)) {
        $update_query = "UPDATE user_accounts SET name = ?, email = ?, phone = ?, profile_image = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssi", $name, $email, $contact_no, $profile_picture_path, $user_id);

        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
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
            <h2 class="text-2xl font-semibold text-gray-800">Update Profile</h2>
            <p class="text-gray-600 mt-1">Update your profile information</p>
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

        <!-- Back to Dashboard Button -->
        <div class="mb-6">
            <a href="admin.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                Back to Dashboard
            </a>
        </div>

        <!-- Update Profile Form -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                            placeholder="Enter full name">
                    </div>

                    <!-- Email Address -->
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
                        <?php if (!empty($profile_picture)): ?>
                            <div class="mt-2">
                                <p class="text-sm text-gray-600">Current Profile Picture:</p>
                                <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="w-20 h-20 rounded-full object-cover">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition-colors">
                        Update Profile
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