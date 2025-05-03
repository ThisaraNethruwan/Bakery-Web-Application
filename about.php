<?php include "./components/navbar.html"; ?>
<?php include "./components/load.html"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Nishan Bakery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/2.0.7/countUp.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FFA726',
                        secondary: '#FFE0B2',
                        accent: '#FF5722',
                        bakery: {
                            red: '#E74C3C',
                            brown: '#8D6E63',
                            cream: '#FFF8E1',
                            gold: '#FFCA28'
                        }
                    },
                    fontFamily: {
                       
                        'poppins': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg,rgb(255, 255, 255) 0%,rgb(255, 255, 255) 100%);
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        .stat-card {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
        }
        
        .value-icon {
            transition: all 0.3s ease;
        }
        
        .value-card:hover .value-icon {
            transform: scale(1.1);
        }
        
        .team-card {
            overflow: hidden;
        }
        
        .team-card img {
            transition: all 0.5s ease;
        }
        
        .team-card:hover img {
            transform: scale(1.05);
        }
    </style>
</head>
<body >
    <!-- Hero Section -->
    <section class="relative min-h-screen overflow-hidden hero-gradient">
        <div class="absolute top-0 right-0 w-64 h-64 rounded-full -mr-32 -mt-16"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 rounded-full -ml-48 -mb-24"></div>
        
        <div class="container mx-auto px-4 pt-32 pb-20 relative">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-right" data-aos-duration="1200">
                    <h2 class="text-5xl font-bold text-bakery-red mb-4 leading-tight">Crafting Sweet</h2>
                    <h3 class="text-5xl font-bold text-gray-800 mb-8 leading-tight">Moments Since 2005</h3>
                    <p class="text-lg text-gray-600 mb-10 leading-relaxed">At Nishan Bakery, we believe in creating more than just baked goods. We craft memories, celebrate traditions, and bring joy to every occasion with our artisanal creations made with love and precision.</p>
                    
                    <div class="flex flex-wrap gap-4">
                        <button class="bg-bakery-red text-white px-8 py-3 rounded-full hover:bg-red-700 transition shadow-lg flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd" />
                            </svg>
                            Our Story
                        </button>
                        <a href= "shop.php"> 
                        <button class="border-2 border-bakery-red text-bakery-red px-8 py-3 rounded-full hover:bg-bakery-red hover:text-white transition flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            
                            View Products
                        </button>
                        </a>
                    </div>
                </div>
                
                <div class="relative" data-aos="fade-left" data-aos-duration="1200" data-aos-delay="200">
                    <div class="absolute top-1/2 left-1/2 w-80 h-80 bg-bakery-gold/20 rounded-full transform -translate-x-1/2 -translate-y-1/2"></div>
                    <img src="./images/sweet.png" alt="Fresh Pastries" class="rounded-2xl floating relative z-10">
                    <div class="absolute -bottom-6 -right-6 bg-white p-4 rounded-xl z-20">
                     
                        </div>
                    </div>
                </div>
            </div>
        </div>
   

    <!-- Values Section -->
    <section class="py-24">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h3 class="text-gray-500 text-lg font-medium tracking-wider mb-4">OUR VALUES</h3>
                <h2 class="text-4xl font-bold text-gray-800 mb-6">What Makes Us Special</h2>
                <div class="w-24 h-1 bg-bakery-red mx-auto rounded-full"></div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-8 bg-white rounded-xl shadow-lg hover:shadow-xl transition value-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-6 value-icon">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Traditional Recipes</h3>
                    <p class="text-gray-600 leading-relaxed">We preserve the authenticity of traditional recipes passed down through generations while adding our unique modern twist for a perfect blend of nostalgia and innovation.</p>
                </div>

                <div class="p-8 bg-white rounded-xl shadow-lg hover:shadow-xl transition value-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6 value-icon">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Quality Ingredients</h3>
                    <p class="text-gray-600 leading-relaxed">We source the finest ingredients from trusted local and international suppliers to ensure every bite is pure perfection. Quality is never compromised.</p>
                </div>

                <div class="p-8 bg-white rounded-xl shadow-lg hover:shadow-xl transition value-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 value-icon">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Customer First</h3>
                    <p class="text-gray-600 leading-relaxed">Your satisfaction is our priority. We go above and beyond to exceed expectations and create memorable experiences with every visit to our bakery.</p>
                </div>
            </div>
        </div>
    </section>


   <!-- Team Section -->

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Counter animation
        const counterOptions = {
            duration: 2.5,
            useEasing: true,
            useGrouping: true,
            decimal: '.'
        };

        const counters = [
            { id: 'yearsCounter', end: 50 },
            { id: 'productsCounter', end: 135 },
            { id: 'customersCounter', end: 15000 },
            { id: 'locationsCounter', end: 12 }
        ];

        // Function to start counters
        function startCounters() {
            counters.forEach(counter => {
                const countUpInstance = new CountUp(counter.id, 0, counter.end, 0, 2.5, {
                    useEasing: true,
                    useGrouping: true,
                    separator: ',',
                    decimal: '.'
                });
                
                if (!countUpInstance.error) {
                    countUpInstance.start();
                } else {
                    console.error(countUpInstance.error);
                    document.getElementById(counter.id).textContent = counter.end.toLocaleString();
                }
            });
        }

        // Use Intersection Observer to trigger counters when stats section is in view
        document.addEventListener('DOMContentLoaded', function() {
            const statsSection = document.getElementById('stats-section');
            
            if (statsSection) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            startCounters();
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.2 });
                
                observer.observe(statsSection);
            }
        });
    </script>
</body>
<?php include 'components/footer.php'; ?>
</html>