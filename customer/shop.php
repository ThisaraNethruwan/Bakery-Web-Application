<?php include "nav.php"; ?>

<?php


// Database Connection
$conn = mysqli_connect("localhost", "root", "", "nishan_bakery");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    
    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    // Check if product already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }
    
    // If not found, add new item
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => 1,
            'image' => $product['image']
        ];
    }
    
  
}

// Build query for filtered products
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

// Add search condition
if (!empty($search_query)) {
    $search_param = "%" . $search_query . "%";
    $sql .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

// Add category filter
if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

// Add sorting
if ($sort_by == 'price_asc') {
    $sql .= " ORDER BY price ASC";
} else if ($sort_by == 'price_desc') {
    $sql .= " ORDER BY price DESC";
} else if ($sort_by == 'name_asc') {
    $sql .= " ORDER BY name ASC";
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$filtered_products = [];

// Fetch all products
while ($row = $result->fetch_assoc()) {
    $filtered_products[] = $row;
}

// Get unique categories for filter
$cat_sql = "SELECT DISTINCT category FROM products ORDER BY category";
$cat_result = $conn->query($cat_sql);
$categories = [];

while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row['category'];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Delights Bakery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            light: '#FF6B6B',
                            DEFAULT: '#DC2626',
                            dark: '#B91C1C',
                        },
                        secondary: {
                            light: '#FFF1F1',
                            DEFAULT: '#FECACA',
                            dark: '#FECACA',
                        }
                    }
                }
            }
        }

        // Show cart added notification
        <?php if(isset($_GET['cart_added']) && $_GET['cart_added'] == 'true'): ?>
        window.addEventListener('load', function() {
            Swal.fire({
                icon: 'success',
                title: 'Item Added to Cart',
                text: 'Your item has been successfully added to the cart!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });
        <?php endif; ?>
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .product-card {
            transition: all 0.3s ease;
        }
        
        .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            background-color:rgb(230, 66, 66);
            color: white;
            border-radius: 20px;
            font-size: 0.8rem;
        }
    </style>
</head>
<br><br><br>
<body>
    <!-- Header -->
    <header>
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="cart.php" class="flex items-center px-4 py-2 bg-white text-red-500 rounded-lg shadow hover:bg-secondary-dark">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="font-bold">Cart (<?php echo count($_SESSION['cart']); ?>)</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Search & Filter Section -->
    <section class="bg-secondary-default py-1 shadow-md">
        <div class="container mx-auto px-2">
            <form action="shop.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Search for bakery items..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full px-10 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-lienjoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <select name="category" class="w-full px-2 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $category_filter == $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <select name="sort_by" class="w-full px-2 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Sort By</option>
                        <option value="price_asc" <?php echo $sort_by == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo $sort_by == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_asc" <?php echo $sort_by == 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                    </select>
                </div>
                <div class="md:col-span-4">
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg shadow transition duration-300 ease-in-out">
                        Filter Results
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Products Section -->
    <section class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold mb-8 text-red-500">
            <?php if (!empty($search_query)): ?>
                Search Results for "<?php echo htmlspecialchars($search_query); ?>"
            <?php elseif (!empty($category_filter)): ?>
                <?php echo htmlspecialchars($category_filter); ?>
            <?php else: ?>
                Our Freshly Baked Products
            <?php endif; ?>
        </h2>

        <?php if (empty($filtered_products)): ?>
            <div class="text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-lienjoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No products found</h3>
                <p class="text-gray-500">Try adjusting your search or filter to find what you're looking for.</p>
                <a href="shop.php" class="inline-block mt-4 px-6 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition duration-300">View All Products</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($filtered_products as $product): ?>
                    <div class="product-card bg-white rounded-lg overflow-hidden shadow-md">
                        <span class="badge"><?php echo htmlspecialchars($product['category']); ?></span>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-red-500 font-bold text-lg">Rs.<?php echo number_format($product['price'], 2); ?></span>
                                <form method="post" action="shop.php">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="add_to_cart" class="px-2 py-1 bg-green-100 text-green-600 font-bold rounded-lg hover:bg-green-200 transition duration-300 ease-in-out">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</body>
</html>