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
    // -- delete comment by id
    // connect db
    $dbhost = 'localhost:3307';
    $dbuser = 'root';
    $dbpassword = '';
    $dbname = 'travel blog';
    $conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
        or die ('Failed to connect to db.');
    
    // delete
    $sql = "DELETE FROM comment WHERE `comment`.`CommentID` = '$id'";
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
<body>
    <?php include("../../inc/_navbar.php") ?>
    <div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
        <h1 class="mb-4 text-center">Delete a selected comment: id= <?php echo $id; ?></h1>
        <?php
        // Hiển thị FullName của comment nếu có
        $dbhost = 'localhost:3307';
        $dbuser = 'root';
        $dbpassword = '';
        $dbname = 'travel blog';
        $conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
        $fullname = '';
        if ($conn && !empty($id)) {
            $sql = "SELECT FullName FROM comment WHERE CommentID = '$id' LIMIT 1";
            $rs = mysqli_query($conn, $sql);
            if ($rs && $row = mysqli_fetch_assoc($rs)) {
                $fullname = $row['FullName'];
            }
            mysqli_close($conn);
        }
        if ($fullname) {
            echo '<h4 class="mb-4 text-center">Full Name: <span class="text-primary">' . htmlspecialchars($fullname) . '</span></h4>';
        }
        ?>
        <?php if (isset($result) && $result): ?>
            <h2 class="text-success text-center">Deleted successfully! You are redirecting to index.php after 3s...</h2>
            <script>
                setTimeout(function() {
                    window.location.href = "index.php";
                }, 3000);
            </script>
        <?php else: ?>
            <div class="card shadow p-4" style="max-width: 400px;">
                <form action="" method="post" enctype="multipart/form-data" class="text-center">
                    <h2 class="text-danger mb-4">Are you sure?</h2>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="index.php" class="btn btn-secondary mr-2">Cancel</a>
                        <button name="action" value="confirm" type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>