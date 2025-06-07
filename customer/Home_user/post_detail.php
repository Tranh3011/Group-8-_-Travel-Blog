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
$comments = [];
$errors = [];

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

    // Handle add comment
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
        $comment_content = trim($_POST['comment_content']);
        if (empty($comment_content)) {
            $errors['comment'] = "Comment cannot be empty";
        } else {
            $user_id = $_SESSION['user_id'];
            // Lấy FullName từ user_id
            $stmt_user = $conn->prepare("SELECT FullName FROM user WHERE UserID = ?");
            $full_name = '';
            if ($stmt_user) {
                $stmt_user->bind_param("i", $user_id);
                $stmt_user->execute();
                $stmt_user->bind_result($full_name);
                $stmt_user->fetch();
                $stmt_user->close();
            }
            if ($full_name) {
                $stmt = $conn->prepare("INSERT INTO comment (FullName, PostID, Content, Created_at) VALUES (?, ?, ?, NOW())");
                if ($stmt) {
                    $stmt->bind_param("sis", $full_name, $post_id, $comment_content);
                    if ($stmt->execute()) {
                        header("Location: post_detail.php?post_id=$post_id");
                        exit();
                    } else {
                        $errors['comment'] = "Error adding comment";
                    }
                    $stmt->close();
                } else {
                    $errors['comment'] = "Database error: " . $conn->error;
                }
            } else {
                $errors['comment'] = "User not found";
            }
        }
    }

    // Fetch comments
    $sql_comments = "SELECT c.*, u.Avatar, c.FullName 
                     FROM comment c
                     LEFT JOIN user u ON c.FullName = u.FullName
                     WHERE c.PostID = ?
                     ORDER BY c.Created_at DESC";
    $stmt_cmt = $conn->prepare($sql_comments);
    $stmt_cmt->bind_param("i", $post_id);
    $stmt_cmt->execute();
    $result_comments = $stmt_cmt->get_result();
    $comments = [];
    if ($result_comments) {
        while ($row = $result_comments->fetch_assoc()) {
            $comments[] = $row;
        }
    }
    $stmt_cmt->close();
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
                    <img src="<?= htmlspecialchars($post['image']) ?>" class="card-img-top mb-3"
                         alt="<?= htmlspecialchars($post['Title']) ?>"
                         style="width:100%;max-width:500px;max-height:350px;object-fit:cover;display:block;margin:auto;">
                <?php endif; ?>

                <!-- Show additional images if available -->
                <div class="mb-3 d-flex flex-wrap gap-2">
                    <?php
                    foreach (['image2', 'image3', 'image4'] as $imgField) {
                        if (isset($post[$imgField]) && !empty($post[$imgField])) {
                            echo '<img src="' . htmlspecialchars($post[$imgField]) . '" alt="Additional" style="width:180px;height:120px;object-fit:cover;border-radius:6px;margin-right:10px;">';
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
            <div class="comment-section">
            <h3>Comments (<?= count($comments) ?>)</h3>
            <?php if (isset($errors['comment'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errors['comment']) ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="comment_content" class="form-label">Add a comment</label>
                        <textarea class="form-control" id="comment_content" name="comment_content" rows="3" required></textarea>
                    </div>
                    <input type="hidden" name="add_comment" value="1">
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info">
                    Please <a href="../../auth/login.php">login</a> to leave a comment.
                </div>
            <?php endif; ?>
            <?php if (empty($comments)): ?>
                <div class="alert alert-info">No comments yet. Be the first to comment!</div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="d-flex">
                            <?php
                            $avatarPath = !empty($comment['Avatar']) && file_exists("../../uploads/" . $comment['Avatar'])
                                ? "../../uploads/" . $comment['Avatar']
                                : "../uploads/default-avatar.jpg";
                            ?>
                            <img src="<?= htmlspecialchars($avatarPath) ?>"
                                alt="User" class="user-avatar me-3"
                                style="width:48px;height:48px;object-fit:cover;border-radius:50%;">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($comment['FullName']) ?></h6>
                                <small class="text-muted">
                                    <?= date('M d, Y H:i', strtotime($comment['Created_at'])) ?>
                                </small>
                                <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($comment['Content'])) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
        <?php else: ?>
            <div class="alert alert-warning">Post not found.</div>
            <a href="post_index.php" class="btn btn-secondary">Back to Posts</a>
        <?php endif; ?>
    </div>
</body>
</html>
