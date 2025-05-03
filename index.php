
<?php

include "./components/navbar.html";
include "./components/load.html";

// Database connection
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "Nishan_Bakery";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nishan Bakers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.0/css/boxicons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  
  
    <style>
        @keyframes fullRotation {
            from { transform: perspective(1000px) rotateY(0deg); }
            to { transform: perspective(1000px) rotateY(360deg); }
        }
        
        .rotate-anim {
            animation: fullRotation 1.5s ease-in-out;
        }

        .custom-heading {
            font-size: 70px;
            line-height: 1.5;
            font-weight: bold;
            color: #1B1B3F;
        }

        @media (max-width: 1024px) {
            .custom-heading {
                font-size: 36px;
            }
        }

        @media (max-width: 768px) {
            .custom-heading {
                font-size: 32px;
            }
        }

        @media (max-width: 640px) {
            .custom-heading {
                font-size: 28px;
            }
        }

      
    </style>
</head>
<body class="bg-white">
    <!-- Main container with side margins -->
    <div class="max-w-[1450px] mx-auto px-6 md:px-8 lg:px-12 mt-20"> <!-- Adjusted margins -->
        <div class="flex flex-col lg:flex-row items-center justify-between gap-8 lg:gap-12"> <!-- Added gap for spacing -->
            <!-- Left Column -->
            <div class="w-full lg:w-1/2 text-center lg:text-left"> <!-- Center on mobile -->
                <!-- Get Your Orders Badge -->
                <div class="inline-flex items-center bg-red-100 rounded-full px-6 py-2 mb-6">
                    <span class="text-red-500 text-lg font-medium">Get Your Orders</span>
                    <img src="./images/french-fries.svg" alt="French fries" class="w-8 h-8 ml-4"/>
                </div>

                <!-- Main Heading -->
                <h1 class="custom-heading mb-6 ">
                    Get Your Cuisine<br/>
                    Delivered Right to<br/>
                    <span class="text-red-500">Your Door.....</span>
                </h1>
    <!-- Description -->
<p class="custom-paragraph mb-8 max-w-auto mx-auto lg:mx-0 text-gray-600 font-serif italic tracking-wide">
    Food that is delivered at the right time. The trendy food delivery 
    partner. Good food is what we deliver. Your hunger companion.
</p>

              

                <!-- Explore Button -->
                <a href="shop.php" 
                   class="inline-block bg-red-500 text-white px-10 py-3 rounded-full 
                          hover:bg-red-600 transition-colors duration-300 text-lg font-medium">
                    Explore Food
                </a>
            </div>

            <!-- Right Column - Rotating Image -->
            <div class="w-full lg:w-1/2 flex justify-center">
                <div class="relative w-full max-w-[600px] aspect-square"> <!-- Made image responsive -->
                    <img id="bakeryImage" 
                         src="./images/bakery2.png" 
                         alt="Bakery"
                         class="w-full h-full object-contain" />
                </div>
            </div>
        </div>
    </div>

    <script>
        const bakeryImage = document.getElementById('bakeryImage');

        function performRotation() {
            bakeryImage.classList.add('rotate-anim');
            
            setTimeout(() => {
                bakeryImage.classList.remove('rotate-anim');
            }, 1500);
        }

        // Initial rotation after page load
        setTimeout(performRotation, 1000);

        // Repeat rotation every 6 seconds
        setInterval(performRotation, 6000);
    </script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
    }
    
    .service-card {
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      backface-visibility: hidden;
    }
    
    .service-card:hover {
      transform: translateY(-15px);
      box-shadow: 0 10px 5px rgb(243, 220, 8);
      
    }
    
    .service-card .img-container {
      transition: all 0.5s ease;
      overflow: hidden;
      position: relative;
    }
    
    .service-card:hover .img-container::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      animation: pulse 1.5s infinite;
    }
    
    .service-card:hover img {
      transform: scale(1.1) rotate(3deg);
    }
    
    @keyframes pulse {
      0% { opacity: 0.2; }
      50% { opacity: 0.6; }
      100% { opacity: 0.2; }
    }
    
    .title-animation {
      display: inline-block;
      animation: titleFloat 3s ease-in-out infinite;
    }
    
    @keyframes titleFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
  </style>
  <body class="bg-gradient-to-br from-gray-50 to-gray-100">
  <!--=============== Services ===============-->
  <section class="py-20 md:py-28 relative overflow-hidden" id="services">
    <!-- Background decorative elements -->
    <div class="absolute top-0 left-0 w-32 h-32 bg-indigo-100 rounded-full opacity-50 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-yellow-200 rounded-full opacity-40 translate-x-1/4 translate-y-1/4"></div>
    
    <div class="container mx-auto px-6 md:px-20 max-w-full">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 md:gap-10 items-center">
        <!-- Left column with heading and text -->
        <div class="md:col-span-1 pr-6" data-aos="fade-right">
          <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-800 mb-6 leading-tight">
            <span class="title-animation block text-red-500">Why we're</span> 
            <span class="title-animation block" style="animation-delay: 0.2s">Best in our</span>
            <span class="title-animation block" style="animation-delay: 0.4s">Twon</span>
          </h2>
          <p class="text-gray-600 leading-relaxed text-base md:text-lg">
            whole grains and low-fat dairy can help to reduce your risk of heart
            disease by maintaining blood pressure and
          </p>
        </div>
        
        <!-- First Card - Bread -->
        <div class="service-card-container" data-aos="fade-up" data-aos-delay="100">
          <div class="service-card bg-white  p-4 md:p-6 rounded-3xl shadow-lg flex flex-col items-center">
            <div class="img-container mb-6 relative w-45 h-45 flex items-center justify-center">
              <img src="./images/bread.png" alt="Bread" class="w-40 h-40 object-contain"/>
            </div>
            <h3 class="text-xl md:text-2xl font-bold font-semibold text-center text-gray-800 leading-relaxed">
              Choose <br>
              your favorite <br>
              food
            </h3>
          </div>
        </div>
        
        <!-- Second Card - Delivery -->
        <div class="service-card-container" data-aos="fade-up" data-aos-delay="200">
          <div class="service-card bg-white p-4 md:p-6 rounded-3xl shadow-lg flex flex-col items-center">
            <div class="img-container mb-8 relative w-40 h-40 flex items-center justify-center">
              <img src="./images/delivery-icon.svg" alt="Delivery" class="w-36 h-36 object-contain"/>
            </div>
            <h3 class="text-xl md:text-2xl font-semibold text-center text-gray-800 leading-relaxed">
              Get delivery <br>
              at your door <br>
              step
            </h3>
          </div>
        </div>
        
        <!-- Third Card - Reviews -->
        <div class="service-card-container" data-aos="fade-up" data-aos-delay="300">
          <div class="service-card bg-white p-4 md:p-10 rounded-3xl shadow-lg flex flex-col items-center">
            <div class="img-container mb-8 relative w-40 h-40 flex items-center justify-center">
              <img src="./images/phone-icon.svg" alt="Phone" class="w-36 h-36 object-contain"/>
            </div>
            <h3 class="text-xl md:text-2xl font-semibold text-center text-gray-800 leading-relaxed">
              We have <br>
              Better Reviews
            </h3>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- AOS for animations -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
  
  <script>
    // Initialize AOS
    AOS.init({
      duration: 800,
      once: false,
      mirror: true
    });
    
    // Add smooth hover interactions for images
    document.querySelectorAll('.service-card').forEach(card => {
      const img = card.querySelector('img');
      
      card.addEventListener('mouseenter', function() {
        // Smoother image scale effect
        if (img) {
          img.style.transition = 'transform 0.7s cubic-bezier(0.19, 1, 0.22, 1)';
        }
      });
      
      card.addEventListener('mouseleave', function() {
        // Add a slight delay on mouse leave for smoother effect
        setTimeout(() => {
          if (img) {
            img.style.transition = 'transform 0.9s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
          }
        }, 100);
      });
    });
  </script>
  
  <style>
    @keyframes ripple {
      0% {
        opacity: 0.5;
        transform: scale(0.8);
      }
      100% {
        opacity: 0;
        transform: scale(1.5);
      }
    }
  </style>
   <style>
    .benefits-section {
      overflow-x: hidden;
    }
    .hidden-initially {
      opacity: 0;
    }
    .card-icon {
      transition: transform 0.3s ease;
    }
    .card:hover .card-icon {
      transform: scale(1.15);
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
  </style>
</head>
<style>
    .hidden-initially {
      opacity: 0;
      visibility: hidden;
    }
  </style>
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
    }
    
    .card {
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      backface-visibility: hidden;
    }
    
    .card:hover {
      transform: translateY(-15px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    
    .card-icon {
      transition: all 0.5s ease;
      overflow: hidden;
      position: relative;
    }
    
    .card:hover .card-icon {
      transform: scale(1.1) rotate(3deg);
    }
    
    .card-icon::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      opacity: 0;
      transition: opacity 0.5s ease;
    }
    
    .card:hover .card-icon::after {
      opacity: 0.2;
      animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
      0% { opacity: 0.2; }
      50% { opacity: 0.6; }
      100% { opacity: 0.2; }
    }
    
    .title-animation {
      display: inline-block;
      animation: titleFloat 3s ease-in-out infinite;
    }
    
    @keyframes titleFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    @keyframes ripple {
      0% {
        opacity: 0.5;
        transform: scale(0.8);
      }
      100% {
        opacity: 0;
        transform: scale(1.5);
      }
    }
  </style>
</head>
<body class="bg-gray-50">
  <!--=============== About ===============-->
  <section class="py-16 md:py-24 relative overflow-hidden" id="about">
    <!-- Background decorative elements -->
    <div class="absolute top-0 left-0 w-32 h-32 bg-indigo-100 rounded-full opacity-50 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-yellow-200 rounded-full opacity-40 translate-x-1/4 translate-y-1/4"></div>
    
    <div class="container mx-auto px-6 md:px-8 lg:px-12 flex flex-col lg:flex-row items-center justify-between">
      <!-- Left Column (Image) -->
      <div class="w-full lg:w-1/2 mb-10 lg:mb-0" data-aos="fade-right" data-aos-duration="1000">
        <img src="./images/delivery-guy-2.svg" alt="Delivery Person" class="max-w-full lg:max-w-md mx-auto" />
      </div>
      
      <!-- Right Column (Content) -->
      <div class="w-full lg:w-1/2 lg:pl-12">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-700 mb-5" data-aos="fade-up" data-aos-duration="800">
          <span class="title-animation block">Take a look at the</span>
          <span class="title-animation block" style="animation-delay: 0.2s">benefits we offer</span>
          <span class="title-animation block" style="animation-delay: 0.4s">for you</span>
        </h2>
        <p class="text-gray-600 mb-10 max-w-lg" data-aos="fade-up" data-aos-delay="200" data-aos-duration="800">
          Good service means a friendly, welcoming service. A restaurant owner should not merely strive to avoid bad service,
        </p>
<!-- Cards Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 max-w-auto py-auto">
  <!-- Card 1: Free Home Delivery -->
  <div class="card bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden" data-aos="fade-up" data-aos-delay="300" data-aos-duration="800">
    <div class="card-icon bg-red-500 p-4 rounded-full mb-4 mx-auto mt-6" style="width: 64px; height: 64px; display: flex; align-items: center; justify-content: center;">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
        <rect x="1" y="3" width="15" height="13"></rect>
        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
        <circle cx="5.5" cy="18.5" r="2.5"></circle>
        <circle cx="18.5" cy="18.5" r="2.5"></circle>
      </svg>
    </div>
    <div class="p-4 text-center">
      <h4 class="text-lg font-semibold mb-1">Free Home Delivery</h4>
      <span class="text-gray-500 text-sm">For all orders over Rs.5000</span>
    </div>
  </div>
  
  <!-- Card 2: Return & Refund -->
  <div class="card bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden" data-aos="fade-up" data-aos-delay="400" data-aos-duration="800">
    <div class="card-icon bg-indigo-500 p-4 rounded-full mb-4 mx-auto mt-6" style="width: 64px; height: 64px; display: flex; align-items: center; justify-content: center;">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
      </svg>
    </div>
    <div class="p-4 text-center">
      <h4 class="text-lg font-semibold mb-1">Return & Refund</h4>
      <span class="text-gray-500 text-sm">Money Back Guarantee</span>
    </div>
  </div>
  
  <!-- Card 3: Secure Payment -->
  <div class="card bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden" data-aos="fade-up" data-aos-delay="500" data-aos-duration="800">
    <div class="card-icon bg-yellow-400 p-4 rounded-full mb-4 mx-auto mt-6" style="width: 64px; height: 64px; display: flex; align-items: center; justify-content: center;">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        <line x1="12" y1="15" x2="12" y2="17"></line>
      </svg>
    </div>
    <div class="p-4 text-center">
      <h4 class="text-lg font-semibold mb-1">Secure Payment</h4>
      <span class="text-gray-500 text-sm">100% Secure Payment</span>
    </div>
  </div>
  
  <!-- Card 4: Quality Support -->
  <div class="card bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden" data-aos="fade-up" data-aos-delay="600" data-aos-duration="800">
    <div class="card-icon bg-green-400 p-4 rounded-full mb-4 mx-auto mt-6" style="width: 64px; height: 64px; display: flex; align-items: center; justify-content: center;">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
      </svg>
    </div>
    <div class="p-4 text-center">
      <h4 class="text-lg font-semibold mb-1">Quality Support</h4>
      <span class="text-gray-500 text-sm">Always Online 24/7</span>
    </div>
  </div>
</div>
  </section>

  <script>
    // Initialize AOS
    AOS.init({
      duration: 800,
      once: false,
      mirror: true
    });
    
    // Add smooth hover interactions for icons
    document.querySelectorAll('.card').forEach(card => {
      const icon = card.querySelector('.card-icon');
      
      card.addEventListener('mouseenter', function() {
        // Smoother icon scale effect
        if (icon) {
          icon.style.transition = 'transform 0.7s cubic-bezier(0.19, 1, 0.22, 1)';
        }
      });
      
      card.addEventListener('mouseleave', function() {
        // Add a slight delay on mouse leave for smoother effect
        setTimeout(() => {
          if (icon) {
            icon.style.transition = 'transform 0.9s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
          }
        }, 100);
      });
    });
  </script>
    
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
    }
    
    .testimonial-card {
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      backface-visibility: hidden;
    }
    
    .testimonial-card:hover, .testimonial-card.active {
      transform: translateY(-10px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .testimonial-card .image {
      transition: all 0.5s ease;
      overflow: hidden;
      position: relative;
    }
    
    .testimonial-card:hover .image::after,
    .testimonial-card.active .image::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      animation: pulse 1.5s infinite;
    }
    
    .testimonial-card:hover img,
    .testimonial-card.active img {
      transform: scale(1.05);
    }
    
    @keyframes pulse {
      0% { opacity: 0.2; }
      50% { opacity: 0.4; }
      100% { opacity: 0.2; }
    }
    
    .testimonial-content-wrapper {
      position: relative;
      min-height: 300px;
    }
    
    .testimonial-content {
  display: none;
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.4s ease, transform 0.4s ease;
}

.testimonial-content.active {
  display: block;
  opacity: 1;
  transform: translateY(0);
}
    
    .title-animation {
      display: inline-block;
      animation: titleFloat 3s ease-in-out infinite;
    }
    
    @keyframes titleFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-6px); }
    }
    
    .star-rating {
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .star-rating i {
      transition: all 0.3s ease;
    }
    
    .testimonial-content.active .star-rating i {
      animation: starPop 0.5s ease-out;
      animation-fill-mode: backwards;
    }
    
    @keyframes starPop {
      0% { transform: scale(0); opacity: 0; }
      50% { transform: scale(1.2); }
      100% { transform: scale(1); opacity: 1; }
    }
  </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100">
<!--=============== Testimonials ===============-->
<section id="testimonials" class="py-16 px-4 md:px-8 lg:px-16 relative overflow-hidden">
  <!-- Background decorative elements -->
  <div class="absolute top-0 left-0 w-32 h-32 bg-indigo-100 rounded-full opacity-50 -translate-x-1/2 -translate-y-1/2"></div>
  <div class="absolute bottom-0 right-0 w-64 h-64 bg-yellow-200 rounded-full opacity-40 translate-x-1/4 translate-y-1/4"></div>
  
  <div class="container mx-auto flex flex-col lg:flex-row gap-12">
    
    <!-- Left Column - Profile Cards -->
    <div class="lg:w-1/3 space-y-6" data-aos="fade-right">
      <?php
      // Fetch ratings from database
      $ratings_query = $conn->query("SELECT * FROM ratings ORDER BY created_at DESC LIMIT 4");
      $ratings = [];
      $first_rating = true;
      
      while ($rating = $ratings_query->fetch_assoc()) {
        $ratings[] = $rating;
        $rating_id = 'rating_' . $rating['id'];
        $active_class = $first_rating ? 'active' : '';
      ?>
        <div class="testimonial-card rounded-xl bg-white shadow-md p-4 flex items-center gap-4 cursor-pointer <?= $active_class ?>" data-testimonial="<?= $rating_id ?>">
          <div class="image w-16 h-16 rounded-full overflow-hidden flex-shrink-0">
            <?php 
            // Check if user has profile image
            $user_query = $conn->query("SELECT profile_image FROM user_accounts WHERE id = " . $rating['user_id']);
            $user_data = $user_query->fetch_assoc();
            $profile_image = !empty($user_data['profile_image']) ? $user_data['profile_image'] : './images/default-profile.jpg';
            ?>
            <img src="<?= $profile_image ?>" alt="<?= htmlspecialchars($rating['user_name']) ?>" class="w-full h-full object-cover" />
          </div>
          <div>
            <h4 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($rating['user_name']) ?></h4>
            <span class="text-sm text-gray-600">Customer</span>
          </div>
        </div>
      <?php
        $first_rating = false;
      }
      
      // Display placeholder cards if less than 4 ratings
      if (count($ratings) < 4) {
        for ($i = count($ratings); $i < 4; $i++) {
      ?>
        <div class="testimonial-card rounded-xl bg-white shadow-md p-4 flex items-center gap-4 opacity-50">
          <div class="image w-16 h-16 rounded-full overflow-hidden flex-shrink-0 bg-gray-200 flex items-center justify-center">
            <i class="fas fa-user text-gray-400 text-2xl"></i>
          </div>
          <div>
            <h4 class="text-lg font-semibold text-gray-800">Be the next reviewer</h4>
            <span class="text-sm text-gray-600">Submit your feedback</span>
          </div>
        </div>
      <?php
        }
      }
      ?>
    </div>
    
    <!-- Right Column - Testimonial Content -->
    <div class="lg:w-2/3 relative" data-aos="fade-left">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8">
        <span class="title-animation block">What our Customers</span>
        <span class="title-animation block text-red-500" style="animation-delay: 0.2s">are saying</span>
      </h2>
      
      <div class="testimonial-content-wrapper">
        <?php
        // Reset first rating flag
        $first_rating = true;
        
        foreach ($ratings as $rating) {
          $rating_id = 'rating_' . $rating['id'];
          $active_class = $first_rating ? 'active' : '';
          
          // Format date
          $rating_date = new DateTime($rating['created_at']);
          $formatted_date = $rating_date->format('M d, Y');
          
          // Get stars based on rating
          $full_stars = floor($rating['rating']);
          $half_star = ($rating['rating'] - $full_stars) >= 0.5;
          $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
        ?>
          <!-- <?= htmlspecialchars($rating['user_name']) ?>'s Testimonial -->
          <div class="testimonial-content <?= $active_class ?>" id="<?= $rating_id ?>-content">
            <div class="border-l-4 border-red-500 pl-6 mb-6">
              <h4 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($rating['user_name']) ?></h4>
              <span class="text-sm text-gray-600">Customer • <?= $formatted_date ?></span>
            </div>
            
            <div class="flex justify-end mb-6">
              <div class="star-rating flex text-yellow-400 text-xl">
                <?php
                // Output full stars
                for ($i = 0; $i < $full_stars; $i++) {
                  echo '<i class="bx bxs-star" style="animation-delay: ' . (0.1 * ($i + 1)) . 's"></i>';
                }
                
                // Output half star if needed
                if ($half_star) {
                  echo '<i class="bx bxs-star-half" style="animation-delay: ' . (0.1 * ($full_stars + 1)) . 's"></i>';
                }
                
                // Output empty stars
                for ($i = 0; $i < $empty_stars; $i++) {
                  echo '<i class="bx bx-star" style="animation-delay: ' . (0.1 * ($full_stars + ($half_star ? 1 : 0) + $i + 1)) . 's"></i>';
                }
                ?>
              </div>
            </div>
            
            <p class="text-gray-600 text-lg leading-relaxed">
              "<?= htmlspecialchars($rating['comment'] ?: 'Great experience at Nishan Bakers!') ?>"
            </p>
          </div>
        <?php
          $first_rating = false;
        }
        
        // Display a placeholder message if no ratings
        if (empty($ratings)) {
        ?>
          <div class="testimonial-content active" id="no-ratings-content">
            <div class="border-l-4 border-red-500 pl-6 mb-6">
              <h4 class="text-xl font-semibold text-gray-800">No Reviews Yet</h4>
              <span class="text-sm text-gray-600">Be the first to leave a review</span>
            </div>
            
            <div class="flex justify-end mb-6">
              <div class="star-rating flex text-gray-300 text-xl">
                <i class="bx bx-star" style="animation-delay: 0.1s"></i>
                <i class="bx bx-star" style="animation-delay: 0.2s"></i>
                <i class="bx bx-star" style="animation-delay: 0.3s"></i>
                <i class="bx bx-star" style="animation-delay: 0.4s"></i>
                <i class="bx bx-star" style="animation-delay: 0.5s"></i>
              </div>
            </div>
            
            <p class="text-gray-600 text-lg leading-relaxed">
              "We're waiting for our first customer review. If you've enjoyed our products and services, please consider leaving a rating!"
            </p>
          </div>
        <?php
        }
        ?>
      </div>
    </div>
  </div>
</section>

<!-- AOS for animations -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Initialize AOS
    AOS.init({
      duration: 800,
      once: false,
      mirror: true
    });
    
  // Get all testimonial cards and content elements
const testimonialCards = document.querySelectorAll('.testimonial-card');
const testimonialContents = document.querySelectorAll('.testimonial-content');

// Add click event listeners to each card (only to those with data-testimonial)
testimonialCards.forEach(card => {
  const testimonialId = card.getAttribute('data-testimonial');
  if (!testimonialId) return; // Skip placeholder cards
  
  card.addEventListener('click', () => {
    // First, handle card selection
    testimonialCards.forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    
    // Hide all testimonial contents first
    testimonialContents.forEach(content => {
      content.classList.remove('active');
      content.style.display = 'none'; // Add this line to hide content
    });
    
    // Select the content to display
    const selectedContent = document.getElementById(`${testimonialId}-content`);
    
    // Make the selected content visible before adding the active class
    if (selectedContent) {
      selectedContent.style.display = 'block';
      
      // Small delay before showing the new content for smoother transition
      setTimeout(() => {
        selectedContent.classList.add('active');
        
        // Reset and restart star animations
        const stars = selectedContent.querySelectorAll('.star-rating i');
        stars.forEach((star, index) => {
          star.style.animationName = 'none';
          setTimeout(() => {
            star.style.animationName = 'starPop';
          }, 10);
        });
      }, 300);
    }
  });
});
    // Add hover effects for images
    testimonialCards.forEach(card => {
      const img = card.querySelector('img');
      
      card.addEventListener('mouseenter', function() {
        if (img) {
          img.style.transition = 'transform 0.7s cubic-bezier(0.19, 1, 0.22, 1)';
        }
      });
      
      card.addEventListener('mouseleave', function() {
        setTimeout(() => {
          if (img) {
            img.style.transition = 'transform 0.9s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
          }
        }, 100);
      });
    });
    
    // Add entrance animation for cards
    function animateEntrance() {
      testimonialCards.forEach((card, index) => {
        setTimeout(() => {
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, 100 * index);
      });
      
      setTimeout(() => {
        const activeContent = document.querySelector('.testimonial-content.active');
        if (activeContent) {
          activeContent.style.opacity = '1';
          activeContent.style.transform = 'translateY(0)';
          
          // Animate stars with delay
          const stars = activeContent.querySelectorAll('.star-rating i');
          stars.forEach((star, index) => {
            star.style.animationDelay = `${0.1 * (index + 1)}s`;
          });
        }
      }, 400);
    }
    
 // Initial setup
testimonialCards.forEach(card => {
  card.style.opacity = '0';
  card.style.transform = 'translateY(20px)';
});

// Hide all testimonial contents except the active one
testimonialContents.forEach(content => {
  if (!content.classList.contains('active')) {
    content.style.display = 'none';
  }
});

// Animate the entrance
animateEntrance();
  });
</script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9f9f9;
      overflow-x: hidden;
    }
    
    /* Scale-up animation for the sale badge */
    .scale-up-anim {
      animation: scaleUp 3s ease-in-out infinite alternate;
    }
    
    @keyframes scaleUp {
      0% { transform: scale(1); }
      100% { transform: scale(1.05); }
    }
    
    /* Smooth landing animation for elements */
    .fade-in {
      opacity: 0;
      transform: translateY(30px);
      transition: opacity 1s cubic-bezier(0.175, 0.885, 0.32, 1.275), 
                  transform 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      will-change: opacity, transform;
    }
    
    .fade-in.active {
      opacity: 1;
      transform: translateY(0);
    }
    
    /* Staggered animation delays */
    .delay-100 { transition-delay: 0.1s; }
    .delay-200 { transition-delay: 0.2s; }
    .delay-300 { transition-delay: 0.3s; }
    .delay-400 { transition-delay: 0.4s; }
    .delay-500 { transition-delay: 0.5s; }
    
    /* List item animations with smoother transition */
    .list-item {
      opacity: 0;
      transform: translateX(40px);
      transition: opacity 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275),
                  transform 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      backface-visibility: hidden;
    }
    
    .list-item.active {
      opacity: 1;
      transform: translateX(0);
    }
    
    /* Smooth hover effects for the button */
    button {
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      backface-visibility: hidden;
      position: relative;
      overflow: hidden;
    }
    
    button:hover {
      transform: translateY(-5px) scale(1.05);
      box-shadow: 0 8px 20px rgba(255, 140, 0, 0.3);
    }
    
    /* Ripple effect on button click */
    button::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 150px;
      height: 150px;
      background: rgba(255, 255, 255, 0.4);
      border-radius: 50%;
      transform: translate(-50%, -50%) scale(0);
      opacity: 0;
      transition: transform 0.5s, opacity 0.5s;
    }
    
    button:active::after {
      transform: translate(-50%, -50%) scale(1);
      opacity: 1;
      animation: ripple 0.6s linear;
    }
    
    @keyframes ripple {
      0% {
        opacity: 0.5;
        transform: translate(-50%, -50%) scale(0);
      }
      100% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(2);
      }
    }
    
    /* Title animation */
    .title-highlight {
      display: inline-block;
      position: relative;
      animation: titleFloat 4s ease-in-out infinite;
    }
    
    @keyframes titleFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-8px); }
    }
    
    /* Main image hover effect */
    .main-image {
      transition: all 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      backface-visibility: hidden;
    }
    
    .main-image:hover {
      transform: scale(1.03) translateY(-10px);
      filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.15));
    }
    
    /* On-scroll animations */
    .scroll-fade-in {
      opacity: 0;
      transform: translateY(50px);
      transition: opacity 1s cubic-bezier(0.175, 0.885, 0.32, 1.275),
                  transform 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .scroll-fade-in.visible {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>
<br><br>
<body>
  <section class="py-16 md:py-24 px-4 md:px-0 bg-white">
    <div class="container mx-auto max-w-6xl flex flex-col md:flex-row items-center justify-between">
      
      <!-- Left side - Banner and images -->
      <div class="relative w-full md:w-1/2 mb-10 md:mb-0 flex justify-center items-center fade-in">
        <!-- "Get Up To 50% Off Now" badge -->
        <div class="absolute z-10 top-0 left-1/4 transform -translate-x-1/2 -translate-y-1/4">
          <img src="./images/sale-shape-red.png" width="216" height="226" alt="get up to 50% off now" class="scale-up-anim">
         <br>
        </div>
        
        <!-- Main cake image -->
        <img src="./images/cakes.png" width="509" height="459" loading="lazy" alt="food" class="w-full max-w-md md:max-w-lg main-image">
      </div>
      
      <!-- Right side - Content -->
      <div class="w-full md:w-1/2 md:pl-12 fade-in delay-100">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-3 fade-in delay-200">
          Pastries and Best <br>
          Cakes <span class="text-yellow-500 title-highlight">in Town!</span>
        </h2>
        
        <p class="text-gray-600 my-6 leading-relaxed fade-in delay-300">
          The Nishan Bakers not only delighted local customers but also served many northern Chinese who migrated south from Kaifeng during the Jurchen invasion of the 1120s. These skilled bakers introduced traditional northern baking techniques and unique flavors, enriching the local pastry culture. It is also known that many of these bakeries were family-owned businesses, passing down their cherished recipes through generations.
        </p>
        
        <ul class="my-8 space-y-4">
          <li class="flex items-center list-item" style="transition-delay: 0.5s">
            <span class="text-orange-500 mr-3 text-xl">●</span>
            <span class="text-gray-700">Delicious & Healthy Foods</span>
          </li>
          
          <li class="flex items-center list-item" style="transition-delay: 0.7s">
            <span class="text-orange-500 mr-3 text-xl">●</span>
            <span class="text-gray-700">Specific Beverages</span>
          </li>
          
          <li class="flex items-center list-item" style="transition-delay: 0.9s">
            <span class="text-orange-500 mr-3 text-xl">●</span>
            <span class="text-gray-700">Fastest Food Home Delivery</span>
          </li>
        </ul>
        
        <button class="bg-orange-400 hover:bg-red-500 text-white py-3 px-8 rounded-xl font-medium transition-all duration-300 fade-in delay-500">
          Order Now
        </button>
      </div>
      
    </div>
  </section>
  <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.12.0/cdn.min.js" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .offer-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .offer-card:hover {
            transform: translateY(-5px);
        }
        
        .buy-now-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .buy-now-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease-in-out;
        }
        
        .buy-now-btn:hover::before {
            left: 100%;
        }
        .carousel-arrow {
        transition: all 0.2s ease;
    }
    
    .carousel-arrow:hover {
        transform: scale(1.1);
        background-color: rgba(0, 0, 0, 0.7);
    }
        .days-left-badge {
            transition: all 0.3s ease;
        }
        
        .offer-card:hover .days-left-badge {
            transform: scale(1.05);
        }

    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="container mx-auto py-16 px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-center mb-2 text-gray-900">Current Special Offers</h2>
            <div class="w-24 h-1 bg-red-500 mx-auto rounded-full mb-4"></div>
        </div>
        
        <div class="max-w-6xl mx-auto" x-data="carouselData()">
            <?php
            // Database connection
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "nishan_bakery";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Get current date
            $currentDate = date('Y-m-d');

            // Fetch active offers
            $sql = "SELECT * FROM offers WHERE '$currentDate' BETWEEN start_date AND end_date ORDER BY created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Store offers in an array for JavaScript
                echo '<script>';
                echo 'function carouselData() {';
                echo '    return {';
                echo '        offers: [';
                
                $offerCount = 0;
                while($row = $result->fetch_assoc()) {
                    $daysLeft = (strtotime($row['end_date']) - strtotime($currentDate)) / (60 * 60 * 24);
                    $offerCount++;
                    
                    echo '{';
                    echo '    id: ' . $row['id'] . ',';
                    echo '    title: "' . addslashes($row['title']) . '",';
                    echo '    description: "' . addslashes($row['description']) . '",';
                    echo '    image: "' . addslashes($row['image']) . '",';
                    echo '    endDate: "' . date('M d, Y', strtotime($row['end_date'])) . '",';
                    echo '    daysLeft: ' . $daysLeft . ',';
                    echo '},';
                }
                
                echo '        ],';
                echo '        currentPage: 0,';
                echo '        totalPages: Math.ceil(' . $offerCount . ' / 3),';
                echo '        get visibleOffers() {';
                echo '            return this.offers.slice(this.currentPage * 3, (this.currentPage * 3) + 3);';
                echo '        },';
                echo '        nextPage() {';
                echo '            if (this.currentPage < this.totalPages - 1) {';
                echo '                this.currentPage++;';
                echo '            }';
                echo '        },';
                echo '        prevPage() {';
                echo '            if (this.currentPage > 0) {';
                echo '                this.currentPage--;';
                echo '            }';
                echo '        }';
                echo '    };';
                echo '}';
                echo '</script>';
                
                ?>
                <div class="relative">
                    <!-- Left Arrow -->
                    <button 
    @click="prevPage()" 
    class="absolute left-0 top-1/2 z-10 bg-black bg-opacity-50 text-white p-3 rounded-full shadow-lg"
    :class="{'opacity-50 cursor-not-allowed': currentPage === 0, 'opacity-100 cursor-pointer': currentPage > 0}"
    :disabled="currentPage === 0"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
</button>

                    
                    <!-- Right Arrow -->
                    <button 
    @click="nextPage()" 
    class="absolute right-0 top-1/2 z-10 bg-black bg-opacity-50 text-white p-3 rounded-full shadow-lg"
    :class="{'opacity-50 cursor-not-allowed': currentPage === totalPages - 1, 'opacity-100 cursor-pointer': currentPage < totalPages - 1}"
    :disabled="currentPage === totalPages - 1"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
</button>
                    
                    <!-- Carousel Container -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 transition-all duration-500">
                        <template x-for="offer in visibleOffers" :key="offer.id">
                            <div class="offer-card bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                                <div class="h-56 overflow-hidden" x-show="offer.image">
                                    <img 
                                        :src="offer.image" 
                                        :alt="offer.title"
                                        class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"
                                    >
                                </div>
                                
                                <div class="p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="text-xl font-bold text-gray-800" x-text="offer.title"></h3>
                                        <span class="days-left-badge text-xs font-medium px-3 py-1 rounded-full shadow-sm"
                                            :class="offer.daysLeft <= 1 ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'"
                                            x-text="offer.daysLeft <= 1 ? 'Ends today!' : offer.daysLeft + ' days left'">
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-600 mb-6" x-text="offer.description"></p>
                                    
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm text-gray-500 flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span x-text="'Valid until: ' + offer.endDate"></span>
                                        </p>
                                        <button 
                                            class="buy-now-btn bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg flex items-center gap-2 shadow-md hover:shadow-lg"
                                            :data-offer-id="offer.id"
                                            onclick="window.location.href='shop.php'"
                                        >
                                            <span>Buy Now</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Pagination Dots -->
                    <div class="flex justify-center mt-8">
                        <template x-for="(_, index) in Array.from({ length: totalPages })" :key="index">
                            <button 
                                @click="currentPage = index" 
                                :class="{'bg-red-500': currentPage === index, 'bg-gray-300': currentPage !== index}"
                                class="w-3 h-3 rounded-full mx-1 transition-all duration-300"
                            ></button>
                        </template>
                    </div>
                </div>
                <?php
            } else {
                echo '<div class="text-center py-16 bg-white rounded-lg shadow-md">
                        <svg class="mx-auto h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-4 text-xl font-medium text-gray-900">No active offers</h3>
                        <p class="mt-2 text-sm text-gray-500">Check back soon for new special offers!</p>
                        <button class="mt-6 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                            Browse Products
                        </button>
                      </div>';
            }

            $conn->close();
            ?>
        </div>
    </div>
</body>
</html>
  <script>
    // Initial landing animations
    document.addEventListener('DOMContentLoaded', () => {
      // Animate elements on load with staggered timing
      setTimeout(() => {
        const fadeElements = document.querySelectorAll('.fade-in');
        fadeElements.forEach((el, index) => {
          setTimeout(() => {
            el.classList.add('active');
          }, 100 * index);
        });
        
        const listItems = document.querySelectorAll('.list-item');
        listItems.forEach((item, index) => {
          const baseDelay = parseInt(item.style.transitionDelay) || 0;
          setTimeout(() => {
            item.classList.add('active');
          }, baseDelay + (150 * index));
        });
      }, 300);
      
      // Setup scroll animations
      const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            scrollObserver.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
      });
      
      document.querySelectorAll('.scroll-fade-in').forEach(el => {
        scrollObserver.observe(el);
      });
    });
  </script>
</body>
<?php
include "./components/footer.php";


?>
</html>