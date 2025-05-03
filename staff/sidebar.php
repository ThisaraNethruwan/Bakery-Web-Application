<!-- sidebar.php -->
<div class="bg-primary text-white w-64 fixed h-screen overflow-y-auto z-10">
    <div class="p-4">
        <h2 class="text-2xl font-bold">Staff Dashboard</h2>
    </div>
    <nav class="mt-4">
        <a href="?page=dashboard" class="block px-4 py-2 hover:bg-white hover:text-primary <?php echo $page === 'dashboard' ? 'bg-white text-primary' : ''; ?>">
            Dashboard
        </a>
        <a href="?page=products" class="block px-4 py-2 hover:bg-white hover:text-primary <?php echo $page === 'products' ? 'bg-white text-primary' : ''; ?>">
            Products
        </a>
        <a href="?page=orders" class="block px-4 py-2 hover:bg-white hover:text-primary <?php echo $page === 'orders' ? 'bg-white text-primary' : ''; ?>">
            Orders
        </a>
        <a href="?page=offers" class="block px-4 py-2 hover:bg-white hover:text-primary <?php echo $page === 'offers' ? 'bg-white text-primary' : ''; ?>">
            Offers & Discounts
        </a>
        <a href="?page=customers" class="block px-4 py-2 hover:bg-white hover:text-primary <?php echo $page === 'customers' ? 'bg-white text-primary' : ''; ?>">
            Customers
        </a>
        <a href="?page=messages" class="block px-4 py-2 hover:bg-white hover:text-primary <?php echo $page === 'messages' ? 'bg-white text-primary' : ''; ?>">
            Messages
        </a>
        <a href="?page=blogs" class="block px-4 py-2 hover:bg-white hover:text-primary <?php echo $page === 'blogs' ? 'bg-white text-primary' : ''; ?>">
            Blogs
        </a>
        <a href="logout.php" class="block px-4 py-2 hover:bg-white hover:text-primary mt-4">
            Logout
        </a>
    </nav>
</div>