<?php
function logStaffActivity($conn, $userId, $activityType, $details) {
    $stmt = $conn->prepare("INSERT INTO staff_activity_log (user_id, activity_type, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $activityType, $details);
    $stmt->execute();
    $stmt->close();
}

function getStaffStats($conn) {
    $stats = array();
    
    // Get today's orders count
    $query = "SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()";
    $result = $conn->query($query);
    $stats['today_orders'] = $result->fetch_assoc()['count'];
    
    // Get pending orders count
    $query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
    $result = $conn->query($query);
    $stats['pending_orders'] = $result->fetch_assoc()['count'];
    
    // Get total products
    $query = "SELECT COUNT(*) as count FROM products";
    $result = $conn->query($query);
    $stats['total_products'] = $result->fetch_assoc()['count'];
    
    // Get unread messages
    $query = "SELECT COUNT(*) as count FROM messages WHERE status = 'unread'";
    $result = $conn->query($query);
    $stats['unread_messages'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

function getRecentOrders($conn, $limit = 5) {
    $query = "SELECT o.*, u.name as customer_name 
              FROM orders o 
              JOIN user_accounts u ON o.user_id = u.id 
              ORDER BY o.order_date DESC 
              LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = array();
    
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $stmt->close();
    return $orders;
}

function updateOrderStatus($conn, $orderId, $status) {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function addProduct($conn, $name, $description, $price, $category, $image, $stock) {
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, image, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image, $stock);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function updateProduct($conn, $id, $name, $description, $price, $category, $image, $stock) {
    $query = "UPDATE products SET name = ?, description = ?, price = ?, category = ?";
    $params = array($name, $description, $price, $category);
    $types = "ssds";
    
    if (!empty($image)) {
        $query .= ", image = ?";
        $params[] = $image;
        $types .= "s";
    }
    
    $query .= ", stock_quantity = ? WHERE id = ?";
    $params[] = $stock;
    $params[] = $id;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function createOffer($conn, $title, $description, $discountType, $discountValue, $code, $startDate, $endDate, $minPurchase) {
    $stmt = $conn->prepare("INSERT INTO offers (title, description, discount_type, discount_value, code, start_date, end_date, min_purchase) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdsssd", $title, $description, $discountType, $discountValue, $code, $startDate, $endDate, $minPurchase);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function replyToMessage($conn, $messageId, $staffId, $replyText) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert reply
        $stmt = $conn->prepare("INSERT INTO message_replies (message_id, staff_id, reply_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $messageId, $staffId, $replyText);
        $stmt->execute();
        $stmt->close();
        
        // Update message status
        $stmt = $conn->prepare("UPDATE messages SET status = 'replied' WHERE id = ?");
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return false;
    }
}

function createBlogPost($conn, $title, $content, $category, $image, $authorId) {
    $stmt = $conn->prepare("INSERT INTO blog_posts (title, content, category, image, author_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $title, $content, $category, $image, $authorId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function validateStaffAccess() {
    if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "staff") {
        header("Location: ../login and signin.php");
        exit;
    }
}

function calculate_loyalty_points($cart_total) {
    $points_per_100 = 10; // 10 points for every Rs. 100
    $points = floor($cart_total / 100) * $points_per_100;
    return $points;
}

?>
