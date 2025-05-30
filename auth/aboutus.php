
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Mountain Travel Blog</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    /* Navbar Styles */
    .navbar {
        background-color:rgba(6, 29, 48); /* Màu xanh dương */
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 15px 0;
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
    }
    
    .navbar-brand {
        font-weight: bold;
        font-size: 1.5rem;
        color: white !important;
    }
    
    .navbar-nav .nav-link {
        color: white !important;
        font-weight: 600; /* Chữ in đậm */
        margin: 0 10px;
        padding: 8px 15px;
        transition: all 0.3s ease;
    }
    
    .navbar-nav .nav-link:hover {
        color: #D4C9BE !important; /* Màu vàng khi hover */
        transform: translateY(-2px);
    }
    
    .navbar-nav .nav-item.active .nav-link {
        color: #D4C9BE !important; /* Màu vàng khi active */
        text-decoration: underline;
    }
    
    /* Toggler icon màu trắng */
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    
    .navbar-toggler {
        border-color: rgba(255,255,255,0.5);
    }
    
    /* Adjust body padding to account for fixed navbar */
    body {
        padding-top: 70px;
    }
</style>

</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="../Home_user/index_homepage.php">
            <img src="../inc/logo.jpg" alt="Travel Blog Logo" class="img-fluid" style="max-width: 100px; margin-right: 10px;">
            Travel Blog
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../auth/login.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="aboutus.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/register.php">Register</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- About Us Content -->
<div class="container">
    <div class="about-section">
        <h1 class="text-center mb-5">About Mountain Travel Blog</h1>
        
        <div class="row">
            <div class="col-md-6">
                <h2>Our Story</h2>
                <p>Founded in 2024, Mountain Travel Blog was born out of a passion for exploration and a desire to share the world's most breathtaking mountain destinations with fellow travelers. What started as a small blog between friends has grown into a thriving community of adventure seekers.</p>
                <p>Our team of travel experts scours the globe to bring you the most up-to-date information, hidden gems, and practical tips for your mountain adventures.</p>
            </div>
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="Mountain View" class="img-fluid rounded">
            </div>
        </div>
        
        <div class="mission-section mt-5">
            <h2 class="text-center mb-4">Our Mission</h2>
            <p class="text-center lead">To inspire and empower travelers to explore the world's mountain regions responsibly and authentically, while providing the most reliable and comprehensive travel resources.</p>
        </div>
    
        </div>
        
        <div class="login-prompt">
            <h3>Join Our Community</h3>
            <p>Create an account to save your favorite destinations, share your travel stories, and connect with other mountain enthusiasts.</p>
            <a href="../auth/register.php" class="btn btn-primary">Register Now</a>
            <p class="mt-3">Already have an account? <a href="../auth/login.php">Login here</a></p>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white mt-5">
    <div class="container py-4">
        <div class="row">
            <div class="col-md-6">
                <h5>Mountain Travel Blog</h5>
                <p>Your trusted guide to mountain adventures around the world.</p>
            </div>
            <div class="col-md-6 text-end">
                <p>&copy; 2024 Mountain Travel Blog. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>