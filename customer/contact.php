<?php
// Fetch user details from database after login
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

// Get user information from session
$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"];
$userEmail = $_SESSION["user_email"];

// Fetch messages for the current user
$stmt = $conn->prepare("SELECT * FROM messages WHERE customer_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Handle new message submission
$successMessage = "";
$errorMessage = "";

if (isset($_POST['send_message'])) {
    $newMessage = trim($_POST['message']);
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : "New Message"; // Allow subject if provided
    
    if (!empty($newMessage)) {
        // Insert new message into database
        $insertStmt = $conn->prepare("INSERT INTO messages (customer_id, subject, message, status, created_at) VALUES (?, ?, ?, 'unread', NOW())");
        $insertStmt->bind_param("iss", $userId, $subject, $newMessage);
        
        if ($insertStmt->execute()) {
            $successMessage = "Message sent successfully!";
           
        } else {
            $errorMessage = "Error sending message: " . $conn->error;
        }
        
        $insertStmt->close();
    } else {
        $errorMessage = "Please enter a message.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nishan Bakery - Customer Support</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
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
        .chat-bubble-received {
            border-radius: 18px 18px 18px 0;
        }
        .chat-bubble-sent {
            border-radius: 18px 18px 0 18px;
        }
        .message-time {
            font-size: 0.65rem;
        }
        .chat-body::-webkit-scrollbar {
            width: 5px;
        }
        .chat-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .chat-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        .chat-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-white to-bakery-50 min-h-screen">
    <div class="container mx-auto px-2 py-1 max-w-full"> <!-- Increased max-width for better laptop display -->

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

        <!-- Chat Interface -->
        <div class="bg-white rounded-2xl overflow-hidden shadow-lg border border-bakery-100 transform hover:scale-[1.005] transition-all duration-300 hover:shadow-xl">
            <!-- Chat Header -->
            <div class="bg-bakery-500 text-white p-4 flex items-center justify-between shadow-md">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-white text-bakery-500 flex items-center justify-center text-xl font-bold">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-bold">Nishan Bakery</h3>
                        <p class="text-xs text-bakery-100">Customer Support</p>
                    </div>
                </div>
                <div class="text-sm bg-white text-bakery-500 px-3 py-1 rounded-full">
                    <span class="font-medium"><?php echo htmlspecialchars($userName); ?></span>
                </div>
            </div>

            <!-- Chat Messages Area -->
            <div class="chat-body h-[500px] overflow-y-auto p-4 bg-gray-100" id="chatMessages"> <!-- Increased height for better visibility -->
                <?php if (empty($messages)): ?>
                    <!-- Welcome message when no messages exist -->
                    <div class="text-center my-4">
                        <div class="bg-bakery-100 text-bakery-800 rounded-lg p-4 inline-block">
                            <i class="fas fa-cookie-bite text-xl mb-2"></i>
                            <p class="font-bold">Welcome to Nishan Bakery Support!</p>
                            <p class="text-sm">Send us a message and we'll respond as soon as possible.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php 
                    $currentDate = null;
                    foreach ($messages as $message): 
                        // Add date separator
                        $messageDate = date('Y-m-d', strtotime($message['created_at']));
                        if ($currentDate != $messageDate): 
                            $currentDate = $messageDate;
                    ?>
                        <div class="text-center my-3">
                            <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">
                                <?php echo date('F j, Y', strtotime($message['created_at'])); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                        
                        <!-- Customer Message -->
                        <div class="max-w-md md:max-w-lg lg:max-w-xl ml-auto mb-4">
                            <div class="flex items-start justify-end">
                                <div class="chat-bubble-sent bg-bakery-500 text-white p-3">
                                    <?php if (!empty($message['subject']) && $message['subject'] != "New Message"): ?>
                                        <div class="font-bold text-sm text-bakery-100 mb-1"><?php echo htmlspecialchars($message['subject']); ?></div>
                                    <?php endif; ?>
                                    <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                    <div class="message-time text-right text-bakery-100 mt-1">
                                        <?php echo date('g:i A', strtotime($message['created_at'])); ?>
                                        <?php if ($message['status'] == 'read'): ?>
                                            <span class="ml-1"><i class="fas fa-check-double"></i></span>
                                        <?php elseif ($message['status'] == 'unread'): ?>
                                            <span class="ml-1"><i class="fas fa-check"></i></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="h-8 w-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center text-sm ml-2">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Admin Reply (if exists) -->
                        <?php if (!empty($message['reply'])): ?>
                        <div class="max-w-md md:max-w-lg lg:max-w-xl mb-4">
                            <div class="flex items-start">
                                <div class="h-8 w-8 rounded-full bg-bakery-500 text-white flex items-center justify-center text-sm mr-2">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div class="chat-bubble-received bg-white text-gray-700 shadow-sm p-3">
                                    <?php if (!empty($message['subject']) && $message['subject'] != "New Message"): ?>
                                        <div class="font-bold text-sm text-bakery-600 mb-1">Re: <?php echo htmlspecialchars($message['subject']); ?></div>
                                    <?php endif; ?>
                                    <p><?php echo nl2br(htmlspecialchars($message['reply'])); ?></p>
                                    <div class="message-time text-right text-gray-500 mt-1">
                                        <?php echo !empty($message['replied_at']) ? date('g:i A', strtotime($message['replied_at'])) : ''; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Input Area -->
            <form method="post" action="" class="p-4 bg-white border-t">
                <div class="flex flex-col">
                    <!-- Optional subject field -->
                    <div class="mb-3">
                        <input type="text" name="subject" class="w-full border rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-bakery-500" placeholder="Subject (optional)">
                    </div>
                    <div class="flex items-center">
                        <textarea name="message" id="messageInput" rows="2" class="flex-grow border rounded-lg py-2 px-4 mr-2 focus:outline-none focus:ring-2 focus:ring-bakery-500 resize-none" placeholder="Type a message..." required></textarea>
                        <button type="submit" name="send_message" class="bg-bakery-500 hover:bg-bakery-600 text-white rounded-full p-2 w-12 h-12 flex items-center justify-center transition-all">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto scroll to bottom of chat on page load
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
        
        // Add textarea auto-resize functionality
        const messageInput = document.getElementById('messageInput');
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            if (this.scrollHeight > 200) {
                this.style.height = '200px';
                this.style.overflowY = 'auto';
            } else {
                this.style.overflowY = 'hidden';
            }
        });
    </script>
</body>
</html>