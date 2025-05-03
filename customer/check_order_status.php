<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo json_encode(['error' => 'Not logged in']);
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
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

$userId = $_SESSION["user_id"];

// Get the latest order status
$stmt = $conn->prepare("
    SELECT id as order_id, status, 
           (SELECT status FROM orders_status_history 
            WHERE order_id = o.id 
            ORDER BY created_at DESC LIMIT 1,1) as previous_status
    FROM orders o
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
    $statusChanged = $order['status'] !== $order['previous_status'] && $order['previous_status'] !== null;
    
    echo json_encode([
        'order_id' => $order['order_id'],
        'status' => $order['status'],
        'status_changed' => $statusChanged
    ]);
} else {
    echo json_encode(['status' => 'none', 'status_changed' => false]);
}

$conn->close();
?>