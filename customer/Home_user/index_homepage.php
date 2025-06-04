<?php
// Database connection 
$host = 'localhost:3307';
$username = 'root';
$password = '';
$dbname = 'travel blog';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch posts with category
$sql = "SELECT p.PostID, p.Title, p.Content, p.Image, c.Name AS category_name
        FROM post p
        LEFT JOIN category c ON p.CategoryID = c.CategoryID
        ORDER BY p.Created_at DESC";
$result = $conn->query($sql);

// Fetch travel tips (giữ nguyên)
$tips_sql = "SELECT TipTitle, TipContent FROM travel_tips";
$tips_result = $conn->query($tips_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Giữ nguyên phần <head> của bạn -->
</head>
<body>
<?php include("../../inc/_navbar.php"); ?>

<!-- Booking & Search Section -->
<div class="container mt-3 mb-4">
    <div class="row justify-content-between align-items-center">
        <div class="col-md-3 mb-2 mb-md-0">
            <a href="booking.php" class="btn btn-success w-100">Booking</a>
        </div>
        <div class="col-md-9">
            <form class="d-flex" method="get" action="search.php">
                <input class="form-control me-2" type="search" name="q" placeholder="Search destinations, tips, posts..." aria-label="Search">
                <button class="btn btn-outline-primary" type="submit">Search</button>
            </form>
        </div>
    </div>
</div>

<!-- Hero Section -->
<div class="hero-section">
    <h1>Discover Amazing Destinations</h1>
</div>

<!-- Recent Posts Section -->
<div class="container">
    <h1 class="mb-4">Best places to visit in the world</h1>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($post = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card">
                        <img src="<?= htmlspecialchars($post['Image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($post['Title']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($post['Title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(substr($post['Content'], 0, 100)) ?>...</p>
                            <p class="card-text"><strong>Category:</strong> <?= htmlspecialchars($post['category_name'] ?? 'Uncategorized') ?></p>
                            <a href="post_detail.php?post_id=<?= $post['PostID'] ?>" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col">
                <p>No posts available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer>
    <!-- Giữ nguyên phần footer của bạn -->
</footer>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>