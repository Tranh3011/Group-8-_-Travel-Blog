<?php
// manage.php

// Include database connection
include_once '../../database/connect-db.php';

// Fetch tour posts from the database
function fetchTourPosts($conn) {
    $sql = "SELECT * FROM tour_posts";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Handle delete request
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $deleteSql = "DELETE FROM tour_posts WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage.php");
}

// Fetch tour posts
$tourPosts = fetchTourPosts($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tour Posts</title>
</head>
<body>
    <h1>Manage Tour Posts</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tourPosts as $post): ?>
                <tr>
                    <td><?php echo $post['id']; ?></td>
                    <td><?php echo $post['title']; ?></td>
                    <td>
                        <form method="post" action="edit.php?id=<?php echo $post['id']; ?>">
                            <button type="submit">Edit</button>
                        </form>
                        <form method="post" action="manage.php">
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <button type="submit" name="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>