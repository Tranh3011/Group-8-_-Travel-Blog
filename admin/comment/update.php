<?php
$id = '';
// -- get data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
// redirect if no id
if (empty($id)) {
    header('Location: index.php');
}

// -- get comment by id
// connect db
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';
$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
or die('Failed to connect to db.');

$sql = "SELECT * FROM comment WHERE CommentID = '$id'";
$_result = mysqli_query($conn, $sql);
$comment = mysqli_fetch_assoc($_result);
// close connection
mysqli_close($conn);

// redirect if comment not exist
if (!$comment) {
    header('Location: index.php');
}

$errors = [];
$content = $comment['Content'];

// get user data
if ($_POST) {
    if (isset($_POST['content'])) {
        $content = $_POST['content'];
    }

    // -- clean data
    $content = trim($content);
    $content = htmlspecialchars($content);
    $content = addslashes($content);

    // -- validate user data
    // required
    if (empty($content)) {
        $errors['content'] = 'Content is required';
    }

    if (empty($errors)) {
        // connect db
        $conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
        if (!$conn) {
            die('Failed to connect to database');
        }

        // update
        $sql = "UPDATE `comment`
                SET `Content` = '$content'
                WHERE `CommentID` = $id";
        $result = @mysqli_query($conn, $sql);
        @mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Comment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include("../../inc/_navbar.php") ?>
    <div class="container">
    <h1>Update Comment</h1>
    <?php if (isset($result) && $result): ?>
        <h2 class="text-success">Updated successfully! Redirecting to index.php after 3 seconds...</h2>
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 3000); // Redirect to index.php after 3 seconds
        </script>
    <?php else: ?>
        <form action="" method="post">
            <div>
                <label for="content">Content:</label>
                <textarea name="content" id="content" class="form-control"><?php echo $content; ?></textarea>
                <span class="text-danger"><?php echo isset($errors['content']) ? $errors['content'] : ''; ?></span>
            </div>
<br>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    <?php endif; ?>
</body>
</html>