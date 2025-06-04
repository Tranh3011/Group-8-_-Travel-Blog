<?php
// Create.php
// Kết nối cơ sở dữ liệu
$servername = "localhost:3307";
$username = "root";
$password = "";
$database = "travel blog";

$conn = new mysqli($servername, $username, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['Name'];
    $description = $_POST['Description'];
    $location = $_POST['Location'];
    $image = "";

    // Xử lý tải ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetFile = $targetDir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $targetFile;
        } else {
            echo "Error uploading the image.";
        }
    }

    // Thêm thông tin vào cơ sở dữ liệu
    $sql = "INSERT INTO destination (Name, Description, Location, image, Created_at, Updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $description, $location, $image);

    if ($stmt->execute()) {
        header("Location: Destination.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Destination</title>
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
    </style>
</head>
<body>
    <?php include("../../inc/_navbar.php"); ?>
    <div class="container">
        <h1>Add New Destination</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="Name">Destination Name:</label>
                <input type="text" id="Name" name="Name" required>
            </div>
            <div class="input-group">
                <label for="Description">Describe:</label>
                <textarea id="Description" name="Description" rows="4" required></textarea>
            </div>
            <div class="input-group">
                <label for="Location">Location:</label>
                <input type="text" id="Location" name="Location" required>
            </div>
            <div class="input-group">
                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            <div class="button-group">
                <button type="submit">Add</button>
            </div>
        </form>
    </div>
</body>
</html>
