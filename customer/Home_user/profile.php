<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'user') {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../database/connect-db.php';

$user_id = $_SESSION['user_id'];
$success = '';
$errors = [];

// Lấy thông tin user
$sql = "SELECT * FROM user WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Lấy các bài post đã đăng
$sql = "SELECT * FROM posts WHERE UserID = ? ORDER BY Created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Lấy các đơn hàng đã booking (nếu có bảng booking)
$bookings = [];
if ($conn->query("SHOW TABLES LIKE 'booking'")->num_rows > 0) {
    $sql = "SELECT * FROM booking WHERE UserID = ? ORDER BY BookingDate DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Let's Travel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
        .section-title { font-weight: bold; font-size: 1.3rem; margin-top: 30px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #123458; position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
    <div class="container">
        <a class="navbar-brand" href="category/indexcategory.php">
            <img src="../uploads/logo.jpg" alt="Logo">
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
                        <li><a class="dropdown-item" href="/PHP/TravelBlog/category/indexcategory.php?category=Cities">Cities</a></li>
                        <li><a class="dropdown-item" href="/PHP/TravelBlog/category/indexcategory.php?category=Beaches">Beaches</a></li>
                        <li><a class="dropdown-item" href="/PHP/TravelBlog/category/indexcategory.php?category=Cultural%20Sites">Cultural Sites</a></li>
                        <li><a class="dropdown-item" href="/PHP/TravelBlog/category/indexcategory.php?category=Adventure%20Spots">Adventure Spots</a></li>
                        <li><a class="dropdown-item" href="/PHP/TravelBlog/category/indexcategory.php?category=Natural%20Wonders">Natural Wonders</a></li>
                        <li><a class="dropdown-item" href="/PHP/TravelBlog/category/indexcategory.php?category=Luxury%20Destinations">Luxury Destinations</a></li>
                        <li><a class="dropdown-item" href="/PHP/TravelBlog/category/indexcategory.php?category=Family-friendly%20Locations">Family-friendly Locations</a></li>
                        <li><a class="dropdown-item" href="/PHP/TravelBlog/category/indexcategory.php?category=Romantic%20Getaways">Romantic Getaways</a></li>
                        <li><a class="dropdown-item" href="/PHP/TravelBlog/category/indexcategory.php?category=Wildlife">Wildlife</a></li>
                        <li><a class="dropdown-item" href="/PHP/TravelBlog/category/indexcategory.php?category=Culinary%20Destinations">Culinary Destinations</a></li>
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
                    <a class="nav-link active" href="profile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-4 mb-5" style="padding-top:90px;">
    <h1 class="mb-4">My Profile</h1>
    <div class="row">
        <div class="col-md-4 text-center">
            <img src="<?php
                $avatarPath = $user['Avatar'];
                if (strpos($avatarPath, '../uploads/') === 0) $avatarPath = substr($avatarPath, 3);
                echo htmlspecialchars($avatarPath ?: 'uploads/default-avatar.png');
            ?>" alt="Avatar" class="profile-avatar mb-3">
            <h3><?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?></h3>
            <p class="text-muted"><?php echo htmlspecialchars($user['Email']); ?></p>
            <p>
                <i class="bi bi-geo-alt"></i>
                <?php echo htmlspecialchars(($user['City'] ?? '') . ', ' . ($user['Country'] ?? '')); ?>
            </p>
        </div>
        <div class="col-md-8">
            <div class="section-title">Personal Information</div>
            <table class="table">
                <tr>
                    <th>First Name</th>
                    <td><?php echo htmlspecialchars($user['FirstName']); ?></td>
                </tr>
                <tr>
                    <th>Last Name</th>
                    <td><?php echo htmlspecialchars($user['LastName']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td><?php echo htmlspecialchars($user['PhoneNumber']); ?></td>
                </tr>
                <tr>
                    <th>City</th>
                    <td><?php echo htmlspecialchars($user['City']); ?></td>
                </tr>
                <tr>
                    <th>Country</th>
                    <td><?php echo htmlspecialchars($user['Country']); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section-title">My Posts</div>
    <?php if (empty($posts)): ?>
        <p>You haven't posted anything yet.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100">
                        <?php
                            $img = $post['image'];
                            if (strpos($img, '../uploads/') === 0) $img = substr($img, 3);
                        ?>
                        <?php if ($img): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" class="card-img-top" alt="Post Image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['Title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(mb_strimwidth($post['Content'], 0, 100, "...")); ?></p>
                            <small class="text-muted">Posted on <?php echo htmlspecialchars($post['Created_at']); ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="section-title">My Bookings</div>
    <?php if (empty($bookings)): ?>
        <p>You have no bookings.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <?php foreach (array_keys($bookings[0]) as $col): ?>
                            <th><?php echo htmlspecialchars($col); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <?php foreach ($booking as $val): ?>
                                <td><?php echo htmlspecialchars($val); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>