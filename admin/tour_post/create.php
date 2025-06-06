<?php
//--initial data
$errors = [];
$title = '';
$category = '';
$destination = '';
$description = '';
$fileImage = '';

if ($_POST) { // post data is not empty
    //-- get user data
    if (isset($_POST['title'])) {
        $title = $_POST['title'];
    }
    if (isset($_POST['category'])) {
        $category = $_POST['category'];
    }
    if (isset($_POST['destination'])) {
        $destination = $_POST['destination'];
    }    
    if (isset($_POST['description'])) {
        $description = $_POST['description'];
    }
    if (file_exists($_FILES['fileImage']['tmp_name'])) {
        $fileImage = $_FILES['fileImage']; // assign fileImage = array chứa các nội dung của file img
    }

    // -- clean user data
    $title = trim($title);                // strip leading & trailing whitespaces 
    $title = htmlspecialchars($title);    // escape html special characters
    $title = addslashes($title);          // escape sql special characters

    $category = trim($category);                // strip leading & trailing whitespaces 
    $category = htmlspecialchars($category);    // escape html special characters
    $category = addslashes($category);          // escape sql special characters

    $destination = trim($destination);                // strip leading & trailing whitespaces 
    $destination = htmlspecialchars($destination);    // escape html special characters
    $destination = addslashes($destination);          // escape sql special characters

    $description = trim($description);                // strip leading & trailing whitespaces 
    $description = htmlspecialchars($description);    // escape html special characters
    $description = addslashes($description);          // escape sql special characters

    // required
    if (empty($title)) {
        $errors['title'] = 'Title is required';
    }

    if (empty($category)) {
        $errors['category'] = 'Category is required';
    }
    if (empty($destination)) {
        $errors['destination'] = 'Destination is required';
    }    

    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }

    // validation file type
    if ($fileImage) {
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'png', 'jpeg'])) {
            $errors['fileImage'] = 'Invalid file type, expect png, jpg, jpeg';
        }
        // validation file size
        if ($fileImage["size"] > 20 * 1024 * 1024) {
            $errors['fileImage'] = 'File too large, expect <= 20mb';
        }
    }
    
    //--validate user data
    if (empty($errors)) {
        if ($fileImage) {
        $image = "../../uploads/" . basename($fileImage["name"]);
        move_uploaded_file($fileImage["tmp_name"], $image);
        }
        
        //--insert into db
        //connect db
        $dbhost = 'localhost:3307';
        $dbuser = 'root';
        $dbpassword = '';
        $dbname = 'travel_blog';

        $conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
        or die ('Failed to connect to db.');

        //insert
        $sql = "INSERT INTO `tour_posts` (`id`, `title`, `category`, `Destination`, `Description`, `Created_at`, `Updated_at`, `Image`)
        VALUES (NULL, '$title', '$category', '$destination', '$description', NOW(), NOW(), '$image')"; 

        $result = mysqli_query($conn, $sql);

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
    <title>Create Tour Post</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
</head>
<body class="container">
    <?php include("../../inc/_navbar.php"); ?>

    <h1>Create a new tour post</h1>
    <?php if (isset($result) && $result): ?>
        <h2 class="text-success">Inserted successfully! You are redirecting to index.php after 3s...</h2>
        <script>
            setTimeout(function() {
                window.location.href = "index.php";
            }, 3000);
        </script>
    <?php else: ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title" class="form-label">Title</label>
            <input type="text" id="title" name="title" class="form-control" 
                value="<?php echo $title; ?>">

            <?php if (isset($errors['title'])): ?>
                <span class="text-danger"><?php echo $errors['title']; ?></span>
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
            <label for="destination" class="form-label">Destination</label>
            <input type="text" id="destination" name="destination" class="form-control" 
                value="<?php echo $destination; ?>">

            <?php if (isset($errors['destination'])): ?>
                <span class="text-danger"><?php echo $errors['destination']; ?></span>
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