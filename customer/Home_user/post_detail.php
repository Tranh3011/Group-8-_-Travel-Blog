<?php
session_start();
// Database connection
$host = 'localhost:3307';
$username = 'root';
$password = '';
$dbname = 'travel blog';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Lấy tất cả các bài post từ bảng posts, đổi CategoryID thành DestinationID
$sql_all_posts = "SELECT p.PostID, p.Title, p.Image, c.Name AS category_name, p.Content
                  FROM posts p
                  LEFT JOIN category c ON p.DestinationID = c.CategoryID
                  ORDER BY p.PostID DESC";
$result_all_posts = $conn->query($sql_all_posts);
$all_posts = [];
if ($result_all_posts) {
    while ($row = $result_all_posts->fetch_assoc()) {
        $all_posts[] = $row;
    }
} else {
    echo '<div class="alert alert-danger">Query error: ' . htmlspecialchars($conn->error) . '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include("../../inc/_navbar.php"); ?>
    <div class="container mt-4">
        <h2 class="mb-4">All Posts</h2>
        <div class="row">
            <?php if (!empty($all_posts)): ?>
                <?php foreach ($all_posts as $p): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($p['Image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['Title']) ?>" style="height:200px;object-fit:cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($p['Title']) ?></h5>
                                <p class="card-text"><small class="text-muted"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></small></p>
                                <p class="card-text"><?= htmlspecialchars(mb_strimwidth($p['Content'], 0, 100, '...')) ?></p>
                                <a href="/PHP/TravelBlog/customer/Home_user/post_detail.php?post_id=<?= $p['PostID'] ?>" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">No posts found.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
