<?php
// Handle product operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $description = $_POST['description'];
                $price = $_POST['price'];
                $category = $_POST['category'];
                
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../uploads/products/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $image_path = $upload_dir . time() . '_' . $_FILES['image']['name'];
                    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
                }
                
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdss", $name, $description, $price, $category, $image_path);
                $stmt->execute();
                break;

            case 'edit':
                $id = $_POST['id'];
                $name = $_POST['name'];
                $description = $_POST['description'];
                $price = $_POST['price'];
                $category = $_POST['category'];
                
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../uploads/products/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $image_path = $upload_dir . time() . '_' . $_FILES['image']['name'];
                    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
                    
                    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=?, image=? WHERE id=?");
                    $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image_path, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=? WHERE id=?");
                    $stmt->bind_param("ssdsi", $name, $description, $price, $category, $id);
                }
                $stmt->execute();
                break;

            case 'delete':
                $id = $_POST['id'];
                $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
        }
    }
}

// Get search and sort parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Build query
$query = "SELECT * FROM products WHERE 1=1";
if ($search) {
    $search = "%$search%";
    $query .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
}
$query .= " ORDER BY $sort $order";

// Execute search
$stmt = $conn->prepare($query);
if ($search) {
    $stmt->bind_param("sss", $search, $search, $search);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Product Management</h2>
        <button onclick="showAddModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Add New Product
        </button>
    </div>

    <!-- Search and Sort -->
    <div class="mb-6">
        <form method="GET" action="index.php" class="flex gap-4">
            <input type="hidden" name="page" value="products">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search products..." 
                   class="border rounded px-4 py-2 w-64">
            <select name="sort" class="border rounded px-4 py-2">
                <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name</option>
                <option value="price" <?php echo $sort === 'price' ? 'selected' : ''; ?>>Price</option>
                <option value="category" <?php echo $sort === 'category' ? 'selected' : ''; ?>>Category</option>
            </select>
            <select name="order" class="border rounded px-4 py-2">
                <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
            </select>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
                Apply
            </button>
        </form>
    </div>

    <!-- Products Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-primary/5">
                    <th class="px-4 py-2">Image</th>
                    <th class="px-4 py-2">
                        <a href="<?php echo '?page=products&sort=name&order=' . ($sort === 'name' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search); ?>" 
                           class="flex items-center text-primary">
                            Name
                            <?php if ($sort === 'name'): ?>
                                <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">
                        <a href="<?php echo '?page=products&sort=price&order=' . ($sort === 'price' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search); ?>" 
                           class="flex items-center text-primary">
                            Price
                            <?php if ($sort === 'price'): ?>
                                <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-4 py-2">
                        <a href="<?php echo '?page=products&sort=category&order=' . ($sort === 'category' && $order === 'ASC' ? 'DESC' : 'ASC') . '&search=' . urlencode($search); ?>" 
                           class="flex items-center text-primary">
                            Category
                            <?php if ($sort === 'category'): ?>
                                <span class="ml-1"><?php echo $order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $result->fetch_assoc()): ?>
                <tr>
                    <td class="px-4 py-2">
                        <?php if ($product['image']): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-16 h-16 object-cover">
                        <?php else: ?>
                            <div class="w-16 h-16 bg-gray-200"></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($product['name']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($product['description']); ?></td>
                    <td class="px-4 py-2">Rs.<?php echo number_format($product['price'], 2); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($product['category']); ?></td>
                    <td class="px-4 py-2">
                        <button onclick="showEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)"
                                class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 mr-2">
                            Edit
                        </button>
                        <button onclick="deleteProduct(<?php echo $product['id']; ?>)"
                                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                            Delete
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
            <h3 id="modalTitle" class="text-xl font-bold mb-4">Add New Product</h3>
            <form id="productForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="productId">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" id="productName" required
                           class="border rounded w-full px-3 py-2">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea name="description" id="productDescription" required
                              class="border rounded w-full px-3 py-2"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Price</label>
                    <input type="number" name="price" id="productPrice" step="0.01" required
                           class="border rounded w-full px-3 py-2">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                    <input type="text" name="category" id="productCategory" required
                           class="border rounded w-full px-3 py-2">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Image</label>
                    <input type="file" name="image" id="productImage" accept="image/*"
                           class="border rounded w-full px-3 py-2">
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeModal()"
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('formAction').value = 'add';
    document.getElementById('productForm').reset();
    document.getElementById('productModal').classList.remove('hidden');
}

function showEditModal(product) {
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productDescription').value = product.description;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productCategory').value = product.category;
    document.getElementById('productModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('productModal').classList.add('hidden');
}

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
