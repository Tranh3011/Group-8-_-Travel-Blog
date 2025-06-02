<?php
// Update.php
// Kết nối cơ sở dữ liệu
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname) or die('Failed to connect to database.');

// Lấy ID từ URL
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: Destination.php');
    exit();
}

// Lấy thông tin hiện tại của điểm đến
$sql = "SELECT * FROM destination WHERE DestinationID = '$id'";
$result_des = mysqli_query($conn, $sql);
$destination = mysqli_fetch_assoc($result_des);

// Kiểm tra nếu không tìm thấy
if (!$destination) {
    header('Location: Destination.php');
    exit();
}

$errors = [];
$name = $destination['Name'];
$description = $destination['Description'];
$location = $destination['Location'];
$image = $destination['image'];

// Kiểm tra nếu có POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['Name'] ?? '');
    $description = trim($_POST['Description'] ?? '');
    $location = trim($_POST['Location'] ?? '');

    // Kiểm tra dữ liệu nhập
    if (empty($name)) {
        $errors['Name'] = 'Destination name is required';
    }
    if (empty($description)) {
        $errors['Description'] = 'Description is required';
    }
    if (empty($location)) {
        $errors['Location'] = 'Location is required';
    }

    // Xử lý ảnh nếu có tải lên
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetFile = $targetDir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $targetFile;
        } else {
            $errors['image'] = 'Error uploading the image';
        }
    }

    // Nếu không có lỗi, cập nhật cơ sở dữ liệu
    if (empty($errors)) {
        $name = htmlspecialchars(addslashes($name));
        $description = htmlspecialchars(addslashes($description));
        $location = htmlspecialchars(addslashes($location));

        $sql = "UPDATE destination 
                SET Name = '$name', 
                    Description = '$description', 
                    Location = '$location', 
                    image = '$image', 
                    Updated_at = NOW() 
                WHERE DestinationID = '$id'";
        $updateResult = mysqli_query($conn, $sql);

        if ($updateResult) {
            header('Location: Destination.php');
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Destination</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #003366;
            color: #FFF8E1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #FFF8E1;
            color: #333;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #003366;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #003366;
            border-radius: 5px;
        }
        button {
            background-color: #228B22;
            color: #FFF8E1;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #003366;
            color: #FFF8E1;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Destination</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="name">Destination Name:</label>
            <input type="text" id="Name" name="Name" value="<?php echo htmlspecialchars($name); ?>" required>
            <?php if (isset($errors['Name'])) echo "<p class='error'>{$errors['Name']}</p>"; ?>

            <label for="description">Describe:</label>
            <textarea id="Description" name="Description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
            <?php if (isset($errors['Description'])) echo "<p class='error'>{$errors['Description']}</p>"; ?>

            <label for="location">Location:</label>
            <input type="text" id="Location" name="Location" value="<?php echo htmlspecialchars($location); ?>" required>
            <?php if (isset($errors['Location'])) echo "<p class='error'>{$errors['Location']}</p>"; ?>

            <label for="image">Image:</label>
            <input type="file" id="image" name="image" accept="image/*">
            <p>Current image: <a href="<?php echo htmlspecialchars($image); ?>" target="_blank">View Image</a></p>
            <?php if (isset($errors['image'])) echo "<p class='error'>{$errors['image']}</p>"; ?>

            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>
