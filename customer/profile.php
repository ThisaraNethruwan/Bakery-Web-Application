<?php
// Start session

include "nav.php";


// Check if user is logged in and is a customer
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "customer") {
    // Redirect to login page if not logged in or not a customer
    header("Location: index.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Nishan_Bakery";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user information
$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"];
$userEmail = $_SESSION["user_email"];

// Initialize variables for profile update
$successMessage = "";
$errorMessage = "";

// Handle profile image upload
if (isset($_POST['upload_image'])) {
    // Check if file was uploaded without errors
    if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["profile_image"]["name"];
        $filetype = $_FILES["profile_image"]["type"];
        $filesize = $_FILES["profile_image"]["size"];

        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $errorMessage = "Error: Please select a valid file format.";
        }

        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $errorMessage = "Error: File size is larger than the allowed limit.";
        }

        // Verify MIME type of the file
        if (in_array($filetype, $allowed)) {
            // Check if file exists before uploading it
            $newFilename = uniqid() . "." . $ext;
            $uploadDir = "uploads/";
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $targetPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetPath)) {
                // Update database with new profile image path
                $updateStmt = $conn->prepare("UPDATE user_accounts SET profile_image = ? WHERE id = ?");
                $updateStmt->bind_param("si", $targetPath, $userId);
                
                if ($updateStmt->execute()) {
                    $successMessage = "Your profile image has been updated successfully.";
                    
                    // Update session with new image path if needed
                    $_SESSION["profile_image"] = $targetPath;
                } else {
                    $errorMessage = "Error: There was an error updating your profile image.";
                }
                
                $updateStmt->close();
            } else {
                $errorMessage = "Error: There was an error uploading your file.";
            }
        } else {
            $errorMessage = "Error: There was a problem with your upload. Please try again.";
        }
    } else {
        $errorMessage = "Error: " . $_FILES["profile_image"]["error"];
    }
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    // Update user profile
    $updateStmt = $conn->prepare("UPDATE user_accounts SET name = ?, email = ? WHERE id = ?");
    $updateStmt->bind_param("ssi", $name, $email, $userId);
    
    if ($updateStmt->execute()) {
        $successMessage = "Your profile has been updated successfully.";
        
        // Update session variables
        $_SESSION["user_name"] = $name;
        $_SESSION["user_email"] = $email;
    } else {
        $errorMessage = "Error: There was an error updating your profile.";
    }
    
    $updateStmt->close();
}
  
// Fetch user details
$stmt = $conn->prepare("SELECT * FROM user_accounts WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // Handle case where user data can't be found
    $user = [
        'profile_image' => 'uploads/default-profile.png',
        'name' => $userName,
        'email' => $userEmail,
        'phone' => '',
        'address' => '',
        'points' => 0
    ];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nishan Bakery - Profile</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Import -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    },
                    colors: {
                        'bakery': {
                            50: '#FFF5F5',
                            100: '#FFE0E0',
                            200: '#FFBCBC',
                            300: '#FF9B9B',
                            400: '#FF7070',
                            500: '#FF4D4D',
                            600: '#FF1A1A',
                            700: '#E60000',
                            800: '#CC0000',
                            900: '#990000',
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FFFAFA;
        }
    </style>
</head>
<br>
<body class="bg-gradient-to-b from-white to-bakery-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header with animation -->
        <div class="flex items-center justify-between mb-10">
       
            <div class="hidden md:flex items-center space-x-2">
                
            </div>
        </div>

        <!-- Notifications -->
        <?php if (!empty($successMessage)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 shadow-md transform hover:scale-[1.01] transition-all duration-300">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <p class="font-medium"><?php echo $successMessage; ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 shadow-md transform hover:scale-[1.01] transition-all duration-300">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="font-medium"><?php echo $errorMessage; ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Profile Image Card -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-lg border border-bakery-100 transform hover:scale-[1.01] transition-all duration-300 hover:shadow-xl">
                <div class="bg-gradient-to-r from-bakery-400 to-bakery-600 h-24 relative"></div>
                <div class="flex justify-center -mt-12 mb-4 relative">
                    <div class="w-28 h-28 bg-bakery-100 rounded-full flex items-center justify-center overflow-hidden border-4 border-white shadow-lg ring-2 ring-bakery-400 ring-opacity-50">
                        <img src="<?php echo !empty($user['profile_image']) ? $user['profile_image'] : 'uploads/default-profile.png'; ?>" 
                             alt="Profile Image" 
                             class="w-full h-full object-cover">
                    </div>
                </div>
                
                <div class="text-center px-6 pb-6">
                    <h2 class="text-xl font-bold mb-1 text-gray-800"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="text-gray-500 text-sm mb-6"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                   

                    <form action="" method="post" enctype="multipart/form-data" id="profileImageForm">
                        <div class="mb-4">
                            <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-2">
                                Update Profile Picture
                            </label>
                            <div class="relative border-2 border-dashed border-bakery-200 p-4 rounded-lg hover:border-bakery-400 transition-colors group cursor-pointer">
                                <input type="file" name="profile_image" id="profile_image" 
                                    class="opacity-0 absolute inset-0 w-full cursor-pointer z-10" 
                                    onchange="updateFileName(this)">
                                <div class="text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-bakery-400 group-hover:text-bakery-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm text-gray-500 mt-2 group-hover:text-gray-700 transition-colors">Click to upload or drag and drop</p>
                                    <p id="fileName" class="text-xs text-bakery-600 mt-2 hidden"></p>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="upload_image" class="w-full bg-bakery-600 hover:bg-bakery-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors duration-300 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-bakery-500 focus:ring-opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12" />
                            </svg>
                            Upload Image
                        </button>
                    </form>
                </div>
            </div>

            <!-- Profile Details Card -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-lg border border-bakery-100 md:col-span-2 transform hover:scale-[1.01] transition-all duration-300 hover:shadow-xl p-6">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-bakery-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Profile Information
                </h2>
                
                <form action="" method="post" id="profileForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="group">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2 group-hover:text-bakery-700 transition-colors">Full Name</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-bakery-400 group-hover:text-bakery-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>"
                                   class="w-full pl-10 p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-bakery-500 focus:border-bakery-500 transition-colors group-hover:border-bakery-200">
                            </div>
                        </div>
                        <div class="group">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2 group-hover:text-bakery-700 transition-colors">Email Address</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-bakery-400 group-hover:text-bakery-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                   class="w-full pl-10 p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-bakery-500 focus:border-bakery-500 transition-colors group-hover:border-bakery-200">
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="group">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2 group-hover:text-bakery-700 transition-colors">Phone Number</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-bakery-400 group-hover:text-bakery-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </span>
                                <input type="tel" name="phone" id="phone" value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>"
                                   class="w-full pl-10 p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-bakery-500 focus:border-bakery-500 transition-colors group-hover:border-bakery-200">
                            </div>
                        </div>
        
                    <div class="flex justify-end mt-8">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2.5 px-4 rounded-lg transition-colors duration-300 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 mr-4">
                            Reset
                        </button>
                        <button type="submit" name="update_profile" class="bg-bakery-600 hover:bg-bakery-700 text-white font-medium py-2.5 px-6 rounded-lg transition-colors duration-300 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-bakery-500 focus:ring-opacity-50 shadow-md hover:shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>