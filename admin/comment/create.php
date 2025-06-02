<?php

// initial values

$user_id = '';
$post_id = '';
$content = '';
$create_at = '';

$errors = []; // [input => error message]

if ($_POST) {
    // -- get user data
    if (isset($_POST['UserID'])) {
        $user_id = $_POST['UserID'];
    }

    if (isset($_POST['PostID'])) {
        $post_id = $_POST['PostID'];
    }
    if (isset($_POST['Content'])) {
        $content = $_POST['Content'];
    }
    if (isset($_POST['Created_at'])) {
        $create_at = $_POST['Created_at'];
    }

    // -- clean data
    $content = trim($content);
    $content = htmlspecialchars($content);
    $content = addslashes($content);

    

    // -- validate data
    if (empty($user_id)) {
        $errors['UserID'] = 'user_id is required!';
    }

    if (empty($post_id)) {
        $errors['PostID'] = 'post_id is required!';
    }

    if (empty($content)) {
        $errors['Content'] = 'comment is required!';
    }
    if (empty($create_at)) {
        $errors['Created_at'] = 'create_at is required!';
    }

    // TODO: validate more...

    // if no errors
    if (empty($errors)) {
        $dbhost = 'localhost:3307';
        $dbuser = 'root';
        $dbpassword = '';
        $dbname = 'travel blog';
        $conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
        if (!$conn) {
            die('failed to connect to database');
        }


        // insert into db using prepared statement
        $sql = "INSERT INTO `comment` ( `CommentID`,`UserID`, `PostID`, `Content`, `Created_at`) 
                VALUES (NULL, NULL, NULL, '$content', '$create_at') ";

        $result = @mysqli_query($conn, $sql);
        // expected always successful

        // close connection
        @mysqli_close($conn);

        
    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments</title>

    <!-- bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<br>
<br>
<br>
<body class="container">
    <?php include('../inc/_navbar.php'); ?>

    <h1>Create a new Comment</h1>
    <?php if (isset($result) && $result): ?>
    <h2 class="text-success">Inserted successfully!</h2>
    <script>
        setTimeout(function() {
        window.location.href = 'index.php';
        }, 3000); // Redirect to index.php after 3 seconds
    </script>
    <?php else: ?>
    <form action="" method="post" enctype="multipart/form-data">

        <div class="form-group">
            <label class="form-label" for="UserID">UserID</label>
            <input class="form-control" type="text" name="UserID" id="UserID" value="<?php echo $user_id; ?>">

            <?php if (isset($errors['UserID'])): ?>
                <p class="text-danger"><?php echo htmlspecialchars($errors['UserID']); ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="PostID">PostID</label>
            <input class="form-control" type="text" name="PostID" id="PostID" value="<?php echo $post_id; ?>">

            <?php if (isset($errors['PostID'])): ?>
                <p class="text-danger"><?php echo htmlspecialchars($errors['PostID']); ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="Content">Comment</label>
            <input class="form-control" type="text" name="Content" id="Content" value="<?php echo $content; ?>">
            
            <?php if (isset($errors['Content'])): ?>
                <p class="text-danger"><?php echo htmlspecialchars($errors['Content']); ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="Created_at">Create_at</label>
            <input class="form-control" type="datetime-local" name="Created_at" id="Created_at" value="<?php echo $create_at; ?>">

            <?php if (isset($errors['Created_at'])): ?>
                <p class="text-danger"><?php echo htmlspecialchars($errors['Created_at']); ?></p>
            <?php endif; ?>
        </div>
        <br/>

        <button class="btn btn-primary">Save</button>
    </form>
    <?php endif;?>
</body>

</html>