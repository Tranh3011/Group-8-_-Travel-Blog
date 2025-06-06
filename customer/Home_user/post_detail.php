<?php
session_start();
$host = 'localhost:3307';
$username = 'root';
$password = '';
$dbname = 'travel blog';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$post = null;
if ($post_id > 0) {
    $sql = "SELECT p.*, c.Name AS category_name, d.Name AS destination_name
            FROM posts p
            LEFT JOIN category c ON p.CategoryID = c.CategoryID
            LEFT JOIN destination d ON p.DestinationID = d.DestinationID
            WHERE p.PostID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include("../../inc/_navbar.php"); ?>
    <div class="container mt-4">
        <?php if ($post): ?>
            <div class="card mb-4">
                <?php if (!empty($post['image'])): ?>
                    <img src="<?= htmlspecialchars($post['image']) ?>" class="card-img-top mb-3" alt="<?= htmlspecialchars($post['Title']) ?>" style="max-height:350px;object-fit:cover;">
                <?php endif; ?>

                <!-- Show additional image  s if available -->
                <div class="mb-3 d-flex flex-wrap gap-2">
                    <?php
                    // Show up to 3 additional image  s if columns exist
                    foreach (['image2', 'image3', 'image4'] as $imgField) {
                        if (isset($post[$imgField]) && !empty($post[$imgField])) {
                            echo '<img src="' . htmlspecialchars($post[$imgField]) . '" alt="Additional" style="max-width:180px;max-height:120px;object-fit:cover;border-radius:6px;margin-right:10px;">';
                        }
                    }
                    ?>
                </div>

                <div class="card-body">
                    <h2 class="card-title"><?= htmlspecialchars($post['Title']) ?></h2>
                    <p>
                        <span class="badge bg-primary"><?= htmlspecialchars($post['category_name'] ?? 'Uncategorized') ?></span>
                        <?php if (!empty($post['destination_name'])): ?>
                            <span class="badge bg-info text-dark"><?= htmlspecialchars($post['destination_name']) ?></span>
                        <?php endif; ?>
                    </p>
                    <p class="card-text"><?= nl2br(htmlspecialchars($post['Content'])) ?></p>
                    <p class="text-muted">
                        Created at: <?= htmlspecialchars($post['Created_at']) ?>
                        <?php if (!empty($post['Updated_at'])): ?>
                            | Updated at: <?= htmlspecialchars($post['Updated_at']) ?>
                        <?php endif; ?>
                    </p>
                    <a href="post_index.php" class="btn btn-secondary">Back to Posts</a>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Post not found.</div>
            <a href="post_index.php" class="btn btn-secondary">Back to Posts</a>
        <?php endif; ?>
    </div>
</body>
</html>
