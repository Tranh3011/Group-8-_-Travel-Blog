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
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 48px auto 0 auto;
            padding: 0 16px;
        }
        h1 {
            text-align: center;
            color: #2d3e50;
            margin-bottom: 32px;
            font-size: 2.2rem;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        form {
            width: 100%;
        }
        .input-group {
            margin-bottom: 22px;
            width: 100%;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #34495e;
            font-size: 1.08rem;
        }
        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #dfe4ea;
            border-radius: 6px;
            font-size: 1.08rem;
            background: #f9fafb;
            font-weight: bold;
            box-sizing: border-box;
        }
        .input-group input:focus,
        .input-group textarea:focus {
            border-color: #2980b9;
            outline: none;
        }
        .button-group {
            text-align: center;
            margin-top: 28px;
        }
        .button-group button {
            background-color: #2980b9;
            color: #fff;
            font-weight: bold;
            padding: 13px 48px;
            border: none;
            border-radius: 6px;
            font-size: 1.08rem;
            cursor: pointer;
            transition: background 0.2s;
            letter-spacing: 0.5px;
        }
        .button-group button:hover {
            background-color: #1a5d8f;
        }
        .error {
            color: red;
            font-size: 0.95em;
            margin-top: 4px;
        }
        .current-image {
            margin-bottom: 12px;
        }
        .current-image a {
            color: #2980b9;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include("../../inc/_navbar.php"); ?>
    <div class="container">
        <h1>Update Destination</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="Name">Destination Name:</label>
                <input type="text" id="Name" name="Name" value="<?php echo htmlspecialchars($name); ?>" required>
                <?php if (isset($errors['Name'])) echo "<div class='error'>{$errors['Name']}</div>"; ?>
            </div>
            <div class="input-group">
                <label for="Description">Describe:</label>
                <textarea id="Description" name="Description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
                <?php if (isset($errors['Description'])) echo "<div class='error'>{$errors['Description']}</div>"; ?>
            </div>
            <div class="input-group">
                <label for="Location">Location:</label>
                <input type="text" id="Location" name="Location" value="<?php echo htmlspecialchars($location); ?>" required>
                <?php if (isset($errors['Location'])) echo "<div class='error'>{$errors['Location']}</div>"; ?>
            </div>
            <div class="input-group">
                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*">
                <div class="current-image">
                    Current image: <a href="<?php echo htmlspecialchars($image); ?>" target="_blank">View Image</a>
                </div>
                <?php if (isset($errors['image'])) echo "<div class='error'>{$errors['image']}</div>"; ?>
            </div>
            <div class="button-group">
                <button type="submit">Update</button>
                <a href="Destination.php" class="btn btn-secondary" style="margin-left: 10px; background: #ccc; color: #333; border-radius: 6px; padding: 13px 30px; text-decoration: none;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
