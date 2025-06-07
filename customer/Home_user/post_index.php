<?php
session_start();
// Database connection
$host = 'localhost:3307';
$username = 'root';
$password = '';
$dbname = 'travel blog';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Lấy danh sách category và destination cho filter
$categories = [];
$destinations = [];
$cat_rs = $conn->query("SELECT CategoryID, Name FROM category ORDER BY Name");
if ($cat_rs) {
    while ($row = $cat_rs->fetch_assoc()) $categories[] = $row;
}
$dest_rs = $conn->query("SELECT DestinationID, Name FROM destination ORDER BY Name");
if ($dest_rs) {
    while ($row = $dest_rs->fetch_assoc()) $destinations[] = $row;
}

// Xử lý filter
$filter_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$filter_destination = isset($_GET['destination']) ? intval($_GET['destination']) : 0;

$sql_all_posts = "SELECT p.PostID, p.Title, p.Image, c.Name AS category_name, d.Name AS destination_name, p.Content
                  FROM posts p
                  LEFT JOIN category c ON p.CategoryID = c.CategoryID
                  LEFT JOIN destination d ON p.DestinationID = d.DestinationID
                  WHERE 1=1";
$params = [];
if ($filter_category) {
    $sql_all_posts .= " AND p.CategoryID = ?";
    $params[] = $filter_category;
}
if ($filter_destination) {
    $sql_all_posts .= " AND p.DestinationID = ?";
    $params[] = $filter_destination;
}
$sql_all_posts .= " ORDER BY p.PostID DESC";

$stmt = $conn->prepare($sql_all_posts);
if ($params) {
    $types = str_repeat('i', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_all_posts = $stmt->get_result();
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
        <!-- Filter Form -->
        <form class="row g-3 mb-4" method="get">
            <div class="col-md-4">
                <label for="category" class="form-label">Category</label>
                <select name="category" id="category" class="form-select">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['CategoryID'] ?>" <?= $filter_category == $cat['CategoryID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="destination" class="form-label">Destination</label>
                <select name="destination" id="destination" class="form-select">
                    <option value="0">All Destinations</option>
                    <?php foreach ($destinations as $dest): ?>
                        <option value="<?= $dest['DestinationID'] ?>" <?= $filter_destination == $dest['DestinationID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dest['Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
        <div class="row">
            <?php if (!empty($all_posts)): ?>
                <?php foreach ($all_posts as $p): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($p['Image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['Title']) ?>" style="height:200px;object-fit:cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($p['Title']) ?></h5>
                                <p class="card-text">
                                    <small class="text-muted"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></small>
                                    <?php if (!empty($p['destination_name'])): ?>
                                        <span class="text-muted ms-2"><?= htmlspecialchars($p['destination_name']) ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="card-text"><?= htmlspecialchars(mb_strimwidth($p['Content'], 0, 100, '...')) ?></p>
                                <a href="/PHP/TravelBlog/customer/Home_user/post_detail.php?post_id=<?= $p['PostID'] ?>" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <?php
                        if ($filter_category || $filter_destination) {
                            echo "No posts found for your filter.";
                        } else {
                            echo "No posts found.";
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
