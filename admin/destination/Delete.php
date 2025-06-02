<?php
// Kiểm tra ID từ URL
$id = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

// Nếu ID không tồn tại, chuyển hướng
if (empty($id)) {
    header('Location: Destination.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Kiểm tra nếu là POST request
    // Kết nối cơ sở dữ liệu
    $dbhost = 'localhost:3307';
    $dbuser = 'root';
    $dbpassword = '';
    $dbname = 'travel blog';
    
    $conn = new mysqli($dbhost, $dbuser, $dbpassword, $dbname);

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }

    // Chuẩn bị câu lệnh SQL
    $sql = "DELETE FROM destination WHERE DestinationID = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Gắn giá trị và thực thi câu lệnh
        $stmt->bind_param('i', $id); // 'i' là kiểu dữ liệu integer
        if ($stmt->execute()) {
            // Thành công, chuyển hướng
            header('Location: Destination.php');
            exit();
        } else {
            echo "Lỗi: Không thể xóa dữ liệu.";
        }
        $stmt->close();
    } else {
        echo "Lỗi: Không thể chuẩn bị câu lệnh.";
    }

    // Đóng kết nối
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Destination</title>
</head>
<body>
    <div class="container">
        <h1>Delete Destination</h1>
        <p>Are you sure you want to delete this destination?</p>
        <form action="" method="POST">
            <button type="submit">Confirm Delete</button>
        </form>
        <a href="Destination.php">Cancel</a>
    </div>
</body>
</html>
