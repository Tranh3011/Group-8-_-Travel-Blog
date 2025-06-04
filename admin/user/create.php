<?php
// PHPdeveloperbot speaking

//--initial data
$errors = [];
$firstName = '';
$lastName = '';
$email = '';
$phoneNumber = '';
$password = '';
$city = '';
$country = '';
$fileAvatar = '';
$avatar = ''; // add this line to initialize $avatar
$result = false; // always initialize $result

if ($_POST) { // post data is not empty
    //-- get user data
    if (isset($_POST['firstName'])) {
        $firstName = $_POST['firstName'];
    }
    if (isset($_POST['lastName'])) {
        $lastName = $_POST['lastName'];
    }
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
    }
    if (isset($_POST['phoneNumber'])) {
        $phoneNumber = $_POST['phoneNumber'];
    }
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }
    if (isset($_POST['city'])) {
        $city = $_POST['city'];
    }
    if (isset($_POST['country'])) {
        $country = $_POST['country'];
    }
    if (
        isset($_FILES['fileAvatar']) &&
        isset($_FILES['fileAvatar']['tmp_name']) &&
        is_uploaded_file($_FILES['fileAvatar']['tmp_name'])
    ) {
        $fileAvatar = $_FILES['fileAvatar'];
    }

    // -- clean user data
    $firstName = trim($firstName);                // strip leading & trailing whitespaces 
    $firstName = htmlspecialchars($firstName);    // escape html special characters
    $firstName = addslashes($firstName);          // escape sql special characters

    $lastName = trim($lastName);                // strip leading & trailing whitespaces 
    $lastName = htmlspecialchars($lastName);    // escape html special characters
    $lastName = addslashes($lastName);          // escape sql special characters

    $email = trim($email);                // strip leading & trailing whitespaces 
    $email = htmlspecialchars($email);    // escape html special characters
    $email = addslashes($email);          // escape sql special characters

    $password = trim($password);                // strip leading & trailing whitespaces 
    $password = htmlspecialchars($password);    // escape html special characters
    $password = addslashes($password);          // escape sql special characters

    $city = trim($city);                // strip leading & trailing whitespaces 
    $city = htmlspecialchars($city);    // escape html special characters
    $city = addslashes($city);          // escape sql special characters

    $country = trim($country);                // strip leading & trailing whitespaces 
    $country = htmlspecialchars($country);    // escape html special characters
    $country = addslashes($country);          // escape sql special characters

    // required
    if (empty($firstName)) {
        $errors['firstName'] = 'First Name is required';
    }

    if (empty($lastName)) {
        $errors['lastName'] = 'Last Name is required';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }

    // validation file type
    if ($fileAvatar) {
        $fileType = strtolower(pathinfo($fileAvatar['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'png', 'jpeg'])) {
            $errors['fileAvatar'] = 'Invalid file type, expect png, jpg, jpeg';
        }
        // validation file size
        if ($fileAvatar["size"] > 20 * 1024 * 1024) {
            $errors['fileAvatar'] = 'File too large, expect <= 20mb';
        }
    }
    //--validate user data
    if (empty($errors)) {
        if ($fileAvatar) {
            $avatar = "../uploads/" . basename($fileAvatar["name"]);
            if (!move_uploaded_file($fileAvatar["tmp_name"], $avatar)) {
                $errors['fileAvatar'] = "Failed to upload avatar file.";
            }
        } else {
            $avatar = ""; // set avatar to empty string if no file uploaded
        }
        if (empty($errors)) {
            //--insert into db
            //connect db
            $dbhost = 'localhost:3307';
            $dbuser = 'root';
            $dbpassword = '';
            $dbname = 'travel blog';

            $conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
            or die ('Failed to connect to db.');

            //insert
            $sql = "INSERT INTO `user` (`UserID`, `FirstName`, `LastName`, `Email`, `PhoneNumber`, `Password`, `City`, `Country`, `Avatar`)
            VALUES (NULL, '$firstName', '$lastName', '$email', '$phoneNumber', '$password', '$city', '$country', '$avatar')"; 

            $result = mysqli_query($conn, $sql);

            if (!$result) {
                $errors['db'] = "Failed to create user: " . mysqli_error($conn);
            }

            // close connection
            @mysqli_close($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
</head>
<body>
     <?php include("../../inc/_navbar.php"); ?>
     <div class="container">
    <h1>Create a new user</h1>
    <?php if ($result): ?>
        <h2 class="text-success">User created successfully! You are redirecting to index.php after 5s...</h2>
        <script>
            setTimeout(function() {
                window.location.href = "index.php"; // Chuyển hướng đến indexUser.php
            }, 3000);
        </script>
    <?php else: ?>
        <?php if (isset($errors['db'])): ?>
            <div class="alert alert-danger"><?php echo $errors['db']; ?></div>
        <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="firstName" class="form-label">First Name</label>
            <input type="text" id="firstName" name="firstName" class="form-control" 
                value="<?php echo $firstName; ?>">

            <?php if (isset($errors['firstName'])): ?>
                <span class="text-danger"><?php echo $errors['firstName']; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="lastName" class="form-label">Last Name</label>
            <input type="text" id="lastName" name="lastName" class="form-control" 
                value="<?php echo $lastName; ?>">

            <?php if (isset($errors['lastName'])): ?>
                <span class="text-danger"><?php echo $errors['lastName']; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" 
                value="<?php echo $email; ?>">

            <?php if (isset($errors['email'])): ?>
                <span class="text-danger"><?php echo $errors['email']; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phoneNumber" class="form-label">Phone Number</label>
            <input type="text" id="phoneNumber" name="phoneNumber" class="form-control" 
                value="<?php echo $phoneNumber; ?>">
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" 
                value="<?php echo $password; ?>">

            <?php if (isset($errors['password'])): ?>
                <span class="text-danger"><?php echo $errors['password']; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="city" class="form-label">City</label>
            <input type="text" id="city" name="city" class="form-control" 
                value="<?php echo $city; ?>">
        </div>

        <div class="form-group">
            <label for="country" class="form-label">Country</label>
            <input type="text" id="country" name="country" class="form-control" 
                value="<?php echo $country; ?>">
        </div>

        <div class="form-group">
            <label for="fileAvatar" class="form-label">Avatar</label>
            <input type="file" id="fileAvatar" name="fileAvatar" class="form-control">

            <?php if (isset($errors['fileAvatar'])): ?>
                <span class="text-danger"><?php echo $errors['fileAvatar']; ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <?php endif; ?>
</body>
</html>

