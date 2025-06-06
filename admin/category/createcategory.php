<?php
//--initial data
$errors = [];
$name = '';
$description = '';
// $fileImage = '';
// $image = '';

if ($_POST) { // post data is not empty
    //-- get user data
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
            $sql = "INSERT INTO `category` (`Name`, `Description`, `Created_at`, `Updated_at`)
                    VALUES (?, ?, NOW(), NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $name, $description);
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
            <label for="description" class="form-label">Description</label>
            <input type="text" id="description" name="description" class="form-control" 
                value="<?php echo $description; ?>">

            <?php if (isset($errors['description'])): ?>
                <span class="text-danger"><?php echo $errors['description']; ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <hr>
    <!-- Hiển thị danh sách category mới nhất -->
    <?php
    $dbhost = 'localhost:3307';
    $dbuser = 'root';
    $dbpassword = '';
    $dbname = 'travel blog';
    $conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
    if ($conn) {
        $sql = "SELECT CategoryID, Name, Category, Description, Created_at FROM category ORDER BY CategoryID DESC LIMIT 10";
        $rs = mysqli_query($conn, $sql);
        if ($rs && mysqli_num_rows($rs) > 0) {
            echo '<h2 class="mt-5">Latest Categories</h2>';
            echo '<table class="table table-bordered mt-2">';
            echo '<thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Description</th><th>Created at</th></tr></thead><tbody>';
            while ($row = mysqli_fetch_assoc($rs)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['CategoryID']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Category']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Description']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Created_at']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        mysqli_close($conn);
    }
    ?>
    <?php endif; ?>
</div>
</body>
</html>