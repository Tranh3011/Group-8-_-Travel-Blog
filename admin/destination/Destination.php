<?php
// Destination.php
// Kết nối tới cơ sở dữ liệu
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "travel_blog";

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
    <title>Mountain Travel Destinations</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f6f8;
        color: #2c3e50;
    }

    

    /* Container and title */
    .container {
        max-width: 2000px;
        margin: auto;
        padding: 20px;
    }

    h1 {
        text-align: center;
        color: #f1c40f;
        margin-bottom: 20px;
        font-size: 32px;
    }

    /* Add New Destination Button */
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

    /* Destination card */
    .destination {
        border: 1px solid #ccc;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        background-color: #ffffff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
    }

    .destination img {
        max-width: 100%;
        border-radius: 5px;
        margin-bottom: 10px;
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

    </style>
</head>
<body>
  

    <div class="container">
        <?php include("../inc/_navbar.php"); ?>

        <h1>Travel Destinations</h1>

        <a href="Create.php">Add New Destination</a>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="destination">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" width="600" height = "350" alt="<?php echo htmlspecialchars($row['Name']); ?>">
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
