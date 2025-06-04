<?php

// initial values

$full_name = '';
$post_id = '';
$content = '';
$create_at = '';

$errors = []; // [input => error message]

if ($_POST) {
    // -- get user data
    if (isset($_POST['FullName'])) {
        $full_name = $_POST['FullName'];
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
    if (empty($full_name)) {
        $errors['FullName'] = 'Full name is required!';
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

        // Lấy UserID từ FullName
        $user_id = null;
        $user_email = '';
        $stmt_user = mysqli_prepare($conn, "SELECT UserID, Email FROM user WHERE FullName = ?");
        if ($stmt_user) {
            mysqli_stmt_bind_param($stmt_user, "s", $full_name);
            mysqli_stmt_execute($stmt_user);
            mysqli_stmt_bind_result($stmt_user, $user_id, $user_email);
            mysqli_stmt_fetch($stmt_user);
            mysqli_stmt_close($stmt_user);
        }
        if (!$user_id) {
            $errors['FullName'] = 'Full name not found in user table!';
        } else {
            // insert into db using prepared statement
            $sql = "INSERT INTO `comment` (`PostID`, `Content`, `Created_at`) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "iss", $post_id, $content, $create_at);
                $result = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } else {
                $result = false;
                $errors['db'] = 'Database error: ' . mysqli_error($conn);
            }
            // Nếu muốn lấy thêm thông tin từ FullName, có thể sử dụng $user_email hoặc các trường khác ở đây
        }
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
<body>
    <?php include('../../inc/_navbar.php'); ?>
<div class="container">
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
            <label class="form-label" for="FullName">Full Name</label>
            <input class="form-control" type="text" name="FullName" id="FullName" value="<?php echo $full_name; ?>">

            <?php if (isset($errors['FullName'])): ?>
                <p class="text-danger"><?php echo htmlspecialchars($errors['FullName']); ?></p>
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

        <?php if (isset($errors['db'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errors['db']); ?></div>
        <?php endif; ?>

        <button class="btn btn-primary">Save</button>
    </form>
    <?php endif;?>
</body>
</html>