<?php
$id = '';
$title = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
if (empty($id)) {
    header('Location: index.php');
    exit;
}

// Lấy title của bài viết để hiển thị
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';
$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
    or die ('Failed to connect to db.');
$titleQuery = mysqli_query($conn, "SELECT Title FROM posts WHERE PostID = '$id'");
if ($row = mysqli_fetch_assoc($titleQuery)) {
    $title = $row['Title'];
}
$deleteSuccess = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    $sql = "DELETE FROM posts WHERE `posts`.`PostID` = '$id'";
    $result = mysqli_query($conn, $sql);
    $deleteSuccess = $result;
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<body>
    <?php include("../../inc/_navbar.php") ?>
    <div class="container" style="max-width: 500px; margin-top: 60px;">
        <h1 class="mb-4 text-center">Delete Post: <span class="font-weight-bold"><?php echo htmlspecialchars($title); ?></span></h1>
        <?php if ($deleteSuccess === true): ?>
            <div class="alert alert-success text-center">
                Deleted successfully! Redirecting to index.php after 3 seconds...
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = "index.php";
                }, 3000);
            </script>
        <?php elseif ($deleteSuccess === false): ?>
            <div class="alert alert-danger text-center">
                Failed to delete post. Please try again.
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                Are you sure you want to delete this post "<strong><?php echo htmlspecialchars($title); ?></strong>"?
            </div>
            <form method="post" class="d-flex justify-content-center gap-3">
                <input type="hidden" name="confirm" value="yes">
                <button type="submit" class="btn btn-danger mr-2">Delete</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>