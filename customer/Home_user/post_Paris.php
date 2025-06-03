<?php
session_start();
require_once '../../database/connect-db.php';

// Post ID for Paris (set a unique ID for this post, e.g., 1)
$post_id = 1;

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
            header("Location: post_Paris.php");
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
    <title>Exploring Paris</title>
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
        <h1>Exploring Paris</h1>
        <img src="./image/paris.jpg" alt="paris">
    </header>

    <div class="container">
        <div class="post">
            <h2>1. Eiffel Tower</h2>
            <img src="./image/eiffel.jpg" alt="Eiffel Tower">
            <p>The Eiffel Tower is a famous symbol of France and an iconic structure of the city of Paris. Designed by engineer Gustave Eiffel, the tower was built between 1887 and 1889 to commemorate the World's Fair and the 100th anniversary of the French Revolution. At 330 meters high, the tower was the tallest structure in the world until 1930. Today, the Eiffel Tower is one of the most popular tourist attractions in the world, attracting millions of visitors each year thanks to its unique architectural beauty and the amazing panoramic views of Paris from its floors. A visit to the top is a must for any traveler.</p>
            <p class="recommendation">Recommendation: Visit in the evening to see the tower lit up beautifully.</p>
        </div>
        <div class="post">
            <h2>2. Louvre Museum</h2>
            <img src="./image/Louvre Museum.jpg" alt="Louvre Museum">
            <p>The Louvre Museum, located in central Paris, is one of the largest and most famous art museums in the world. Founded in 1793, the museum was once a royal palace before becoming a place to display works of art and historical artifacts. The Louvre owns more than 35,000 artifacts, including masterpieces such as Leonardo da Vinci's Mona Lisa and the Venus de Milo statue. With its unique architecture, highlighted by the glass pyramid at the entrance, the Louvre attracts millions of visitors each year, becoming a cultural icon that cannot be missed when visiting Paris.</p>
            <p class="recommendation">Recommendation: Book tickets in advance to skip the long queues.</p>
        </div>
        <div class="post">
            <h2>3. Montmartre</h2>
            <img src="./image/Montmartre.jpg" alt="Montmartre">
            <p>Montmartre is a famous hillside district in northern Paris known as a center of art and culture. It is home to the magnificent Sacré-Cœur Basilica, which offers stunning views of the city. Montmartre has been an inspiration to many famous artists, including Picasso, Van Gogh, and Renoir, and today maintains a unique artistic atmosphere with cafes, galleries, and cobblestone streets. The area is a favorite destination for tourists looking for the romantic and artistic beauty that characterizes Paris.</p>
            <p class="recommendation">Recommendation: Explore the area on foot to discover hidden gems.</p>
        </div>
        <div class="post">
            <h2>4. Seine River Cruise</h2>
            <img src="./image/Seine River Cruise.jpg" alt="Seine River Cruise">
            <p>A Seine River Cruise is a unique and romantic way to see Paris from a different perspective. The cruise takes you past famous landmarks such as the Eiffel Tower, Notre Dame Cathedral, the Louvre Museum, and Pont Alexandre III. Illuminated at night, the Seine offers an unforgettable and captivating view. It is the perfect way to see the beauty of Paris, known as the "City of Light," on a relaxing and emotional journey.</p>
            <p class="recommendation">Recommendation: Opt for a sunset cruise for the best experience.</p>
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
                            <img src="<?= htmlspecialchars($comment['Avatar'] ?? '../uploads/default-avatar.jpg') ?>" 
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>