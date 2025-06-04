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

// -- get post by id
// connect db
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';
$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
or die('Failed to connect to db.');

$sql = "SELECT * FROM posts WHERE PostID = '$id'";
$_result = mysqli_query($conn, $sql);
$post = mysqli_fetch_assoc($_result);
// close connection
mysqli_close($conn);

// redirect if post not exist
if (!$post) {
    header('Location: index.php');
}

$errors = [];
$title = $post['Title'];
$content = $post['Content'];
$image = $post['image'];
$fileImage = '';

// get user data
if ($_POST) {
    if (isset($_POST['title'])) {
        $title = $_POST['title'];
    }
    if (isset($_POST['content'])) {
        $content = $_POST['content'];
    }

    // -- clean data
    $title = trim($title);
    $title = htmlspecialchars($title);
    $title = addslashes($title);

    $content = trim($content);
    $content = htmlspecialchars($content);
    $content = addslashes($content);

    // -- validate user data
    // required
    if (empty($title)) {
        $errors['title'] = 'Title is required';
    }
    if (empty($content)) {
        $errors['content'] = 'Content is required';
    }

    // -- validate file type
    if (file_exists($_FILES['fileImage']['tmp_name'])) {
        $fileImage = $_FILES['fileImage'];
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            $errors['fileImage'] = "Invalid file type, only JPG, JPEG, PNG are allowed.";
        }
        // file size: <= 20Mb
        if ($fileImage["size"] > 20 * 1024 * 1024) {
            $errors['fileImage'] = "File is too large, expect smaller than 20MB";
        }
    }

    if (empty($errors)) {
        // move uploaded file to /uploads if new file is provided
        if ($fileImage) {
            $image = "../uploads/" . basename($fileImage["name"]);
            move_uploaded_file($fileImage["tmp_name"], $image);
        }

        // connect db
        $conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
        if (!$conn) {
            die('Failed to connect to database');
        }

        // update
        $sql = "UPDATE `posts`
                SET `Title` = '$title',
                    `Content` = '$content',
                    `image` = '$image'
                WHERE `PostID` = $id";
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
    <title>Update Post</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include("../../inc/_navbar.php") ?>
    <div class="container">
    <h1><strong>Update Post</h1>
    <?php if (isset($result) && $result): ?>
        <h2 class="text-success">Updated successfully! Redirecting to index.php after 3 seconds...</h2>
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 3000); // Redirect to index.php after 3 seconds
        </script>
    <?php else: ?>
        <form action="" method="post" enctype="multipart/form-data">
            <div>
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo $title; ?>">
                <span class="text-danger"><?php echo isset($errors['title']) ? $errors['title'] : ''; ?></span>
            </div>

            <div>
                <label for="content">Content:</label>
                <textarea name="content" id="content" class="form-control"><?php echo $content; ?></textarea>
                <span class="text-danger"><?php echo isset($errors['content']) ? $errors['content'] : ''; ?></span>
            </div>

            <div>
                <label for="fileImage">Image:</label>
                <input type="file" name="fileImage" id="fileImage">
                <?php if (isset($errors['fileImage'])): ?>
                    <span class="text-danger"><?php echo $errors['fileImage']; ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    <?php endif; ?>
</body>
</html>