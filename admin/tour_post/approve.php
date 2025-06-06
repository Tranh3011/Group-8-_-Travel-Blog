<?php
// This file handles the approval process for tour posts submitted by customers.

// Include database connection
include_once '../../database/connect-db.php';

// Check if the approval request is made
if (isset($_POST['approve'])) {
    $postId = $_POST['post_id'];

    // Prepare the SQL statement to approve the post
    $stmt = $conn->prepare("UPDATE tour_posts SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $postId);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Tour post approved successfully.";
    } else {
        echo "Error approving tour post: " . $conn->error;
    }

    $stmt->close();
}

// Fetch all pending tour posts for approval
$result = $conn->query("SELECT * FROM tour_posts WHERE status = 'pending'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Tour Posts</title>
</head>
<body>
    <h1>Approve Tour Posts</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['title']; ?></td>
            <td>
                <form method="POST" action="">
                    <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="approve">Approve</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>