<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed - Sweet Delights</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Thank You!</h1>
        <p class="text-lg text-gray-600 mb-6">Your order has been placed successfully.</p>
        
        <div class="mb-8 py-4 px-6 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-500">Order ID: <span id="orderId" class="font-semibold text-gray-700"></span></p>
        </div>
        
        <div class="space-y-4">
            <a href="index.php" class="block w-full py-3 px-4 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition duration-200">
                Continue Shopping
            </a>
            <a href="#" class="text-red-500 hover:text-red-700 text-sm font-medium">
                Track Your Order
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get order ID from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const orderId = urlParams.get('order_id');
            
            if (orderId) {
                document.getElementById('orderId').textContent = '#' + orderId;
            } else {
                document.getElementById('orderId').textContent = 'N/A';
            }
        });
    </script>
</body>
</html>