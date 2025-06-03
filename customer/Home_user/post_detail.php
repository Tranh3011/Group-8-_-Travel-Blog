<?php
session_start();
// Database connection
$host = 'localhost:3307';
$username = 'root';
$password = '';
$dbname = 'travel_blog';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get post ID from URL
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

// Fetch post details
$sql_post = "SELECT p.Title, p.Content, p.Image, c.Name AS category_name
             FROM post p
             LEFT JOIN category c ON p.CategoryID = c.CategoryID
             WHERE p.PostID = ?";
$stmt_post = $conn->prepare($sql_post);
$stmt_post->bind_param("i", $post_id);
$stmt_post->execute();
$result_post = $stmt_post->get_result();
$post = $result_post->fetch_assoc();
$stmt_post->close();

if (!$post) {
    die("Post not found.");
}

// Fetch post sections (e.g., Statue of Liberty, Central Park)
$sql_sections = "SELECT Title, Content, Image, Recommendation 
                 FROM post_details 
                 WHERE PostID = ? 
                 ORDER BY DetailID";
$stmt_sections = $conn->prepare($sql_sections);
$stmt_sections->bind_param("i", $post_id);
$stmt_sections->execute();
$result_sections = $stmt_sections->get_result();
$sections = [];
while ($row = $result_sections->fetch_assoc()) {
    $sections[] = $row;
}
$stmt_sections->close();

// Handle add comment
$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
    $comment_content = trim($_POST['comment_content']);
    if (empty($comment_content)) {
        $errors['comment'] = "Comment cannot be empty";
    } else {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO comment (UserID, PostID, Content, Created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $post_id, $comment_content);
        if ($stmt->execute()) {
            header("Location: post_detail.php?post_id=$post_id");
            exit();
        } else {
            $errors['comment'] = "Error adding comment";
        }
        $stmt->close();
    }
}

// Fetch comments
$sql_comments = "SELECT c.*, u.FirstName, u.LastName, u.Avatar 
                 FROM comment c
                 JOIN user u ON c.UserID = u.UserID
                 WHERE c.PostID = ?
                 ORDER BY c.Created_at DESC";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $post_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
$comments = [];
while ($row = $result_comments->fetch_assoc()) {
    $comments[] = $row;
}
$stmt_comments->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['Title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Giữ nguyên CSS từ file post_NewYork.php */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            padding-top: 80px;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        header {
            background: linear-gradient(to bottom, #ffecd2, #fcb69f);
            color: #fff;
            text-align: center;
            padding: 50px 0;
            position: relative;
        }
        header h1 {
            margin: 0;
            font-size: 48px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }
        header img {
            margin-top: 20px;
            width: 90%;
            max-width: 1000px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        header .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 10px;
            z-index: 1;
        }
        .post {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .post img {
            width: 100%;
            border-radius: 5px;
        }
        .post h2 { color: #333; }
        .post p { color: #666; }
        .recommendation { font-weight: bold; color: #0779e4; }
        .comment-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        .comment {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .comment:last-child { border-bottom: none; }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        footer {
            background-color: #123458;
            color: #F1FEFC;
            padding-top: 40px;
            padding-bottom: 40px;
            margin-top: 50px;
        }
        footer h5 {
            font-weight: bold;
            font-size: 1.25rem;
        }
        footer .footer-link {
            color: #F1FEFC;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        footer .footer-link:hover {
            color: #D4C9BE;
            text-decoration: underline;
        }
        footer .text-white-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        @media (max-width: 768px) {
            .container { width: 98%; }
            footer h5 { font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    <?php include("../../inc/_navbar.php"); ?>
    <header>
        <div class="overlay"></div>
        <h1><?= htmlspecialchars($post['Title']) ?></h1>
        <img src="<?= htmlspecialchars($post['Image']) ?>" alt="<?= htmlspecialchars($post['Title']) ?>">
    </header>

    <div class="container">
        <p><strong>Category:</strong> <?= htmlspecialchars($post['category_name'] ?? 'Uncategorized') ?></p>
        <p><?= nl2br(htmlspecialchars($post['Content'])) ?></p>

        <!-- Post Sections -->
        <?php foreach ($sections as $section): ?>
            <div class="post">
                <h2><?= htmlspecialchars($section['Title']) ?></h2>
                <img src="<?= htmlspecialchars($section['Image']) ?>" alt="<?= htmlspecialchars($section['Title']) ?>">
                <p><?= nl2br(htmlspecialchars($section['Content'])) ?></p>
                <p class="recommendation">Recommendation: <?= htmlspecialchars($section['Recommendation']) ?></p>
            </div>
        <?php endforeach; ?>

        <!-- Comment Section -->
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
                            <img src="<?= htmlspecialchars($comment['Avatar'] ?? '../Uploads/default-avatar.jpg') ?>" 
                                 alt="User" class="user-avatar me-3">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($comment['FirstName'] . ' ' . $comment['LastName']) ?></h6>
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
                        <?php while ($tip = $tips_result->fetch_assoc()): ?>
                            <li><a href="#" class="footer-link"><?= htmlspecialchars($tip['TipTitle']) ?></a></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
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
                    <p class="text-white-50">© 2025 Travel Blog. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>