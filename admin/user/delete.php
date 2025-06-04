<?php
// Get car ID from URL parameter
$id = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

// Redirect if no ID is provided
if (empty($id)) {
    header('Location: index.php');
    exit;
}

$deleteSuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Connect to the database
    $dbhost = 'localhost:3307';
    $dbuser = 'root';
    $dbpassword = '';
    $dbname = 'travel blog';

    $conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
        or die('Failed to connect to db.');

    // Delete the user record with the provided ID
    $sql = "DELETE FROM user WHERE UserID = '$id'";
    $result = mysqli_query($conn, $sql);

    // Close the database connection
    mysqli_close($conn);

    $deleteSuccess = $result;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
     <?php include("../../inc/_navbar.php"); ?>
     <div class="container" style="max-width: 500px; margin-top: 60px;">
        <h1 class="mb-4 text-center">Delete User</h1>
        <?php if ($deleteSuccess === true): ?>
            <div class="alert alert-success text-center">
                Deleted successfully! Redirecting to index.php after 5 seconds...
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = "index.php";
                }, 3000);
            </script>
        <?php elseif ($deleteSuccess === false): ?>
            <div class="alert alert-danger text-center">
                Failed to delete user. Please try again.
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                Are you sure you want to delete this user?
            </div>
            <form method="post" class="d-flex justify-content-center gap-3">
                <input type="hidden" name="confirm" value="yes">
                <button type="submit" class="btn btn-danger">Delete</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
