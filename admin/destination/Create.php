<?php
// Create.php
// Kết nối cơ sở dữ liệu
$servername = "localhost:3307";
$username = "root";
$password = "";
$database = "travel_blog";

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
            background-color: #2E4053;
            color: #F7F9F9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #F1C40F;
            color: #2E4053;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #2E4053;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #2E4053;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            background-color: #1ABC9C;
            color: #F7F9F9;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #16A085;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Destination</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="Name">Destination Name:</label>
            <input type="text" id="Name" name="Name" required>

            <label for="Description">Describe:</label>
            <textarea id="Description" name="Description" rows="4" required></textarea>

            <label for="Location">Location:</label>
            <input type="text" id="Location" name="Location" required>

            <label for="image">Image:</label>
            <input type="file" id="image" name="image" accept="image/*">

            <button type="submit">Add</button>
        </form>
    </div>
</body>
</html>
