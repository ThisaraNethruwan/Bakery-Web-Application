<?php
// Handle blog operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $title = $_POST['title'];
            $content = $_POST['content'];
            
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = '../uploads/blogs/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_name = time() . '_' . $_FILES['image']['name'];
                $image_path = 'uploads/blogs/' . $file_name; // Store relative path in database
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name);
            }
            
            $stmt = $conn->prepare("INSERT INTO blogs (title, content, image_path) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $content, $image_path);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Blog post created successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to create blog post.";
            }
            break;
            
        case 'edit':
            $id = $_POST['id'];
            $title = $_POST['title'];
            $content = $_POST['content'];
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                // Get current image to delete later if needed
                $stmt = $conn->prepare("SELECT image_path FROM blogs WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $old_blog = $result->fetch_assoc();
                $old_image = $old_blog['image_path'];
                
                // Upload new image
                $upload_dir = '../uploads/blogs/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_name = time() . '_' . $_FILES['image']['name'];
                $image_path = 'uploads/blogs/' . $file_name; // Store relative path in database
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name);
                
                $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ?, image_path = ? WHERE id = ?");
                $stmt->bind_param("sssi", $title, $content, $image_path, $id);
                
                if ($stmt->execute()) {
                    // Delete old image if it exists
                    if (!empty($old_image) && file_exists('../' . $old_image)) {
                        unlink('../' . $old_image);
                    }
                    $_SESSION['success_message'] = "Blog post updated successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to update blog post.";
                }
            } else {
                $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ? WHERE id = ?");
                $stmt->bind_param("ssi", $title, $content, $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Blog post updated successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to update blog post.";
                }
            }
            break;
            
        case 'delete':
            $id = $_POST['id'];
            
            // Get the image path before deleting
            $stmt = $conn->prepare("SELECT image_path FROM blogs WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $blog = $result->fetch_assoc();
            
            // Delete the blog post
            $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                // Delete the image file if it exists
                if (!empty($blog['image_path']) && file_exists('../' . $blog['image_path'])) {
                    unlink('../' . $blog['image_path']);
                }
                $_SESSION['success_message'] = "Blog post deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to delete blog post.";
            }
            break;
    }
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Build query
$query = "SELECT * FROM blogs WHERE 1=1";

if ($search) {
    $search = "%$search%";
    $query .= " AND (title LIKE ? OR content LIKE ?)";
}

$query .= " ORDER BY $sort $order";

// Execute search
$stmt = $conn->prepare($query);
if ($search) {
    $stmt->bind_param("ss", $search, $search);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-primary">Blogs & Announcements</h2>
        <button onclick="showBlogModal('add')" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
            Add New Blog Post
        </button>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php 
            echo htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php 
            echo htmlspecialchars($_SESSION['error_message']);
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Search Form -->
    <form method="GET" action="index.php" class="flex gap-4 mb-6">
        <input type="hidden" name="page" value="blogs">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Search blogs..." 
               class="border rounded px-4 py-2 w-64 focus:border-primary focus:ring-primary">
               
        <select name="sort" class="border rounded px-4 py-2 focus:border-primary focus:ring-primary">
            <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Date</option>
            <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title</option>
            <option value="updated_at" <?php echo $sort === 'updated_at' ? 'selected' : ''; ?>>Last Updated</option>
        </select>
        
        <select name="order" class="border rounded px-4 py-2 focus:border-primary focus:ring-primary">
            <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Newest First</option>
            <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
        </select>
        
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
            Apply
        </button>
    </form>

    <!-- Blog Posts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($blog = $result->fetch_assoc()): ?>
        <div class="border rounded-lg overflow-hidden hover:border-primary transition-colors">
            <?php if (!empty($blog['image_path'])): ?>
            <img src="<?php echo '../' . htmlspecialchars($blog['image_path']); ?>" 
                 alt="<?php echo htmlspecialchars($blog['title']); ?>"
                 class="w-full h-48 object-cover">
            <?php endif; ?>
            
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-primary"><?php echo htmlspecialchars($blog['title']); ?></h3>
                    <div class="flex gap-2">
                        <button onclick="showBlogModal('edit', <?php echo htmlspecialchars(json_encode($blog)); ?>)"
                                class="text-primary hover:text-primary/90">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button onclick="deleteBlog(<?php echo $blog['id']; ?>)"
                                class="text-red-600 hover:text-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="text-sm text-gray-600 mb-2">
                    <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                    <?php if ($blog['updated_at'] !== $blog['created_at']): ?>
                        <span class="text-gray-400"> · Updated: <?php echo date('M d, Y', strtotime($blog['updated_at'])); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="text-gray-700 line-clamp-3">
                    <?php echo nl2br(htmlspecialchars(substr($blog['content'], 0, 200) . (strlen($blog['content']) > 200 ? '...' : ''))); ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        
        <?php if ($result->num_rows === 0): ?>
            <div class="col-span-3 text-center py-8 text-gray-500">
                No blog posts found with the current filters.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Blog Modal -->
<div id="blogModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-xl font-bold">Add New Blog Post</h3>
                <button onclick="closeBlogModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="blogForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="blogId">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Title</label>
                    <input type="text" name="title" id="blogTitle" required
                           class="border rounded w-full px-3 py-2 focus:border-primary focus:ring-primary">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Content</label>
                    <textarea name="content" id="blogContent" required rows="10"
                              class="border rounded w-full px-3 py-2 focus:border-primary focus:ring-primary"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Image</label>
                    <input type="file" name="image" id="blogImage" accept="image/*"
                           class="border rounded w-full px-3 py-2 focus:border-primary focus:ring-primary">
                    <p class="text-sm text-gray-600 mt-1">Optional. Maximum file size: 2MB</p>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeBlogModal()"
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showBlogModal(action, blog = null) {
    document.getElementById('modalTitle').textContent = action === 'add' ? 'Add New Blog Post' : 'Edit Blog Post';
    document.getElementById('formAction').value = action;
    
    if (blog) {
        document.getElementById('blogId').value = blog.id;
        document.getElementById('blogTitle').value = blog.title;
        document.getElementById('blogContent').value = blog.content;
    } else {
        document.getElementById('blogForm').reset();
    }
    
    document.getElementById('blogModal').classList.remove('hidden');
}

function closeBlogModal() {
    document.getElementById('blogModal').classList.add('hidden');
}

function deleteBlog(id) {
    if (confirm('Are you sure you want to delete this blog post?')) {
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