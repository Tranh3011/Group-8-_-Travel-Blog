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
    /* margin-top: 80px;  Xóa dòng này để ảnh sát navbar */
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

<?php include("../../inc/_navbar.php"); ?>

<!-- Hero Section -->
<div class="hero-section">
    <h1>Discover Amazing Destinations</h1>
</div>

<!-- Recent Posts Section -->
<div class="container">
    <h1 class="mb-4">Best places to visit in the world</h1>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <!-- Static featured posts for Paris, New York, Tokyo -->
        <div class="col">
            <div class="card">
                <img src="./image/paris.jpg" class="card-img-top" alt="Paris">
                <div class="card-body">
                    <h5 class="card-title">Exploring Paris</h5>
                    <p class="card-text">Discover the romance and beauty of Paris, from the Eiffel Tower to the Seine River and the charming streets of Montmartre.</p>
                    <p class="card-text"><strong>Category:</strong> Cities</p>
                    <a href="post_Paris.php" class="btn btn-primary">Read More</a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <img src="./image/newyorkadventureclub.jpg" class="card-img-top" alt="New York">
                <div class="card-body">
                    <h5 class="card-title">New York Adventures</h5>
                    <p class="card-text">Experience the vibrant energy of New York City, from the Statue of Liberty to Central Park and the dazzling lights of Times Square.</p>
                    <p class="card-text"><strong>Category:</strong> Cities</p>
                    <a href="post_NewYork.php" class="btn btn-primary">Read More</a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <img src="./image/tokyo_pic.jpeg" class="card-img-top" alt="Tokyo">
                <div class="card-body">
                    <h5 class="card-title">Tokyo: The traditional and modern city</h5>
                    <p class="card-text">Explore Tokyo's unique blend of tradition and modernity, from ancient shrines to the bustling cityscape and delicious cuisine.</p>
                    <p class="card-text"><strong>Category:</strong> Cities</p>
                    <a href="post_Tokyo.php" class="btn btn-primary">Read More</a>
                </div>
            </div>
        </div>
        <!-- ...existing code for dynamic posts if needed... -->
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
