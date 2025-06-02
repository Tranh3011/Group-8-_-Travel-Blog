<?php
$errors = [];
$title = '';
$content = '';
$fileImage = '';

// get user data
if ($_POST) {
    if (isset($_POST['title'])) {
        $title = $_POST['title'];
    }
    if (isset($_POST['content'])) {
        $content = $_POST['content'];
    }
    if (file_exists($_FILES['fileImage']['tmp_name'])) {
        $fileImage = $_FILES['fileImage'];
    }

    // -- clean data
    $title = trim($title);
    $title = htmlspecialchars($title);
    $title = addslashes($title);

    $content = trim($content);
    $content = htmlspecialchars($content);
    $content = addslashes($content);

    // -- validate user data
    if (empty($title)) {
        $errors['title'] = 'Title is required';
    }
    if (empty($content)) {
        $errors['content'] = 'Content is required';
    }

    // -- validate file type
    if ($fileImage) {
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            $errors['fileImage'] = "Invalid file type, only JPG, JPEG, PNG are allowed.";
        }
        if ($fileImage["size"] > 20 * 1024 * 1024) {
            $errors['fileImage'] = "File is too large, expect smaller than 20MB";
        }
    }

    if(empty($errors)) {
        // move uploaded file to /uploads
            $image = "../uploads/" . basename($fileImage["name"]);
            move_uploaded_file($fileImage["tmp_name"], $image);

            $dbhost = 'localhost:3307';
            $dbuser = 'root';
            $dbpassword = '';
            $dbname = 'travel blog';

            $conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
            if (!$conn) {
                die('Failed to connect to database');
            }

            // Cập nhật câu lệnh SQL để sử dụng cả ImageURL và image
            $sql = "INSERT INTO `posts` (`PostID`, `Title`, `Content`, `Created_at`, `Updated_at`, `image`) 
                    VALUES (NULL, '$title', '$content', NOW(), NOW(), '$image')";
            $result = mysqli_query($conn, $sql);
            @mysqli_close($conn);

        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a New Post</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
</head>
<body class="container">
    <?php include("../inc/_navbar.php") ?>
    <h1>Create a New Post</h1>
    <?php if (isset($result) && $result): ?>
        <h2 class="text-success">Inserted successfully! Your are redirecting to index.php after 3s...</h2>
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 3000); // Redirect to index.php after 3 seconds
        </script>
    <?php else: ?>
        <form action="" method="post" enctype="multipart/form-data">
            <div>
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo isset($title) ? $title : ''; ?>">
                <span class="text-danger"><?php echo isset($errors['title']) ? $errors['title'] : ''; ?></span>
            </div>

            <div>
                <label for="content">Content:</label>
                <textarea name="content" id="content" class="form-control"><?php echo isset($content) ? $content : ''; ?></textarea>
                <span class="text-danger"><?php echo isset($errors['content']) ? $errors['content'] : ''; ?></span>
            </div>

            <div>
                <label for="fileImage">Image:</label>
                <input type="file" name="fileImage" id="fileImage" class="form-control">
                <?php if (isset($errors['fileImage'])): ?>
                    <span class="text-danger"><?php echo $errors['fileImage']; ?></span>
                <?php endif; ?>
            </div>
<br>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    <?php endif; ?>
</body>
</html>