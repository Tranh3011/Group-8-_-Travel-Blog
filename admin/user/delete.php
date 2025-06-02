create.php
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

// Connect to the database
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
    or die('Failed to connect to db.');

// Delete the car record with the provided ID
$sql = "DELETE FROM user WHERE UserID = '$id'";
$result = mysqli_query($conn, $sql);

// Close the database connection
mysqli_close($conn);

// Redirect with success message if the delete was successful
if ($result) {
    echo "<h2 class='text-success'>Deleted successfully! You are redirecting to indexUser.php after 3 seconds...</h2>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'indexUser.php';
            }, 3000);
          </script>";
    exit;
} else {
    echo "<h2 class='text-danger'>An error occurred while deleting the user.</h2>";
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
<body class="container">
     <?php include("../../inc/_navbar.php"); ?>

    <h1>Delete Selected Car</h1>

    <?php if (isset($result) && $result): ?>
        <h2 class="text-success">
            Deleted successfully! You are redirecting to indexUser.php after 3 seconds...
        </h2>
        <script>
            setTimeout(function() {
                window.location.href = "index.php";
            }, 3000);
        </script>
    <?php else: ?>
        <h2 class="text-danger">Failed to delete user. Please try again.</h2>
    <?php endif; ?>

</body>
</html>
