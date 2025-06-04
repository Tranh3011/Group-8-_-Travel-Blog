<?php
//--initial data
$errors = [];
$name = '';
$category = '';
$description = '';
$fileImage = '';

if ($_POST) { // post data is not empty
    //-- get user data
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
    }
    if (isset($_POST['category'])) {
        $category = $_POST['category'];
    }
    if (isset($_POST['description'])) {
        $description = $_POST['description'];
    }
    if (isset($_FILES['fileImage']) && $_FILES['fileImage']['error'] == 0) {
        $fileImage = $_FILES['fileImage'];
    }

    // -- clean user data
    $name = trim($name);
    $name = htmlspecialchars($name);
    $name = addslashes($name);

    $category = trim($category);
    $category = htmlspecialchars($category);
    $category = addslashes($category);

    $description = trim($description);
    $description = htmlspecialchars($description);
    $description = addslashes($description);

    // required
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    if (empty($category)) {
        $errors['category'] = 'Category is required';
    }
    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }

    // validation file type
    if ($fileImage && is_array($fileImage) && isset($fileImage['name']) && $fileImage['name'] !== '') {
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'png', 'jpeg'])) {
            $errors['fileImage'] = 'Invalid file type, expect png, jpg, jpeg';
        }
        if ($fileImage["size"] > 20 * 1024 * 1024) {
            $errors['fileImage'] = 'File too large, expect <= 20mb';
        }
    } else {
        $fileImage = null;
    }

    //--validate user data
    if (empty($errors)) {
        if ($fileImage && isset($fileImage['tmp_name']) && $fileImage['tmp_name'] !== '') {
            $uploadDir = realpath(__DIR__ . '/../../uploads');
            if ($uploadDir === false) {
                $uploadDir = __DIR__ . '/../../uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
            }
            $imageName = uniqid('cat_', true) . '_' . basename($fileImage["name"]);
            $image = "uploads/" . $imageName;
            move_uploaded_file($fileImage["tmp_name"], $uploadDir . "/" . $imageName);
        } else {
            $image = "";
        }

        //connect db
        $dbhost = 'localhost:3307';
        $dbuser = 'root';
        $dbpassword = '';
        $dbname = 'travel blog';

        $conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
        if (!$conn) {
            $errors['db'] = 'Failed to connect to db: ' . mysqli_connect_error();
        } else {
            // insert using prepared statement (CategoryID tự động tăng)
            $sql = "INSERT INTO `category` (`Name`, `Category`, `Description`, `Created_at`, `Updated_at`, `Image`)
                    VALUES (?, ?, ?, NOW(), NOW(), ?)";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssss", $name, $category, $description, $image);
                $result = mysqli_stmt_execute($stmt);
                if (!$result) {
                    $errors['db'] = 'Failed to execute query: ' . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $errors['db'] = 'Database error: ' . mysqli_error($conn);
            }
            mysqli_close($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Category</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include("../../inc/_navbar.php"); ?>
<div class="container">
    <h1>Create a new category</h1>
    <?php if (isset($result) && $result): ?>
        <h2 class="text-success">Inserted successfully! You are redirecting to index.php after 3s...</h2>
        <script>
            setTimeout(function() {
                window.location.href = "indexcategory.php";
            }, 3000);
        </script>
    <?php else: ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" 
                value="<?php echo $name; ?>">

            <?php if (isset($errors['name'])): ?>
                <span class="text-danger"><?php echo $errors['name']; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="category" class="form-label">Category</label>
            <input type="text" id="category" name="category" class="form-control" 
                value="<?php echo $category; ?>">

            <?php if (isset($errors['category'])): ?>
                <span class="text-danger"><?php echo $errors['category']; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <input type="text" id="description" name="description" class="form-control" 
                value="<?php echo $description; ?>">

            <?php if (isset($errors['description'])): ?>
                <span class="text-danger"><?php echo $errors['description']; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="fileImage" class="form-label">File image</label>
            <input type="file" id="fileImage" name="fileImage" class="form-control">

            <?php if (isset($errors['fileImage'])): ?>
                <span class="text-danger"><?php echo $errors['fileImage']; ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <?php endif; ?>
</body>
</html>