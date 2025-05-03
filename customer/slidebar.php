
<!-- Sidebar -->
 <div class="sidebar">
        <div class="sidebar-logo">
            <h2>Nishan Bakery</h2>
        </div>
        <br>
        <div class="sidebar-user">
            <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'uploads/default-profile.png'); ?>" alt="Profile">
            <div class="sidebar-user-info">
                <h3><?php echo htmlspecialchars($userName); ?></h3>
                <p>Customer</p>
            </div>
        </div>
        
        <a href="dashboard.php" class="active">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>
        
        <a href="orders.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            Your Orders
        </a>
        
        <a href="wishlist.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            Wishlist
        </a>
        
        <a href="points.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Loyalty Points
        </a>
        
        <a href="profile.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Your Profile
        </a>
        
        <div class="sidebar-footer">
            <a href="logout.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </a>
        </div>
    </div>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f5f5;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .sidebar {
            width: 290px;
            background-color: #c41c1c;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
        }

        .sidebar-logo {
            margin-bottom: 25px;
            text-align: center;
        }

        .sidebar-logo h2 {
            font-size: 24px;
            font-weight: 700;
            margin-top: 10px;
            letter-spacing: 1px;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            padding: 15px 10px;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            margin-bottom: 25px;
        }

        .sidebar-user img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }

        .sidebar-user-info {
            margin-left: 12px;
        }

        .sidebar-user-info h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .sidebar-user-info p {
            font-size: 12px;
            margin: 0;
            opacity: 0.8;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar a svg {
            margin-right: 10px;
            width: 20px;
            height: 20px;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        </style>