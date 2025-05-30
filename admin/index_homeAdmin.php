<?php 
// Database connection 
$host = 'localhost:3307';
$username = 'root';
$password = '';
$dbname = 'travel_blog';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



$users_no = $conn->query("SELECT * FROM user ") or die('query failed');
$usercount = mysqli_num_rows( $users_no );
$admin_no = $conn->query("SELECT * FROM user WHERE user_type='admin' ") or die('query failed');
$admin_count = mysqli_num_rows( $admin_no );
$user_no = $conn->query("SELECT * FROM user WHERE user_type='user' ") or die('query failed');
$user_count = mysqli_num_rows( $user_no );
$post_no = $conn->query("SELECT * FROM posts ") or die('query failed');
$postcount = mysqli_num_rows( $post_no );
$destination_no = $conn->query("SELECT * FROM destination ") or die('query failed');
$destinationcount = mysqli_num_rows( $destination_no );
$category_no = $conn->query("SELECT * FROM category ") or die('query failed');
$categorycount = mysqli_num_rows( $category_no );

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9; /* Nền sáng */
        }

        .container {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }

        h1 {
            text-align: center;
            color: #004d99;
            margin-bottom: 20px;
        }

        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
        }

        .stat-card {
            background: linear-gradient(90deg, #123458); /* Gradient từ hồng đến cam */
            color: white;
            padding: 20px;
            border-radius: 10px;
            width: 250px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        .stat-card h2 {
            font-size: 2.5rem;
            margin: 10px 0;
        }

        .stat-card p {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <?php include("../inc/_navbar.php"); ?>
    <div class="container">
        <!-- Dashboard Header -->
        <h1>Admin Dashboard</h1>

        <!-- Stats Section -->
        <div class="stats">
            <!-- Total Users -->
            <div class="stat-card">
                <h2><?php echo $usercount; ?></h2>
                <p>Total Users</p>
            </div>

            <!-- Total Admins -->
            <div class="stat-card">
                <h2><?php echo $admin_count; ?></h2>
                <p>Total Admins</p>
            </div>

            <!-- Total Regular Users -->
            <div class="stat-card">
                <h2><?php echo $user_count; ?></h2>
                <p>Total Regular Users</p>
            </div>

            <!-- Total Posts -->
            <div class="stat-card">
                <h2><?php echo $postcount; ?></h2>
                <p>Total Posts</p>
            </div>

            <!-- Total Destinations -->
            <div class="stat-card">
                <h2><?php echo $destinationcount; ?></h2>
                <p>Total Destinations</p>
            </div>

            <!-- Total Categories -->
            <div class="stat-card">
                <h2><?php echo $categorycount; ?></h2>
                <p>Total Categories</p>
            </div>
        </div>
    </div>
</body>
</html>