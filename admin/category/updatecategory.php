<?php
$id = '';

//--get car id to delete
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

if (!$id) {
    header('Location: indexcategory.php');
}

//connect db
$dbhost = 'localhost';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel_blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
or die ('Failed to connect to db.');

//insert
$sql = "SELECT * FROM category WHERE CategoryID ='$id'";
$_result = mysqli_query($conn, $sql);
$categoryData = mysqli_fetch_assoc($_result);

//close connection
@mysqli_close($conn);

//--redirect if car with id not exist
if (!$categoryData) {
    header('Location: indexcategory.php');
}

//--initial data
$errors = [];
$name = $categoryData['Name'];
$description = $categoryData['Description'];
$image = $categoryData['Image'];
$category = $categoryData['Category'];

$fileImage = '';

if ($_POST) {
 //post data is not empty
    //-- get user data
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
    }
    if (isset($_POST['description'])) {
        $description = $_POST['description'];
    }
    if (isset($_POST['category'])) {
        $category = $_POST['category'];
    }
    if (file_exists($_FILES['fileImage']['tmp_name'])) {
        $fileImage = $_FILES['fileImage']; //assign fileImage = array chứa các nội dung của file imgimg
    }
    
    // -- clean user data
    $name = trim($name);                // strip leading & trailing whitespaces 
    $name = htmlspecialchars($name);    // escape html special characters
    $name = addslashes($name);          // escape sql special characters

    $description = trim($description);                // strip leading & trailing whitespaces 
    $description = htmlspecialchars($description);    // escape html special characters
    $description = addslashes($description);          // escape sql special characters

    $category = trim($category);
    $category = htmlspecialchars($category);
    $category = addslashes($category);

    // required
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } 
    if (empty($description)) {
        $errors['description'] = 'Description is required';
    } 
    if (empty($category)) {
        $errors['category'] = 'Category is required';
    }
    //validation file type
    if ($fileImage) {
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'png', 'jpeg'])) {
            $errors['fileImage'] = 'Invalid file type, expect png, jpg, jpeg';
        }

    //validation file size
    if($fileImage["size"] > 20*1024*1024) {
        $errors['fileImage'] = 'File too large, expect <= 20mb';
    }
}


    
    //--validate user data
    if (empty($errors)) {
        // move uploaded file to /uploads if new file is provided
        if ($fileImage) {
            $image = "../uploads/" . basename($fileImage["name"]);
            move_uploaded_file($fileImage["tmp_name"], $image);
        }

        //--update into db
        //connect db
        $dbhost = 'localhost:3307';
        $dbuser = 'root';
        $dbpassword = '';
        $dbname = 'travel_blog';

        $conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
        or die ('Failed to connect to db.');

        //insert
        $sql = "UPDATE `category`
                SET    `Name` = '$name',
                       `Category` = '$category',
                       `Description` = '$description', 
                       `Image` = '$image'
                WHERE `category`.`CategoryID` = '$id'";

        $result = mysqli_query($conn, $sql);

        //close connection
        @mysqli_close($conn);
        }
    }
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
        <?php include("../inc/_navbar.php"); ?>

    <h1>Update a selected category</h1>
    <?php if (isset($result) && $result): ?>
        <h2 class="text-success">Updated successfully! Your are redirecting to index.php after 3s...</h2>
        <script>
            setTimeout(function() {
                window.location.href = "indexcategory.php";
            }, 3000);
        </script>
    <?php else: ?>

    <!-- filter -->
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
                <label for="name" class="form-label">Name</label>
                <input 
                    type="text" id="name" name="name" class="form-control" 
                    value="<?php echo $name; ?>"
                >
                <?php if (isset($errors['name'])): ?>
                    <span class="text-danger"><?php echo $errors['name']; ?></span>
                <?php endif; ?>
        </div>

        <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <input 
                    type="text" id="description" name="description" class="form-control" 
                    value="<?php echo $description; ?>"
                >
                <?php if (isset($errors['description'])): ?>
                    <span class="text-danger"><?php echo $errors['description']; ?></span>
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

        <!-- file-->
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