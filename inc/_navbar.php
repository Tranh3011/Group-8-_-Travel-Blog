<?php
if (session_status() == PHP_SESSION_NONE) session_start();
$user_type = $_SESSION['user_type'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Blog Navbar</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(90deg, #0a1f44, #142850); 
            padding: 10px 20px;
            color: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); 
        }

        .navbar-logo {
            display: flex;
            align-items: center;
        }

        .navbar-logo img {
            height: 60px; 
            width: auto; 
            margin-right: 10px;
        }

        .navbar-logo h1 {
            margin: 0;
            font-size: 20px;
            color: #fff; 
        }

        .navbar-links {
            display: flex;
            gap: 20px;
        }

        .navbar-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .navbar-links a:hover {
            color: #ffcc00; 
            transform: scale(1.1); 
        }

        @media (max-width: 768px) {
            .navbar-links {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-logo">
            <img src="../uploads/logo.jpg" alt="Logo">
            <h1>Let's Travel</h1>
        </div>
        <div class="navbar-links">
            <?php if ($user_type === 'admin'): ?>
                <a href="/PHP/TravelBlog/admin/index_homeAdmin.php">Home</a>
                <a href="/PHP/TravelBlog/admin/user/index.php">Manage User</a>
                <a href="/PHP/TravelBlog/admin/post/index.php">Manage Post</a>
                <a href="/PHP/TravelBlog/destination/Destination.php">Manage Destination</a>
                <a href="/PHP/TravelBlog/admin/category/indexcategory.php">Manage Category</a>
                <a href="/PHP/TravelBlog/admin/comment/index.php">Manage Comment</a>
                <a href="/PHP/TravelBlog/auth/logout.php">Logout</a>
            <?php elseif ($user_type === 'customer'): ?>
                <a href="/PHP/TravelBlog/customer/Home_user/index_homepage.php">Home</a>
                <a href="/PHP/TravelBlog/customer/Home_user/profile.php">My Profile</a>
                <a href="/PHP/TravelBlog/customer/post/index.php">My Post</a>
                <a href="/PHP/TravelBlog/customer/Destination/index.php">Booking Tour</a>
                <a href="/PHP/TravelBlog/auth/logout.php">Logout</a>
            <?php else: ?>
                <a href="/PHP/TravelBlog/auth/login.php">Login</a>
                <a href="/PHP/TravelBlog/auth/aboutus.php">About Us</a>
                <a href="/PHP/TravelBlog/auth/register.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
