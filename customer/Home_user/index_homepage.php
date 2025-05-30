<?php
// Database connection 
$host = 'localhost:3307';
$username = 'root';
$password = '';
$dbname = 'travel_blog';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch categories and destinations
$sql = "SELECT c.Name AS category_name, d.Name AS destination_name, d.Description, d.Image
        FROM category c
        JOIN category_destination cd ON c.CategoryID = cd.CategoryID
        JOIN destination d ON cd.DestinationID = d.DestinationID";
$result = $conn->query($sql);

// Fetch travel tips
$tips_sql = "SELECT TipTitle, TipContent FROM travel_tips";
$tips_result = $conn->query($tips_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Blog</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
body {
    background-color: #F1FEFC; /* Light background color */
    color: #030303; /* Dark text color */
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    padding-top: 80px; /* Đảm bảo nội dung không bị navbar che khuất */
}

/* Navbar */
.navbar {
    background-color: #123458; /* Dark blue background */
    border-bottom: 3px solid #D4C9BE; /* Beige border */
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Thêm hiệu ứng bóng đổ */
}

.navbar a {
    color: #F1FEFC !important; /* Light text color */
    font-weight: 600; /* Chữ đậm */
}

/* Liên kết trong navbar */
.navbar-nav .nav-item .nav-link {
    color: #F1FEFC !important; /* Light text color */
    padding: 10px 15px;
    text-transform: uppercase;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Màu nền khi hover */
.navbar-nav .nav-item .nav-link:hover {
    background-color: #D4C9BE; /* Beige hover background */
    color: #030303; /* Dark text on hover */
    border-radius: 5px;
}

/* Cập nhật dropdown menu */
.navbar-nav .nav-item.dropdown .dropdown-menu {
    background-color: #123458; /* Dark blue background */
    border: none; /* Loại bỏ viền mặc định của dropdown */
}

.navbar-nav .nav-item.dropdown .dropdown-item {
    color: #F1FEFC; /* Light text color */
    padding: 10px 20px;
    font-size: 1rem;
}

/* Màu nền khi hover trên các mục dropdown */
.navbar-nav .nav-item.dropdown .dropdown-item:hover {
    background-color: #D4C9BE; /* Beige hover background */
    color: #030303; /* Dark text on hover */
}

/* Hero Section */
.hero-section {
    background-color: #123458; /* Dark blue background */
    color: #F1FEFC; /* Light text color */
    background-image: url('https://tiesinstitute.com/wp-content/uploads/2021/01/shutterstock_268004744-2.jpg');
    background-size: cover;
    background-position: center;
    height: 400px;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    margin-top: 80px; /* Khoảng cách giữa navbar và hero section */
    text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.7);
}

.hero-section h1 {
    font-size: 2rem;
    font-weight: bold;
}

/* Recent Posts Section */
.container {
    margin-top: 50px;
}

.card {
    border-radius: 10px;
    transition: transform 0.3s ease-in-out;
}

.card:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.card-img-top {
    height: 200px;
    object-fit: cover;
}

.card-title {
    color: #123458; /* Dark blue for card titles */
}

.card-body {
    background-color: #D4C9BE; /* Beige background for cards */
    color: #030303; /* Dark text color */
    border-radius: 5px;
}

/* Thêm khoảng cách giữa các bài viết */
.row-cols-md-3 .col {
    margin-bottom: 30px;
}

/* Thiết lập responsive cho các bài viết */
@media (max-width: 768px) {
    .card-img-top {
        height: 150px; /* Giảm chiều cao hình ảnh cho màn hình nhỏ */
    }
}


/* General footer styles */
footer {
    background-color: #123458; /* Dark blue background */
    color: #F1FEFC; /* Light text color */
    padding-top: 40px;
    padding-bottom: 40px;
    margin-top: 50px;
}

footer h5 {
    font-weight: bold;
    font-size: 1.25rem;
}

footer .footer-link {
    color: #F1FEFC; /* Light text color */
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.3s ease;
}

footer .footer-link:hover {
    color: #D4C9BE; /* Beige hover color */
    text-decoration: underline;
}

footer .social-icons {
    margin-top: 20px;
}

footer .social-icon {
    color: #F1FEFC; /* Light icon color */
    font-size: 2rem; /* Kích thước lớn hơn cho biểu tượng */
    margin: 0 15px;
    transition: all 0.3s ease;
}

footer .social-icon:hover {
    color: #D4C9BE; /* Beige hover color */
    transform: scale(1.2);
}

footer .text-white-50 {
    color: rgba(255, 255, 255, 0.7) !important; /* Màu chữ nhạt cho phần bản quyền */
}

/* Responsive Design */
@media (max-width: 768px) {
    footer h5 {
        font-size: 1.1rem;
    }

    footer .social-icon {
        font-size: 1.5rem;
        margin: 0 10px;
    }
}


    /* .hero-section h1 {
        font-size: 2rem; 
    } */
/* } */

/* Xóa màu nền trắng hoặc overlay cho phần danh mục */
.categories-details {
    background-color: transparent; /* Đảm bảo không có overlay trắng */
    padding: 20px;
    border-radius: 10px;
    box-shadow: none;
}


    </style>
</head>
<body>

<!-- Main navigation -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #123458; position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
    <div class="container">
        <a class="navbar-brand" href="index_homepage.php">
            <img src="../uploads/logo.jpg" alt="Travel Blog Logo" class="img-fluid" style="max-width: 50px; margin-right: 10px;">
            Let's Travel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index_homepage.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Categories
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Cities">Cities</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Beaches">Beaches</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Cultural%20Sites">Cultural Sites</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Adventure%20Spots">Adventure Spots</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Natural%20Wonders">Natural Wonders</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Luxury%20Destinations">Luxury Destinations</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Family-friendly%20Locations">Family-friendly Locations</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Romantic%20Getaways">Romantic Getaways</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Wildlife">Wildlife</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Culinary%20Destinations">Culinary Destinations</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarPosts" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Posts
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarPosts">
                        <li><a class="dropdown-item" href="post_Paris.php">Exploring Paris</a></li>
                        <li><a class="dropdown-item" href="post_NewYork.php">New York Adventures</a></li>
                        <li><a class="dropdown-item" href="post_Tokyo.php">Tokyo: The traditional and modern city</a></li>
                        <li><a class="dropdown-item" href="#">Rome: The Eternal City</a></li>
                        <li><a class="dropdown-item" href="#">London: A City of History</a></li>
                        <li><a class="dropdown-item" href="#">Sydney: Sun and Surf</a></li>
                        <li><a class="dropdown-item" href="#">Singapore: A City of Luxury</a></li>
                        <li><a class="dropdown-item" href="#">Bali</a></li>
                        <li><a class="dropdown-item" href="#">Sapa - A perfect sightseeing and cultural trip</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>


<!-- Hero Section -->
<div class="hero-section">
    <h1>Discover Amazing Destinations</h1>
</div>

<!-- Recent Posts Section -->
<div class="container">
    <h1 class="mb-4">Best places to visit in the world  </h1>
    
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php
// Fetch the 3 most recent posts
$sql = "SELECT
            d.Name AS destination_name,
            d.Description,
            d.Image,
            c.Name AS category_name,
            d.post_link
        FROM
            posts p
        JOIN
            destination d ON p.DestinationID = d.DestinationID
        JOIN
            category_destination cd ON d.DestinationID = cd.DestinationID
        JOIN
            category c ON cd.CategoryID = c.CategoryID
        ORDER BY
            p.Created_at DESC
        LIMIT 3";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Display the recent posts
    while ($row = $result->fetch_assoc()) {
        // Prepare variables
        $image = '../uploads/' . $row['Image']; // Assuming image path is stored relative to 'uploads/'
        $post_title = $row['destination_name'];
        $post_description = $row['Description'];
        $post_link = $row['post_link']; // Assuming 'post_link' column exists in 'destination' table
        $category_name = $row['category_name'];

        // Check if the image exists, fallback to default image if not found
        if (!file_exists($image)) {
            $image = '../uploads/default_image.jpg'; // Fallback image
        }

        // Displaying the card with the recent post and "Read More" button
        echo "<div class='col'>
                <div class='card'>
                    <img src='" . htmlspecialchars($image) . "' class='card-img-top' alt='" . htmlspecialchars($post_title) . "'>
                    <div class='card-body'>
                        <h5 class='card-title'>" . htmlspecialchars($post_title) . "</h5>
                        <p class='card-text'>" . htmlspecialchars(substr($post_description, 0, 100)) . "...</p>
                        <p class='card-text'><strong>Category:</strong> " . htmlspecialchars($category_name) . "</p>
                        <a href='" . htmlspecialchars($post_link) . "' class='btn btn-primary'>Read More</a>
                    </div>
                </div>
            </div>";
    }
} else {
    echo "<p>No posts found.</p>";
}
?>

    </div>
</div>





<!-- Footer -->
<footer>
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4">
                <h5 class="text-white mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="footer-link">About Us</a></li>
                    <li><a href="#" class="footer-link">Our Services</a></li>
                    <li><a href="#" class="footer-link">Privacy Policy</a></li>
                    <li><a href="#" class="footer-link">Support</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="text-white mb-3">Travel Tips</h5>
                <ul class="list-unstyled">
                    <?php while($tip = $tips_result->fetch_assoc()): ?>
                        <li><a href="#" class="footer-link"><?= $tip['TipTitle']; ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <!-- Contact Us -->
            <div class="col-md-4">
                <h5 class="text-white mb-3">Contact Us</h5>
                <ul class="list-unstyled">
                    <li><a href="mailto:info@travelblog.com" class="footer-link">Email: info@travelblog.com</a></li>
                    <li><a href="tel:+123456789" class="footer-link">Phone: +123 456 789</a></li>
                    <li><a href="#" class="footer-link">Address: 123 Travel St, City, Country</a></li>
                </ul>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col text-center">
                <p class="text-white-50">&copy; 2025Travel Blog. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</footer>





<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
