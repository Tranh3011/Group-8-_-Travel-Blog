<?php
// Destination.php
// Kết nối tới cơ sở dữ liệu
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "travel blog";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Lấy danh sách điểm đến
$sql = "SELECT * FROM destination ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Let's Travel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f6f8;
            color: #2c3e50;
        }
        /* .container {
            max-width: 1100px;
            margin: auto;
            padding: 20px;
        } */
        h1 {
            text-align: center;
            color: #0a1f44;
            margin-bottom: 20px;
            font-size: 32px;
        }
        a[href="Create.php"] {
            text-decoration: none;
            color: #0a1f44;
            background-color: #f1c40f;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 25px;
            transition: background-color 0.3s ease;
        }
        a[href="Create.php"]:hover {
            background-color: #d4ac0d;
            color: white;
        }
        .destination {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        .destination img {
            max-width: 300px;
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .destination-card {
            flex: 1;
        }
        .destination-card h2 {
            color: #2c3e50;
        }
        .actions {
            margin-top: 10px;
        }
        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            padding: 6px 12px;
            border-radius: 5px;
            transition: opacity 0.3s ease;
        }
        .actions a.delete {
            background-color: #dc3545;
        }
        .actions a:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
        <?php include("../../inc/_navbar.php"); ?>
          <div class="container">
        <h1><strong>Travel Destinations</h1>
        <a href="Create.php">Add New Destination</a>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="destination">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['Name']); ?>">
                    <div class="destination-card">
                        <h2><?php echo htmlspecialchars($row['Name']); ?></h2>
                        <p><?php echo htmlspecialchars($row['Description']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($row['Location']); ?></p>
                        <div class="actions">
                            <a href="Update.php?id=<?php echo $row['DestinationID']; ?>">Update</a>
                            <a href="Delete.php?id=<?php echo $row['DestinationID']; ?>" class="delete">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No destinations found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close();
?>
