<?php
// Fetch user details from database after login
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'nishan_bakery'); // Adjust DB details
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM user_accounts WHERE id = $user_id")->fetch_assoc();

// Check if rating form was submitted
if (isset($_POST['submit_rating'])) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    // Insert rating into database
    $stmt = $conn->prepare("INSERT INTO ratings (user_id, user_name, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isis", $user_id, $user['name'], $rating, $comment);
    
    if ($stmt->execute()) {
        $rating_message = "Thank you for your feedback!";
    } else {
        $rating_error = "Error submitting rating: " . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Nishan Bakers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #FF4B60;
            --secondary: #2A2742;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }
        .nav-link {
            position: relative;
            overflow: hidden;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        .nav-link:hover::after {
            transform: translateX(0);
        }
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        .mobile-menu.active {
            transform: translateX(0);
        }
        .nav-hidden {
            transform: translateY(-100%);
        }
        
        /* Profile dropdown styling */
        .profile-dropdown {
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        .profile-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .profile-item {
            transition: all 0.2s ease;
        }
        .profile-item:hover {
            background-color: #f3f4f6;
            transform: translateX(5px);
        }
        
        /* Modal styling */
        .modal {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .modal.active {
            opacity: 1;
            visibility: visible;
        }
        
        /* Star rating styling */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            font-size: 2rem;
            padding: 0 0.1em;
            transition: all 0.2s ease;
            color: #ccc;
        }
        .star-rating input:checked ~ label {
            color: #ffb700;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffb700;
        }
    </style>
</head>
<body>
<nav id="navbar" class="fixed top-0 w-full bg-white/95 backdrop-blur-sm transition-all duration-500 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-20">
            <!-- Logo -->
            <a href="cus-index.php" class="text-2xl font-bold text-red-500">Nishan Bakers</a>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center space-x-8">
                <a href="cus-index.php" class="nav-link text-gray-700">Home</a>
                <a href="about.php" class="nav-link text-gray-700">About</a>
                <a href="shop.php" class="nav-link text-gray-700">Shop</a>
                <a href="cus-orders.php" class="nav-link text-gray-700">Orders</a>
                <a href="contact.php" class="nav-link text-gray-700">Contact</a>
            </div>

            <!-- Right Icons -->
            <div class="flex items-center space-x-6">
                <!-- Profile Icon with Dropdown -->
                <div class="relative">
                    <div id="profile-toggle" class="relative group cursor-pointer">
                        <img 
                            src="<?= $user['profile_image'] ?>" 
                            alt="Profile" 
                            class="w-12 h-12 rounded-full object-cover border-2 border-gray-300 hover:border-primary transition-all duration-300 shadow-md"
                        />
                        <div class="absolute inset-0 rounded-full bg-gradient-to-r from-purple-500 to-blue-500 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    </div>
                    
                    <!-- Profile Dropdown Menu -->
                    <div id="profile-dropdown" class="profile-dropdown absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-100 z-50">
                        <!-- User Info Section -->
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <img src="<?= $user['profile_image'] ?>" alt="Profile" class="w-10 h-10 rounded-full object-cover border-2 border-primary" />
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?= $user['name'] ?? 'User Name' ?></h3>
                                    <p class="text-xs text-gray-500"><?= $user['email'] ?? 'user@example.com' ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Menu Items -->
                        <div class="py-2">
                            <a href="dashboard.php" class="profile-item flex items-center space-x-3 px-4 py-3 text-gray-700 hover:text-primary">
                                <i class="fas fa-tachometer-alt text-lg w-6 text-primary"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="profile.php" class="profile-item flex items-center space-x-3 px-4 py-3 text-gray-700 hover:text-primary">
                                <i class="fas fa-user text-lg w-6 text-indigo-500"></i>
                                <span>Your Profile</span>
                            </a>
                            <a href="shop.php" class="profile-item flex items-center space-x-3 px-4 py-3 text-gray-700 hover:text-primary">
                                <i class="fas fa-store text-lg w-6 text-blue-500"></i>
                                <span>Shop</span>
                            </a>
                            <a href="cus-orders.php" class="profile-item flex items-center space-x-3 px-4 py-3 text-gray-700 hover:text-primary">
                                <i class="fas fa-shopping-bag text-lg w-6 text-green-500"></i>
                                <span>Your Orders</span>
                            </a>
                            
                            <!-- New Rating Option -->
                            <button id="open-rating-modal" class="profile-item flex items-center w-full text-left space-x-3 px-4 py-3 text-gray-700 hover:text-primary">
                                <i class="fas fa-star text-lg w-6 text-yellow-500"></i>
                                <span>Submit Rating</span>
                            </button>
                            
                            <!-- Separator -->
                            <div class="my-1 border-t border-gray-100"></div>
                            
                            <!-- Logout Option -->
                            <a href="logout.php" class="profile-item flex items-center space-x-3 px-4 py-3 text-gray-700 hover:text-red-500">
                                <i class="fas fa-sign-out-alt text-lg w-6 text-red-500"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <button id="menu-btn" class="lg:hidden text-xl">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="mobile-menu lg:hidden fixed top-20 left-0 w-full bg-white shadow-md z-50">
        <div class="flex flex-col space-y-4 p-4">
        <div class="hidden lg:flex items-center space-x-8">
                <a href="cus-index.php" class="nav-link text-gray-700">Home</a>
                <a href="about.php" class="nav-link text-gray-700">About</a>
                <a href="shop.php" class="nav-link text-gray-700">Shop</a>
                <a href="cus-orders.php" class="nav-link text-gray-700">Orders</a>
            <a href="#" class="text-gray-700">Recipes</a>
            <a href="#" class="text-gray-700">Contact</a>
        </div>
    </div>
</nav>

<!-- Rating Modal -->
<div id="rating-modal" class="modal fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
        <div class="relative">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-red-500 to-pink-500 rounded-t-lg px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-star mr-2"></i>
                    Rate Your Experience
                </h3>
                <button id="close-rating-modal" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                <?php if (isset($rating_message)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                        <p><?= $rating_message ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($rating_error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                        <p><?= $rating_error ?></p>
                    </div>
                <?php endif; ?>
                
                <form action="" method="POST" id="rating-form">
                    <!-- Star Rating -->
                    <div class="mb-6">
                        <p class="text-gray-700 mb-2 font-medium">How would you rate your experience?</p>
                        <div class="star-rating flex justify-center mb-2">
                            <input type="radio" name="rating" value="5" id="star5" required>
                            <label for="star5" class="text-3xl"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4" class="text-3xl"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3" class="text-3xl"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2" class="text-3xl"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1" class="text-3xl"><i class="fas fa-star"></i></label>
                        </div>
                        <p id="rating-text" class="text-center text-gray-500 italic">Select a rating</p>
                    </div>
                    
                    <!-- Comment -->
                    <div class="mb-6">
                        <label for="comment" class="block text-gray-700 font-medium mb-2">
                            Share your thoughts (optional)
                        </label>
                        <textarea 
                            id="comment" 
                            name="comment" 
                            rows="3" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 resize-none p-3 border"
                            placeholder="Tell us about your experience..."
                        ></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button 
                            type="submit" 
                            name="submit_rating"
                            class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-6 rounded-md transition-all duration-300 transform hover:scale-105 flex items-center"
                        >
                            <i class="fas fa-paper-plane mr-2"></i>
                            Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
        // Enhanced scroll behavior
        let lastScroll = 0;
        const navbar = document.getElementById('navbar');
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        let isMenuOpen = false;

        // Profile Dropdown Toggle
        const profileToggle = document.getElementById('profile-toggle');
        const profileDropdown = document.getElementById('profile-dropdown');
        let isProfileOpen = false;

        // Rating Modal Elements
        const ratingModal = document.getElementById('rating-modal');
        const modalContent = document.getElementById('modal-content');
        const openRatingModalBtn = document.getElementById('open-rating-modal');
        const closeRatingModalBtn = document.getElementById('close-rating-modal');
        const ratingForm = document.getElementById('rating-form');
        const ratingInputs = document.querySelectorAll('.star-rating input');
        const ratingText = document.getElementById('rating-text');

        // Rating text update function
        const updateRatingText = (value) => {
            const ratingMessages = {
                1: 'Poor - Not satisfied',
                2: 'Fair - Could be better',
                3: 'Good - Satisfied',
                4: 'Very Good - Quite satisfied',
                5: 'Excellent - Highly satisfied'
            };
            ratingText.textContent = ratingMessages[value] || 'Select a rating';
        };

        // Rating input event listeners
        ratingInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                updateRatingText(e.target.value);
            });
        });

        // Modal open function
        const openRatingModal = () => {
            ratingModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Animate in the modal
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        };

        // Modal close function
        const closeRatingModal = () => {
            // Animate out the modal
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                ratingModal.classList.remove('active');
                document.body.style.overflow = '';
            }, 300);
        };

        // Modal event listeners
        openRatingModalBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openRatingModal();
            // Close profile dropdown when opening modal
            profileDropdown.classList.remove('active');
            isProfileOpen = false;
        });

        closeRatingModalBtn.addEventListener('click', closeRatingModal);

        // Close modal when clicking outside
        ratingModal.addEventListener('click', (e) => {
            if (e.target === ratingModal) {
                closeRatingModal();
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && ratingModal.classList.contains('active')) {
                closeRatingModal();
            }
        });

        // Profile dropdown handling
        profileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            isProfileOpen = !isProfileOpen;
            
            if (isProfileOpen) {
                profileDropdown.classList.add('active');
            } else {
                profileDropdown.classList.remove('active');
            }
        });

        // Close profile dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target) && isProfileOpen) {
                profileDropdown.classList.remove('active');
                isProfileOpen = false;
            }
        });

        // Scroll handling
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll <= 0) {
                navbar.classList.remove('nav-hidden');
                navbar.style.background = 'rgb(255, 255, 255, 0.95)';
                return;
            }
            
            if (currentScroll > lastScroll && !navbar.classList.contains('nav-hidden')) {
                navbar.classList.add('nav-hidden');
            } else if (currentScroll < lastScroll && navbar.classList.contains('nav-hidden')) {
                navbar.classList.remove('nav-hidden');
            }
            
            lastScroll = currentScroll;
        });

        // Mobile menu toggle
        menuBtn.addEventListener('click', () => {
            isMenuOpen = !isMenuOpen;
            mobileMenu.classList.toggle('active');
            menuBtn.innerHTML = isMenuOpen ? 
                '<i class="fas fa-times text-xl text-[#FF4B60]"></i>' : 
                '<i class="fas fa-bars text-xl text-gray-700"></i>';
            
            // Prevent body scroll when menu is open
            document.body.style.overflow = isMenuOpen ? 'hidden' : '';
        });

        // Handle screen resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024 && isMenuOpen) {
                mobileMenu.classList.remove('active');
                menuBtn.innerHTML = '<i class="fas fa-bars text-xl text-gray-700"></i>';
                isMenuOpen = false;
                document.body.style.overflow = '';
            }
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!menuBtn.contains(e.target) && !mobileMenu.contains(e.target) && isMenuOpen) {
                mobileMenu.classList.remove('active');
                menuBtn.innerHTML = '<i class="fas fa-bars text-xl text-gray-700"></i>';
                isMenuOpen = false;
                document.body.style.overflow = '';
            }
        });

        // Show rating modal automatically if there was a submission
        <?php if (isset($rating_message) || isset($rating_error)): ?>
        document.addEventListener('DOMContentLoaded', openRatingModal);
        <?php endif; ?>
</script>
</body>
</html>