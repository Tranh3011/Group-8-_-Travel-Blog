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
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';

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
$category_id = $categoryData['CategoryID'];

if ($_POST) {
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
    }
    if (isset($_POST['description'])) {
        $description = $_POST['description'];
    }

    // -- clean user data
    $name = trim($name);
    $name = htmlspecialchars($name);
    $name = addslashes($name);

    $description = trim($description);
    $description = htmlspecialchars($description);
    $description = addslashes($description);

    // required
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }

    if (empty($errors)) {
        //connect db
        $dbhost = 'localhost:3307';
        $dbuser = 'root';
        $dbpassword = '';
        $dbname = 'travel blog';

        $conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
        or die ('Failed to connect to db.');

        // update only Name and Description (not CategoryID)
        $sql = "UPDATE `category`
                SET `Name` = '$name',
                    `Description` = '$description',
                    `Updated_at` = NOW()
                WHERE `CategoryID` = '$id'";

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
        <title>Document</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include("../../inc/_navbar.php"); ?>
<div class="container">
    <h1>Update a selected category</h1>
    <?php if (isset($result) && $result): ?>
        <h2 class="text-success">Updated successfully! Your are redirecting to index.php after 3s...</h2>
        <script>
            setTimeout(function() {
                window.location.href = "indexcategory.php";
            }, 3000);
        </script>
    <?php else: ?>
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
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>