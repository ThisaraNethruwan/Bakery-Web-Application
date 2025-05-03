<?php
require_once('components/header.php');
require_once('../admin/db_connect.php');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = "../uploads/products/";
            $image = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Image uploaded successfully
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }

        $sql = "INSERT INTO products (name, description, price, category, image) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdss", $name, $description, $price, $category, $image);
        
        if ($stmt->execute()) {
            header("Location: products.php?success=1");
            exit;
        }
    }
    
    if (isset($_POST['edit_product'])) {
        $id = $_POST['product_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        
        $sql = "UPDATE products SET name=?, description=?, price=?, category=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsi", $name, $description, $price, $category, $id);
        
        if ($stmt->execute()) {
            header("Location: products.php?success=2");
            exit;
        }
    }
    
    if (isset($_POST['delete_product'])) {
        $id = $_POST['product_id'];
        
        $sql = "DELETE FROM products WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: products.php?success=3");
            exit;
        }
    }
}

require_once('components/sidebar.php');
?>

<!-- Main Content -->
<div class="flex-1 p-8">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold">Product Management</h2>
            <a href="?action=add" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                Add New Product
            </a>
        </div>

        <?php if ($action === 'list'): ?>
            <!-- Search and Sort -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex space-x-4">
                    <div class="flex-1">
                        <form action="" method="GET" class="flex space-x-2">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search products..." 
                                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2">
                            <select name="sort" class="border border-gray-300 rounded-lg px-4 py-2">
                                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                                <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low-High)</option>
                                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High-Low)</option>
                            </select>
                            <button type="submit" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        // Build query based on search and sort
                        $sql = "SELECT * FROM products";
                        if ($search) {
                            $sql .= " WHERE name LIKE '%$search%' OR description LIKE '%$search%' OR category LIKE '%$search%'";
                        }
                        switch ($sort) {
                            case 'name_desc':
                                $sql .= " ORDER BY name DESC";
                                break;
                            case 'price_asc':
                                $sql .= " ORDER BY price ASC";
                                break;
                            case 'price_desc':
                                $sql .= " ORDER BY price DESC";
                                break;
                            default:
                                $sql .= " ORDER BY name ASC";
                        }
                        
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0):
                            while ($product = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <img src="../uploads/products/<?php echo $product['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="h-12 w-12 object-cover rounded">
                                </td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($product['name']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($product['category']); ?></td>
                                <td class="px-6 py-4">$<?php echo number_format($product['price'], 2); ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <a href="?action=edit&id=<?php echo $product['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="POST" class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this product?');">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="delete_product" 
                                                    class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No products found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <?php
            $product = null;
            if ($action === 'edit' && isset($_GET['id'])) {
                $id = $_GET['id'];
                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
            }
            ?>
            <!-- Add/Edit Product Form -->
            <div class="p-6">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?php if ($product): ?>
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" required
                               value="<?php echo $product ? htmlspecialchars($product['name']) : ''; ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" required
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"><?php echo $product ? htmlspecialchars($product['description']) : ''; ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" name="price" step="0.01" required
                               value="<?php echo $product ? $product['price'] : ''; ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">Select Category</option>
                            <option value="Bread" <?php echo ($product && $product['category'] === 'Bread') ? 'selected' : ''; ?>>Bread</option>
                            <option value="Pastries" <?php echo ($product && $product['category'] === 'Pastries') ? 'selected' : ''; ?>>Pastries</option>
                            <option value="Cakes" <?php echo ($product && $product['category'] === 'Cakes') ? 'selected' : ''; ?>>Cakes</option>
                            <option value="Cookies" <?php echo ($product && $product['category'] === 'Cookies') ? 'selected' : ''; ?>>Cookies</option>
                        </select>
                    </div>

                    <?php if (!$product): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Image</label>
                        <input type="file" name="image" accept="image/*" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <?php endif; ?>

                    <div class="flex justify-end space-x-2">
                        <a href="products.php" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">Cancel</a>
                        <button type="submit" name="<?php echo $product ? 'edit_product' : 'add_product'; ?>"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            <?php echo $product ? 'Update Product' : 'Add Product'; ?>
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
?>
