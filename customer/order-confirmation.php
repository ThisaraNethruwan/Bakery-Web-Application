<?php
session_start();

if (!isset($_SESSION['order_success']) || !$_SESSION['order_success']) {
    header("Location: cart.php");
    exit;
}

$orderId = $_SESSION['order_id'] ?? 'N/A';

// Clear success message to avoid resubmission issues
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .success { color: green; font-size: 24px; font-weight: bold; }
    </style>
</head>
<body>
    <h1 class="success">🎉 Order Successfully Placed!</h1>
    <p>Your order ID: <strong>#<?php echo htmlspecialchars($orderId); ?></strong></p>
    <a href="index.php">Back to Shop</a>
</body>
</html>
