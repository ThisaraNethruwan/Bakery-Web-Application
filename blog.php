<?php
include "./components/navbar.html";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nishan Bakery - Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }
        .font-poppins { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 font-poppins">
    <br><br><br>

    <!-- Blog Posts Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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
                
            $sql = "SELECT * FROM blogs ORDER BY created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Check if image path exists, otherwise use default
                    // Prepend the uploads/blogs path to the image filename if it doesn't already include it
                    if (!empty($row['image_path'])) {
                        // Check if the image path already includes the uploads/blogs directory
                        if (strpos($row['image_path'], 'uploads/blogs/') === 0) {
                            $image = $row['image_path'];
                        } else {
                            $image = 'uploads/blogs/' . $row['image_path'];
                        }
                    } else {
                        $image = 'images/default-blog.jpg';
                    }
                    
                    $date = date('F j, Y', strtotime($row['created_at']));
                    $excerpt = substr(strip_tags($row['content']), 0, 150) . '...';
                    ?>
                    <article class="bg-white rounded-lg shadow-lg overflow-hidden transform transition duration-300 hover:scale-[1.02]">
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" 
                             class="w-full h-48 object-cover">
                        <div class="p-6">
                            <div class="text-sm text-gray-500 mb-2"><?php echo $date; ?></div>
                            <h2 class="text-xl font-playfair font-bold mb-3 text-gray-900">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h2>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($excerpt); ?></p>
                            <a href="blog-post.php?id=<?php echo $row['id']; ?>" 
                               class="inline-block px-4 py-2 bg-[#c41c1c] text-white rounded-md hover:bg-[#a01717] transition duration-300">
                                Read More
                            </a>
                        </div>
                    </article>
                    <?php
                }
            } else {
                ?>
                <div class="col-span-full text-center py-12">
                    <div class="text-4xl mb-4">📝</div>
                    <h3 class="text-2xl font-playfair font-bold text-gray-900 mb-2">No Blog Posts Yet</h3>
                    <p class="text-gray-600">Check back soon for delicious stories and updates!</p>
                </div>
                <?php
            }
            
            // Close the database connection
            closeConnection($conn);
            ?>
        </div>
    </div>

</body>
</html>