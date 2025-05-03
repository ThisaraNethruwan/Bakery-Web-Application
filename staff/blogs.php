<?php
require_once('components/header.php');
require_once('../admin/db_connect.php');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle blog post submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_post']) || isset($_POST['edit_post'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $category = $_POST['category'];
        $staff_id = $_SESSION['user_id'];
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = "../uploads/blogs/";
            $image = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Image uploaded successfully
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
        
        if (isset($_POST['add_post'])) {
            $sql = "INSERT INTO blog_posts (title, content, category, image, staff_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $content, $category, $image, $staff_id);
        } else {
            $post_id = $_POST['post_id'];
            if ($image) {
                $sql = "UPDATE blog_posts SET title=?, content=?, category=?, image=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $title, $content, $category, $image, $post_id);
            } else {
                $sql = "UPDATE blog_posts SET title=?, content=?, category=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $title, $content, $category, $post_id);
            }
        }
        
        if ($stmt->execute()) {
            header("Location: blogs.php?success=1");
            exit;
        }
    }
    
    if (isset($_POST['delete_post'])) {
        $post_id = $_POST['post_id'];
        
        $sql = "DELETE FROM blog_posts WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $post_id);
        
        if ($stmt->execute()) {
            header("Location: blogs.php?success=2");
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
            <h2 class="text-lg font-semibold">Blog & Announcements</h2>
            <a href="?action=add" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                Add New Post
            </a>
        </div>

        <?php if ($action === 'list'): ?>
            <!-- Blog Posts Grid -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    $sql = "SELECT b.*, s.username as author 
                            FROM blog_posts b 
                            JOIN staff s ON b.staff_id = s.id 
                            ORDER BY b.created_at DESC";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0):
                        while ($post = $result->fetch_assoc()):
                    ?>
                        <div class="bg-white border rounded-lg overflow-hidden shadow-sm">
                            <?php if ($post['image']): ?>
                                <img src="../uploads/blogs/<?php echo $post['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>"
                                     class="w-full h-48 object-cover">
                            <?php endif; ?>
                            
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                        <?php echo htmlspecialchars($post['category']); ?>
                                    </span>
                                    <div class="flex space-x-2">
                                        <a href="?action=edit&id=<?php echo $post['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="POST" class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this post?');">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <button type="submit" name="delete_post" 
                                                    class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <h3 class="text-lg font-semibold mb-2">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </h3>
                                
                                <p class="text-gray-600 mb-4 line-clamp-3">
                                    <?php echo htmlspecialchars(substr($post['content'], 0, 150)) . '...'; ?>
                                </p>
                                
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>By <?php echo htmlspecialchars($post['author']); ?></span>
                                    <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div class="col-span-3 text-center text-gray-500 py-8">
                            No blog posts found
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <?php
            $post = null;
            if ($action === 'edit' && isset($_GET['id'])) {
                $id = $_GET['id'];
                $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $post = $stmt->get_result()->fetch_assoc();
            }
            ?>
            <!-- Add/Edit Blog Post Form -->
            <div class="p-6">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?php if ($post): ?>
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" required
                               value="<?php echo $post ? htmlspecialchars($post['title']) : ''; ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">Select Category</option>
                            <option value="News" <?php echo ($post && $post['category'] === 'News') ? 'selected' : ''; ?>>News</option>
                            <option value="Recipe" <?php echo ($post && $post['category'] === 'Recipe') ? 'selected' : ''; ?>>Recipe</option>
                            <option value="Event" <?php echo ($post && $post['category'] === 'Event') ? 'selected' : ''; ?>>Event</option>
                            <option value="Announcement" <?php echo ($post && $post['category'] === 'Announcement') ? 'selected' : ''; ?>>Announcement</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Content</label>
                        <textarea name="content" rows="10" required
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"><?php echo $post ? htmlspecialchars($post['content']) : ''; ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Featured Image
                            <?php if ($post && $post['image']): ?>
                                <span class="text-xs text-gray-500">(Leave empty to keep current image)</span>
                            <?php endif; ?>
                        </label>
                        <input type="file" name="image" accept="image/*"
                               <?php echo (!$post) ? 'required' : ''; ?>
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <?php if ($post && $post['image']): ?>
                            <div class="mt-2">
                                <img src="../uploads/blogs/<?php echo $post['image']; ?>" 
                                     alt="Current Image"
                                     class="h-32 object-cover rounded">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <a href="blogs.php" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">Cancel</a>
                        <button type="submit" name="<?php echo $post ? 'edit_post' : 'add_post'; ?>"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            <?php echo $post ? 'Update Post' : 'Publish Post'; ?>
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
