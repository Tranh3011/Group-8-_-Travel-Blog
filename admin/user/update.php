create.php
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

// connect db
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname) 
    or die ('Failed to connect to db.');

// Get the user data
$sql = "SELECT * FROM user WHERE UserID = '$id'";
$_result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($_result);

// redirect if user not exist
if (!$user) {
    header('Location: indexUser.php');
}
// close connection
mysqli_close($conn);

// -- initial data
$errors = [];
$firstName = $user['FirstName'];
$lastName = $user['LastName'];
$email = $user['Email'];
$phoneNumber = $user['PhoneNumber'];
$city = $user['City'];
$country = $user['Country'];
$avatar = $user['Avatar'];
$fileAvatar = '';

// Process POST data
if ($_POST) {
    // -- get user data
    if (isset($_POST['FirstName'])) {
        $firstName = $_POST['FirstName'];
    }
    if (isset($_POST['LastName'])) {
        $lastName = $_POST['LastName'];
    }
    if (isset($_POST['Email'])) {
        $email = $_POST['Email'];
    }
    if (isset($_POST['PhoneNumber'])) {
        $phoneNumber = $_POST['PhoneNumber'];
    }
    if (isset($_POST['City'])) {
        $city = $_POST['City'];
    }
    if (isset($_POST['Country'])) {
        $country = $_POST['Country'];
    }
    if (isset($_FILES['fileAvatar'])) {
        $fileAvatar = $_FILES['fileAvatar'];
    }

    // -- clean user data
    $firstName = trim($firstName);
    $firstName = htmlspecialchars($firstName);
    $firstName = addslashes($firstName);

    // -- validate user data
    if (empty($firstName)) {
        $errors['FirstName'] = 'First Name is required';
    }

    if (empty($email)) {
        $errors['Email'] = 'Email is required';
    }

    // File validation
    if (isset($_FILES['fileAvatar']) && $_FILES['fileAvatar']['error'] == 0) {
        $fileAvatar = $_FILES['fileAvatar'];
        $fileType = strtolower(pathinfo($fileAvatar['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            $errors['fileAvatar'] = "Invalid file type, only JPG, JPEG, PNG are allowed.";
        }
    }

    if (empty($errors)) { // if no errors
        $conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
            or die ('Failed to connect to db.');

        // Handle file upload
        if (!empty($fileAvatar)) {
            $targetDir = "../uploads/";
            $targetFile = $targetDir . basename($fileAvatar["name"]);
            move_uploaded_file($fileAvatar["tmp_name"], $targetFile);
            $avatar = $targetFile;
        }

        // Update user data in the database
        $sql = "UPDATE `user`
                SET `FirstName` = '$firstName',
                    `LastName` = '$lastName',
                    `Email` = '$email',
                    `PhoneNumber` = '$phoneNumber',
                    `City` = '$city',
                    `Country` = '$country',
                    `Avatar` = '$avatar'
                WHERE `UserID` = $id";
        $result = @mysqli_query($conn, $sql);

        // close connection
        @mysqli_close($conn);

        // -- redirect user after successful update
        if ($result) {
            echo "<script>
                    alert('User updated successfully!');
                    window.location.href = 'index.php';
                  </script>";
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container">
     <?php include("../../inc/_navbar.php"); ?>

    <h1>Update User Information</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="FirstName" class="form-label">First Name</label>
            <input type="text" id="FirstName" name="FirstName" class="form-control" value="<?php echo $firstName; ?>">
        </div>

        <div class="form-group">
            <label for="LastName" class="form-label">Last Name</label>
            <input type="text" id="LastName" name="LastName" class="form-control" value="<?php echo $lastName; ?>">
        </div>

        <div class="form-group">
            <label for="Email" class="form-label">Email</label>
            <input type="email" id="Email" name="Email" class="form-control" value="<?php echo $email; ?>">
        </div>

        <div class="form-group">
            <label for="PhoneNumber" class="form-label">Phone Number</label>
            <input type="text" id="PhoneNumber" name="PhoneNumber" class="form-control" value="<?php echo $phoneNumber; ?>">
        </div>

        <div class="form-group">
            <label for="City" class="form-label">City</label>
            <input type="text" id="City" name="City" class="form-control" value="<?php echo $city; ?>">
        </div>

        <div class="form-group">
            <label for="Country" class="form-label">Country</label>
            <input type="text" id="Country" name="Country" class="form-control" value="<?php echo $country; ?>">
        </div>

        <div class="form-group">
            <label for="fileAvatar" class="form-label">Avatar</label>
            <input type="file" id="fileAvatar" name="fileAvatar" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>

</body>
</html>
