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
            background-color: #f4f6f9; /* Nền sáng */
        }

        /* Navbar styling */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(90deg, #0a1f44, #142850); /* Xanh đen */
            padding: 10px 20px;
            color: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); 
        }

        .navbar-logo {
            display: flex;
            align-items: center;
        }

        .navbar-logo img {
            height: 60px; /* Set the height of the logo */
            width: auto; /* Keep aspect ratio */
            margin-right: 10px;
        }

        .navbar-logo h1 {
            margin: 0;
            font-size: 20px;
            color: #fff; /* Màu trắng cho tiêu đề logo */
        }

        .navbar-links {
            display: flex;
            gap: 20px;
        }

        .navbar-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold; /* Chữ đậm */
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .navbar-links a:hover {
            color: #ffcc00; /* Màu vàng khi hover */
            transform: scale(1.1); /* Hiệu ứng phóng to nhẹ */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-links {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <!-- Logo Section -->
        <div class="navbar-logo">
            <img src="../inc/logo.jpg" alt="Let's Travel Logo">
            <h1>Let's Travel</h1>
        </div>
        
        <!-- Navigation Links -->
        <div class="navbar-links">
            <a href="../Home_user/index_homeAdmin.php">Home</a>
            <a href="../user/index.php">Manage User</a>
            <a href="../post/index.php">Manage Post</a>
            <a href="../destination/Destination.php">Manage Destination</a>
            <a href="../category/indexcategory.php">Manage Category</a>
            <a href="../comment/index.php">Manage Comment</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
