<?php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nishan_bakery');

// Database Connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Input Sanitization Function
function sanitizeInput($input) {
    global $conn;
    $input = trim($input);
    $input = stripslashes($input);
    $input = mysqli_real_escape_string($conn, $input);
    $input = htmlspecialchars($input);
    return $input;
}

// Fetch Loyalty Points
function getLoyaltyPoints($userId) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT points FROM loyalty_points WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['points'] : 0;
}

// Cart Management Class
class CartManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
        $this->initializeCart();
    }
    
    private function initializeCart() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    public function addToCart($productId, $productName, $productPrice, $productImage) {
        // Validate product exists
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 0) {
            return false;
        }
        
        // Check if product already in cart
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity']++;
                return true;
            }
        }
        
        // Add new product to cart
        $_SESSION['cart'][] = [
            'id' => $productId,
            'name' => $productName,
            'price' => $productPrice,
            'image' => $productImage,
            'quantity' => 1
        ];
        
        return true;
    }
    
    public function updateQuantity($productId, $quantity) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity'] = max(1, $quantity);
                break;
            }
        }
    }
    
    public function removeFromCart($productId) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $productId) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                break;
            }
        }
    }
    
    public function calculateTotal() {
        $total = 0;
        $totalItems = 0;
        
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['price'] * $item['quantity'];
                $totalItems += $item['quantity'];
            }
        }
        
        return [
            'total' => $total,
            'total_items' => $totalItems
        ];
    }
    
    public function placeOrder($paymentMethod, $deliveryLocation, $customerCoordinates, $redeemedPoints = 0, $loyaltyDiscount = 0) {
        if (empty($deliveryLocation) || empty($paymentMethod) || empty($customerCoordinates)) {
            throw new Exception("Missing required order details");
        }
        
        if (empty($_SESSION['cart'])) {
            throw new Exception("Cart is empty");
        }
        
        $total = $this->calculateTotal()['total'] - $loyaltyDiscount;
        
        try {
            // Prepare order details
            $productsJson = json_encode($_SESSION['cart']);
            $userId = $_SESSION['user_id'] ?? 0;
            
            // Prepare SQL statement
            $stmt = mysqli_prepare($this->conn, 
                "INSERT INTO orders (
                    user_id, total_amount, payment_method, 
                    delivery_location, delivery_coordinates, status, products, loyalty_discount
                ) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)"
            );
            
            mysqli_stmt_bind_param($stmt, "idssssd", 
                $userId, 
                $total, 
                $paymentMethod, 
                $deliveryLocation, 
                $customerCoordinates,
                $productsJson,
                $loyaltyDiscount
            );
            
            mysqli_stmt_execute($stmt);
            $orderId = mysqli_insert_id($this->conn);
            
            // Deduct redeemed points
            if ($redeemedPoints > 0) {
                $this->deductLoyaltyPoints($userId, $redeemedPoints, $orderId);
            }
            
            // Calculate loyalty points (10 point for every Rs. 100 spent)
            $loyaltyPointsEarned = floor($total / 10);
            
            if ($loyaltyPointsEarned > 0) {
                // Update loyalty points
                $this->updateLoyaltyPoints($userId, $loyaltyPointsEarned, $orderId);
            }
            
            // Clear cart after successful order
            $_SESSION['cart'] = [];
            
            return [
                'order_id' => $orderId,
                'loyalty_points' => $loyaltyPointsEarned
            ];
            
        } catch (Exception $e) {
            error_log("Order placement error: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function deductLoyaltyPoints($userId, $pointsDeducted, $orderId) {
        // Deduct points from total
        $stmt = mysqli_prepare($this->conn, 
            "UPDATE loyalty_points 
             SET points = points - ? 
             WHERE user_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ii", $pointsDeducted, $userId);
        mysqli_stmt_execute($stmt);
        
        // Record the transaction
        $stmt = mysqli_prepare($this->conn, 
            "INSERT INTO loyalty_transactions (user_id, order_id, points, type) 
             VALUES (?, ?, ?, 'redeemed')"
        );
        mysqli_stmt_bind_param($stmt, "iii", $userId, $orderId, $pointsDeducted);
        mysqli_stmt_execute($stmt);
    }
    
    private function updateLoyaltyPoints($userId, $pointsEarned, $orderId) {
        // Update total loyalty points
        $stmt = mysqli_prepare($this->conn, 
            "INSERT INTO loyalty_points (user_id, points) 
             VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE points = points + ?"
        );
        mysqli_stmt_bind_param($stmt, "iii", $userId, $pointsEarned, $pointsEarned);
        mysqli_stmt_execute($stmt);
        
        // Record the transaction
        $stmt = mysqli_prepare($this->conn, 
            "INSERT INTO loyalty_transactions (user_id, order_id, points, type) 
             VALUES (?, ?, ?, 'earned')"
        );
        mysqli_stmt_bind_param($stmt, "iii", $userId, $orderId, $pointsEarned);
        mysqli_stmt_execute($stmt);
    }
}

// Initialize Cart Manager
$cartManager = new CartManager($conn);

// Handle AJAX Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    header('Content-Type: application/json');
    
    try {
        $orderResult = $cartManager->placeOrder(
            sanitizeInput($_POST['payment_method']), 
            sanitizeInput($_POST['delivery_location']), 
            sanitizeInput($_POST['customer_coordinates']),
            intval($_POST['redeemed_points']), 
            floatval($_POST['loyalty_discount'])
        );
        
        echo json_encode([
            'success' => true,
            'order_id' => $orderResult['order_id'],
            'loyalty_points' => $orderResult['loyalty_points'],
            'message' => 'Order placed successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
    exit;
}

// Handle Regular POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $cartManager->addToCart(
            sanitizeInput($_POST['product_id']), 
            sanitizeInput($_POST['product_name']), 
            sanitizeInput($_POST['product_price']), 
            sanitizeInput($_POST['product_image'])
        );
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['update_quantity'])) {
        $cartManager->updateQuantity(
            sanitizeInput($_POST['product_id']), 
            sanitizeInput($_POST['quantity'])
        );
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['remove_from_cart'])) {
        $cartManager->removeFromCart(
            sanitizeInput($_POST['product_id'])
        );
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Calculate Cart Totals
$cartTotals = $cartManager->calculateTotal();

// Fetch Loyalty Points
$userId = $_SESSION['user_id'] ?? 0;
$loyaltyPoints = getLoyaltyPoints($userId);

// Close database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Cart</title>
    
    <!-- External Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCCt2Jp-gp9MviNqYugzw4YCgIr0cdecuM&libraries=places"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sweet-red': '#e53e3e',
                        'sweet-red-dark': '#c53030',
                        'sweet-pink': '#fdf2f2',
                    }
                }
            }
        }
    </script>
</head>
<body>
    <div class="flex flex-col min-h-screen">
     
        <div class="container px-4 py-4 mx-auto flex-grow">
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Cart Items Section -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-3xl font-bold text-red-600">
                            Your Cart 
                            <span class="text-lg text-gray-500 ml-2">
                                (<?php echo $cartTotals['total_items']; ?> Items)
                            </span>
                        </h2>
                        <a href="shop.php" class="flex items-center px-4 py-2 bg-white text-red-600 font-bold rounded-full shadow hover:bg-red-50 transform hover:-translate-y-1 transition-all duration-300">
                            <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                        </a>
                    </div>
                    
                    <?php if (empty($_SESSION['cart'])): ?>
                        <div class="text-center py-72 bg-white rounded-lg shadow-lg">
                            <i class="fas fa-shopping-cart text-gray-300 text-5xl mb-4"></i>
                            <p class="text-xl text-gray-500">Your cart is empty</p>
                            <a href="shop.php" class="mt-4 inline-block px-6 py-2 bg-red-500 text-white rounded-full hover:bg-red-600 transform hover:-translate-y-1 transition-all duration-300">
                                Start Shopping
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($_SESSION['cart'] as $item): 
                            $item_total = $item['price'] * $item['quantity'];
                        ?>
                            <div class="bg-white rounded-lg shadow-lg p-4 flex items-center transform hover:-translate-y-1 transition-all duration-300">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="w-24 h-24 object-cover rounded-lg mr-4 shadow">
                                
                                <div class="flex-grow">
                                    <h3 class="font-bold text-lg"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-gray-600">Rs. <?php echo number_format($item['price'], 2); ?></p>
                                    
                                    <div class="mt-2 flex items-center">
                                        <div class="flex items-center border rounded-full overflow-hidden shadow-sm">
                                            <button onclick="updateQuantity(<?php echo $item['id']; ?>, -1)" class="px-3 py-1 bg-gray-200 hover:bg-red-200 transition-colors duration-300">
                                                <i class="fas fa-minus text-sm"></i>
                                            </button>
                                            <input type="number" 
                                                   id="quantity-<?php echo $item['id']; ?>"
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   class="w-12 text-center" 
                                                   readonly>
                                            <button onclick="updateQuantity(<?php echo $item['id']; ?>, 1)" class="px-3 py-1 bg-gray-200 hover:bg-red-200 transition-colors duration-300">
                                                <i class="fas fa-plus text-sm"></i>
                                            </button>
                                        </div>
                                        
                                        <button onclick="removeFromCart(<?php echo $item['id']; ?>)" 
                                                class="ml-4 text-red-500 hover:text-red-700 flex items-center transition-colors duration-300">
                                            <i class="fas fa-trash-alt mr-1"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="font-bold text-red-500 text-lg">
                                    Rs. <?php echo number_format($item_total, 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

<!-- Checkout Section -->
<div class="bg-white rounded-lg shadow-xl p-2 ">
    <h2 class="text-2xl font-bold text-red-600 mb-2 flex items-center">
        <i class="fas fa-credit-card mr-2"></i> Checkout
    </h2>
    
    <!-- Loyalty Points Display -->
    <div class="mb-2 bg-green-50 p-3 rounded-lg flex items-center">
        <i class="fas fa-star text-green-500 mr-2"></i>
        <div>
            <span class="block text-gray-700 text-md">Your Loyalty Points: 
            <span id="loyaltyPoints" class="text-green-600 font-bold"><?php echo $loyaltyPoints; ?></span>
        </div>
    </div>
    
    <form id="orderForm" class="space-y-1">
        <!-- Location Input -->
        <div>
            <label class="block mb-1.5 font-medium text-sm">
                <i class="fas fa-map-marker-alt mr-1.5 text-red-500"></i> Delivery Location
            </label>
            <div class="relative">
                <input type="text" 
                       id="customerLocation" 
                       name="delivery_location" 
                       placeholder="Enter your address" 
                       class="w-full border rounded-lg px-3 py-2.5 focus:ring focus:ring-red-200 focus:border-red-500 outline-none transition-all duration-300" 
                       required>
                <div class="absolute right-3 top-3 text-gray-400">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <!-- Map Container -->
        <div id="map" class="rounded-lg overflow-hidden shadow-md h-40 w-full"></div>
        
        <!-- Coordinates Hidden Input -->
        <input type="hidden" id="customerCoordinates" name="customer_coordinates">

        <!-- Estimated Delivery -->
        <div class="bg-red-50 p-2 rounded-lg flex items-start">
            <i class="fas fa-truck text-red-500 mr-1 mt-1"></i>
            <div>
                <span class="block text-gray-700 text-sm">Estimated Delivery:</span> 
                <span id="eta" class="text-red-600 font-bold">Calculating...</span>
            </div>
        </div>

        <!-- Payment Method -->
        <div>
            <label class="block mb-1.5 font-medium text-sm">
                <i class="fas fa-wallet mr-1.5 text-red-500"></i> Payment Method
            </label>
            <div class="relative">
                <select name="payment_method" 
                        class="w-full border rounded-lg px-3 py-1.5 appearance-none focus:ring focus:ring-red-200 focus:border-red-500 outline-none transition-all duration-300" 
                        required>
                    <option value="">Select Payment Method</option>
                    <option value="cash">Cash on Delivery</option>
                    <option value="card">Card Payment</option>
                </select>
                <div class="absolute right-3 top-3 text-gray-400 pointer-events-none">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>
        
        <!-- Redeem Points Section -->
        <div class="bg-yellow-50 p-2 rounded-lg">
            <label class="block mb-1.5 font-medium text-sm">
                <i class="fas fa-coins text-yellow-500 mr-1.5"></i> Redeem Loyalty Points
            </label>
            <div class="flex items-center">
                <input type="number" 
                       id="redeemPoints" 
                       name="redeem_points" 
                       min="0" 
                       max="<?php echo $loyaltyPoints; ?>" 
                       class="w-auto border rounded-lg px-10 py-1 focus:ring focus:ring-yellow-200 focus:border-yellow-500 outline-none transition-all duration-300" 
                       placeholder="Points">
                <button type="button" 
                        onclick="applyLoyaltyDiscount()" 
                        class="ml-2 px-10 py-1 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-300">
                    Apply
                </button>
            </div>
            <p class="text-xs text-gray-600 mt-1.5">
                You have <strong><?php echo $loyaltyPoints; ?></strong> points available.
                <strong>100 points = Rs. 10 discount</strong>
            </p>
        </div>

       <!-- Total and Order Button -->
        <div class="border-t border-gray-200 pt-2 mt-1">
            <div class="space-y-1 mb-1">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="text-gray-600">Rs. <?php echo number_format($cartTotals['total'], 2); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Discount</span>
                    <span id="discountAmount" class="text-red-500">- Rs. 0.00</span>
                </div>
            </div>
            <div class="flex justify-between mb-4 text-lg">
                <span class="font-bold">Total</span>
                <span id="totalAmount" class="text-red-500 font-bold">
                    Rs. <?php echo number_format($cartTotals['total'], 2); ?>
                </span>
            </div>
            
            <!-- Hidden Inputs for Discount and Redeemed Points -->
            <input type="hidden" id="loyaltyDiscount" name="loyalty_discount" value="0">
            <input type="hidden" id="redeemedPoints" name="redeemed_points" value="0">
            
            <button type="button" 
                    id="placeOrderBtn" 
                    class="w-full bg-red-500 text-white py-2.5 px-6 rounded-lg hover:bg-red-600 shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center <?php echo empty($_SESSION['cart']) ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                    <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                <i class="fas fa-check-circle mr-2"></i> Place Order
            </button>
            
            <a href="shop.php" class="mt-3 w-full py-2.5 px-6 border border-red-500 text-red-500 rounded-lg hover:bg-red-50 flex items-center justify-center transition-all duration-300">
                <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
            </a>
        </div>
    </form>
</div>
        
<script>
        // Google Maps Configuration
const bakeryLocation = { lat: 6.936492497391741, lng: 79.93596438025665 };
let map, customerMarker, directionsService, directionsRenderer;

// Initialize Map
function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: bakeryLocation,
        zoom: 15
    });

    // Bakery Marker
    new google.maps.Marker({
        position: bakeryLocation,
        map: map,
        title: 'Nishan Bakery',
        icon: {
            url: 'https://maps.google.com/mapfiles/kml/shapes/dining.png', // Replace with your bakery icon path
            scaledSize: new google.maps.Size(30, 30)
        }
    });

    // Initialize Directions Service
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: true
    });
}

// Update Quantity Function
function updateQuantity(productId, change) {
    const quantityInput = document.getElementById(`quantity-${productId}`);
    let currentQuantity = parseInt(quantityInput.value);
    let newQuantity = currentQuantity + change;
    
    if (newQuantity >= 1) {
        // Create form dynamically
        const form = document.createElement('form');
        form.method = 'POST';
        
        // Product ID input
        const productIdInput = document.createElement('input');
        productIdInput.type = 'hidden';
        productIdInput.name = 'product_id';
        productIdInput.value = productId;
        form.appendChild(productIdInput);
        
        // Quantity input
        const quantityInput = document.createElement('input');
        quantityInput.type = 'hidden';
        quantityInput.name = 'quantity';
        quantityInput.value = newQuantity;
        form.appendChild(quantityInput);
        
        // Update quantity flag
        const updateFlag = document.createElement('input');
        updateFlag.type = 'hidden';
        updateFlag.name = 'update_quantity';
        updateFlag.value = '1';
        form.appendChild(updateFlag);
        
        // Append to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}

// Remove From Cart Function
function removeFromCart(productId) {
    Swal.fire({
        title: 'Remove Item',
        text: 'Are you sure you want to remove this item from your cart?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Remove'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form dynamically
            const form = document.createElement('form');
            form.method = 'POST';
            
            // Product ID input
            const productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'product_id';
            productIdInput.value = productId;
            form.appendChild(productIdInput);
            
            // Remove from cart flag
            const removeFlag = document.createElement('input');
            removeFlag.type = 'hidden';
            removeFlag.name = 'remove_from_cart';
            removeFlag.value = '1';
            form.appendChild(removeFlag);
            
            // Append to body and submit
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Update Customer Location Function
function updateCustomerLocation() {
    const input = document.getElementById('customerLocation');
    const autocomplete = new google.maps.places.Autocomplete(input);
    
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        
        if (place.geometry) {
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            
            // Set coordinates
            document.getElementById('customerCoordinates').value = `${lat},${lng}`;
            
            const customerLocation = { lat, lng };
            
            // Remove previous customer marker
            if (customerMarker) {
                customerMarker.setMap(null);
            }

            // Add new customer marker
            customerMarker = new google.maps.Marker({
                position: customerLocation,
                map: map,
                title: "Your Location",
                icon: {
                    url: 'https://maps.google.com/mapfiles/kml/shapes/homegardenbusiness.png', // Replace with your custom marker path
                    scaledSize: new google.maps.Size(30, 30)
                }
            });

            // Center and zoom map
            map.panTo(customerLocation);
            map.setZoom(13);

            // Calculate route and ETA
            calculateRoute(customerLocation);
        }
    });
}

// Calculate Route and ETA
function calculateRoute(customerLocation) {
    directionsService.route(
        {
            origin: bakeryLocation,
            destination: customerLocation,
            travelMode: google.maps.TravelMode.DRIVING,
        },
        (response, status) => {
            if (status === google.maps.DirectionsStatus.OK) {
                directionsRenderer.setDirections(response);
                const eta = response.routes[0].legs[0].duration.text;
                document.getElementById("eta").textContent = eta;
            } else {
                console.error("Directions request failed: " + status);
                document.getElementById("eta").textContent = "Unable to calculate";
            }
        }
    );
}
// Apply Loyalty Discount Function
function applyLoyaltyDiscount() {
    const redeemPointsInput = document.getElementById('redeemPoints');
    const redeemPoints = parseInt(redeemPointsInput.value);
    const loyaltyPoints = <?php echo $loyaltyPoints; ?>;

    if (isNaN(redeemPoints)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Input',
            text: 'Please enter a valid number of points to redeem.'
        });
        return;
    }

    if (redeemPoints > loyaltyPoints) {
        Swal.fire({
            icon: 'error',
            title: 'Not Enough Points',
            text: 'You do not have enough loyalty points to redeem.'
        });
        return;
    }

    // Calculate discount (100 points = Rs. 10 discount)
    const discount = Math.floor(redeemPoints / 10) * 10;
    const newTotal = <?php echo $cartTotals['total']; ?> - discount;

    // Update the total display
    document.getElementById('totalAmount').textContent = `Rs. ${newTotal.toFixed(2)}`;
    document.getElementById('discountAmount').textContent = `- Rs. ${discount.toFixed(2)}`;

    // Store the discount in a hidden input for submission
    document.getElementById('loyaltyDiscount').value = discount;
    document.getElementById('redeemedPoints').value = redeemPoints;

    // Show success message
    Swal.fire({
        icon: 'success',
        title: 'Discount Applied',
        text: `You have successfully redeemed ${redeemPoints} points for a Rs. ${discount.toFixed(2)} discount.`
    });
}

// Order Validation and Submission Function
function placeOrder() {
    const location = document.getElementById('customerLocation').value;
    const paymentMethod = document.querySelector('select[name="payment_method"]').value;
    const coordinates = document.getElementById('customerCoordinates').value;
    const redeemedPoints = document.getElementById('redeemedPoints').value;
    const loyaltyDiscount = document.getElementById('loyaltyDiscount').value;

    // Validation checks
    if (!location) {
        Swal.fire({
            icon: 'error',
            title: 'Location Missing',
            text: 'Please enter your delivery location'
        });
        return false;
    }

    if (!paymentMethod) {
        Swal.fire({
            icon: 'error',
            title: 'Payment Method Missing',
            text: 'Please select a payment method'
        });
        return false;
    }

    if (!coordinates) {
        Swal.fire({
            icon: 'error',
            title: 'Location Not Confirmed',
            text: 'Please confirm your location using the map'
        });
        return false;
    }

    // Show loading state
    const orderButton = document.getElementById('placeOrderBtn');
    const originalButtonText = orderButton.innerHTML;
    orderButton.disabled = true;
    orderButton.innerHTML = 'Processing...';

    // Prepare data
    const formData = new FormData();
    formData.append('action', 'place_order');
    formData.append('delivery_location', location);
    formData.append('payment_method', paymentMethod);
    formData.append('customer_coordinates', coordinates);
    formData.append('redeemed_points', redeemedPoints);
    formData.append('loyalty_discount', loyaltyDiscount);

    // Send AJAX request
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Order success
            Swal.fire({
                icon: 'success',
                title: 'Order Placed Successfully!',
                html: `Your order #${data.order_id} has been received and is being processed.<br>
                       You earned <strong>${data.loyalty_points} loyalty points</strong>!`,
                confirmButtonColor: '#22c55e'
            }).then(() => {
                // Refresh the page to show empty cart
                window.location.reload();
            });
        } else {
            // Order failed
            Swal.fire({
                icon: 'error',
                title: 'Order Failed',
                text: data.message,
                confirmButtonColor: '#ef4444'
            });
            
            // Reset button
            orderButton.disabled = false;
            orderButton.innerHTML = originalButtonText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Order error
        Swal.fire({
            icon: 'error',
            title: 'System Error',
            text: 'There was a problem processing your order. Please try again.',
            confirmButtonColor: '#ef4444'
        });
        
        // Reset button
        orderButton.disabled = false;
        orderButton.innerHTML = originalButtonText;
    });
}

// Event Listeners and Initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Google Maps
    if (typeof google !== 'undefined' && google.maps) {
        initMap();
    }

    // Place Order Button Event Listener
    const orderButton = document.getElementById('placeOrderBtn');
    if (orderButton) {
        orderButton.addEventListener('click', placeOrder);
    }

    // Add location search functionality
    const locationInput = document.getElementById('customerLocation');
    if (locationInput) {
        locationInput.addEventListener('focus', updateCustomerLocation);
    }
});
    </script>
</body>
</html>