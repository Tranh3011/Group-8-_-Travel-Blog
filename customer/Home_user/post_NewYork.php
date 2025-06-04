<?php
session_start();
// Database connection
$host = 'localhost:3307';
$username = 'root';
$password = '';
$dbname = 'travel blog';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Post ID for New York Adventures (set a unique ID for this post, e.g., 2)
$post_id = 2;

// Lấy post_id từ URL nếu có
if (isset($_GET['post_id'])) {
    $post_id = intval($_GET['post_id']);
}

// Handle add comment
$errors = [];
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
                    header("Location: post_NewYork.php?post_id=$post_id");
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

// Fetch comments (lấy FullName thay vì join user)
$sql_comments = "SELECT c.*, u.Avatar, c.FullName 
                 FROM comment c
                 LEFT JOIN user u ON c.FullName = u.FullName
                 WHERE c.PostID = $post_id
                 ORDER BY c.Created_at DESC";
$result_comments = $conn->query($sql_comments);
$comments = [];
if ($result_comments) {
    while ($row = $result_comments->fetch_assoc()) {
        $comments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New York Adventures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            /* padding-top: 80px; */
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
        /* Comment section styles */
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
        /* Footer styles (match index_homepage) */
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
        <h1>New York Adventures</h1>
        <img src="./image/newyorkadventureclub.jpg" alt="paris">
    </header>

    <div class="container">
        <div class="post">
            <h2>1. Statue of Liberty</h2>
            <img src="./image/Statue of Liberty.jpg" alt="Statue of Liberty">
            <p>The Statue of Liberty, located on Liberty Island in New York Harbor, is one of the most iconic symbols of freedom and democracy. Gifted by France to the United States in 1886, it represents hope and opportunity for millions of immigrants who arrived in America. Standing at 305 feet tall, it depicts the Roman goddess Libertas holding a torch and a tablet inscribed with the date of the Declaration of Independence. The statue is a UNESCO World Heritage Site and a must-visit landmark.</p>
            <p class="recommendation">Recommendation: Book tickets early to access the crown for a unique view.</p>
        </div>
        <div class="post">
            <h2>2. Central Park</h2>
            <img src="./image/Central Park.jpg" alt="Central Park">
            <p>Central Park, situated in the heart of Manhattan, is a sprawling urban oasis spanning 843 acres. Opened in 1858, it offers a peaceful retreat from the hustle and bustle of New York City. The park features scenic landscapes, lakes, walking trails, and attractions such as the Central Park Zoo, Bethesda Terrace, and Strawberry Fields. It's a favorite spot for recreation, picnics, and cultural events, attracting millions of visitors annually.</p>
            <p class="recommendation">Recommendation: Visit during fall to enjoy the colorful foliage.</p>
        </div>
        <div class="post">
            <h2>3. Times Square</h2>
            <img src="./image/Times Square.jpg" alt="Times Square">
            <p>Times Square, located at the intersection of Broadway and Seventh Avenue in Midtown Manhattan, is a dazzling spectacle of lights, entertainment, and energy. Known as "The Crossroads of the World," it is a global icon of New York City and a hub of commerce and culture. Its giant electronic billboards, illuminated 24/7, make it one of the most photographed places in the world. Times Square is the epicenter of Broadway theater, home to dozens of world-class productions, and hosts the world-famous New Year's Eve Ball Drop, an event watched by millions globally. The area also features flagship stores, renowned restaurants, and entertainment venues. With its vibrant atmosphere, Times Square captures the dynamic spirit of New York and continues to draw millions of visitors each year.</p>
            <p class="recommendation">Recommendation: Experience the vibrant nightlife and grab a photo in front of the iconic billboards.</p>
        </div>
        <div class="post">
            <h2>4. Brooklyn Bridge</h2>
            <img src="./image/Brooklyn Bridge.jpg" alt="Brooklyn Bridge">
            <p>The Brooklyn Bridge, completed in 1883, is a historic suspension bridge that connects the boroughs of Manhattan and Brooklyn over the East River. Designed by John A. Roebling and completed by his son Washington Roebling, the bridge was a groundbreaking engineering feat of its time, using steel-wire cables for the first time in history. The bridge spans 1,595 feet and features iconic Gothic-style towers made of limestone and granite. It serves as a vital transportation link and offers a pedestrian walkway that provides breathtaking views of the New York City skyline, the East River, and landmarks such as the Statue of Liberty. A walk across the Brooklyn Bridge is not just a journey between two boroughs but a step back in history, symbolizing innovation, resilience, and the connection between people. The bridge remains a timeless symbol of New York City and an architectural masterpiece admired worldwide.</p>
            <p class="recommendation">Recommendation: Walk the bridge at sunset for a breathtaking experience.</p>
        </div>

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
                            <?php
                            $avatarPath = !empty($comment['Avatar']) && file_exists("../../uploads/" . $comment['Avatar'])
                                ? "../../uploads/" . $comment['Avatar']
                                : "../uploads/default-avatar.jpg";
                            ?>
                            <img src="<?= htmlspecialchars($avatarPath) ?>"
                                alt="User" class="user-avatar me-3">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
