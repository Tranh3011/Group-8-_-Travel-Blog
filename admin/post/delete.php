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

if ($_POST) { // post data is not empty
    // -- delete car by id
    // connect db
    $dbhost = 'localhost:3307';
    $dbuser = 'root';
    $dbpassword = '';
    $dbname = 'travel_blog';
    $conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
    or die ('Failed to connect to db.');
    // delete
    $sql = "DELETE FROM posts WHERE `posts`.`PostID` = '$id'";
    $result = mysqli_query($conn, $sql);
    // close connection
    mysqli_close($conn);
    // -- redirect user after 3s
    } // else do nothing
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
<body class="container">
    <?php include("../inc/_navbar.php") ?>
    <h1>Delete a selected car: id= <?php echo $id; ?></h1>
    <?php if (isset($result) && $result): ?>
        <h2 class="text-success">
Deleted successfully! Your are redirecting to index.php after 3s...
</h2>
<script>
setTimeout(function() {
window.location.href = "index.php";
}, 3000);
</script>
<?php else: ?>
<form action="" method="post" enctype="multipart/form-data">
    <h2 class="text-danger">Are you sure?</h2>
    <a href="index.php">Cancel</a>
    <button name="action" value="confirm" type="submit" class="btn btn-danger">Confirm</button>
</form>
<?php endif;?>
</body>
</html>