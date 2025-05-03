<?php
include "./components/navbar.html";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nishan Bakery - Blog Post</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }
        .font-poppins { font-family: 'Poppins', sans-serif; }
        .blog-content {
            line-height: 1.8;
        }
        .blog-content p {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body class="bg-gray-50 font-poppins">
    <br><br><br>

    <!-- Blog Post Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php
            // Database connection parameters
            $servername = "localhost";
            $username = "root"; // Change to your database username
            $password = ""; // Change to your database password
            $dbname = "Nishan_Bakery";
            
            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            // Function to safely close database connection
            function closeConnection($conn) {
                if ($conn) {
                    $conn->close();
                }
            }
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = intval($_GET['id']);
            $sql = "SELECT * FROM blogs WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $post = $result->fetch_assoc();
                $date = date('F j, Y', strtotime($post['created_at']));
                
                // Fix the image path by ensuring it includes uploads/blogs/
                if (!empty($post['image_path'])) {
                    // Check if the image path already includes the uploads/blogs directory
                    if (strpos($post['image_path'], 'uploads/blogs/') === 0) {
                        $image = $post['image_path'];
                    } else {
                        $image = 'uploads/blogs/' . $post['image_path'];
                    }
                } else {
                    $image = null;
                }
                ?>
                <article>
                    <!-- Back button -->
                    <a href="blog.php" class="inline-flex items-center text-[#c41c1c] mb-8 hover:text-[#a01717]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Blog
                    </a>

                    <!-- Featured Image -->
                    <?php if ($image): ?>
                    <img src="<?php echo htmlspecialchars($image); ?>" 
                         alt="<?php echo htmlspecialchars($post['title']); ?>"
                         class="w-full h-[400px] object-cover rounded-lg shadow-lg mb-8">
                    <?php endif; ?>

                    <!-- Post Header -->
                    <div class="text-center mb-12">
                        <h1 class="text-4xl md:text-5xl font-playfair font-bold text-gray-900 mb-4">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </h1>
                        <div class="text-gray-600"><?php echo $date; ?></div>
                    </div>

                    <!-- Post Content -->
                    <div class="prose max-w-none blog-content text-gray-700">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>

                    <!-- Share Section -->
                    <div class="mt-12 pt-8 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Share this post</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-600 hover:text-[#c41c1c]" title="Share on Facebook">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.77,7.46H14.5v-1.9c0-.9.6-1.1,1-1.1h3V.5h-4.33C10.24.5,9.5,3.44,9.5,5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4Z"/>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-600 hover:text-[#c41c1c]" title="Share on Twitter">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.954 4.569c-.885.389-1.83.654-2.825.775 1.014-.611 1.794-1.574 2.163-2.723-.951.555-2.005.959-3.127 1.184-.896-.959-2.173-1.559-3.591-1.559-2.717 0-4.92 2.203-4.92 4.917 0 .39.045.765.127 1.124C7.691 8.094 4.066 6.13 1.64 3.161c-.427.722-.666 1.561-.666 2.475 0 1.71.87 3.213 2.188 4.096-.807-.026-1.566-.248-2.228-.616v.061c0 2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.314 0-.615-.03-.916-.086.631 1.953 2.445 3.377 4.604 3.417-1.68 1.319-3.809 2.105-6.102 2.105-.39 0-.779-.023-1.17-.067 2.189 1.394 4.768 2.209 7.557 2.209 9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63.961-.689 1.8-1.56 2.46-2.548l-.047-.02z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
                <?php
            } else {
                ?>
                <div class="text-center py-12">
                    <h2 class="text-2xl font-playfair font-bold text-gray-900 mb-4">Blog Post Not Found</h2>
                    <p class="text-gray-600 mb-8">The blog post you're looking for doesn't exist or has been removed.</p>
                    <a href="blog.php" class="inline-block px-6 py-3 bg-[#c41c1c] text-white rounded-md hover:bg-[#a01717] transition duration-300">
                        Return to Blog
                    </a>
                </div>
                <?php
            }
            $stmt->close();
        } else {
            header("Location: blog.php");
            exit();
        }
        
        // Close the database connection
        closeConnection($conn);
        ?>
    </main>

</body>
</html>