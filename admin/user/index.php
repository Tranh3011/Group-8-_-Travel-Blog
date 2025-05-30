<?php
// connect db
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel_blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname) 
    or die ('Failed to connect to db.');

// select
$sql = "SELECT * FROM user WHERE 1";

// filter by FirstName
if (isset($_GET['FirstName']) && !empty($_GET['FirstName'])) {
    $firstName = $_GET['FirstName'];
    $sql .= " AND FirstName LIKE '%$firstName%'";
}

// filter by Email
if (isset($_GET['Email']) && !empty($_GET['Email'])) {
    $email = $_GET['Email'];
    $sql .= " AND Email LIKE '%$email%'";
}

// pagination
$limit = 3; // number of items per page

// total pages
$countSql = "SELECT COUNT(*) AS total from ($sql) t";
$result = mysqli_query($conn, $countSql);
$row = mysqli_fetch_assoc($result);
$totalRecords = $row['total'];

$totalPages = ceil($totalRecords / $limit);

$page = 1;
// get data from url = user defined page
if (isset($_GET['page'])) {
    $page = $_GET['page'];
}
$offset = ($page-1) * $limit;

$sql .= " LIMIT $limit OFFSET $offset";

// Execute the query
$result = @mysqli_query($conn, $sql);
$users = @mysqli_fetch_all($result, MYSQLI_ASSOC);

// free result
@mysqli_free_result($result);

// close connection
@mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container">
    <?php include("../inc/_navbar.php"); ?>

    <h1>All Users</h1>

    <a href="create.php" class="btn btn-success btn-sm mt-3 mb-3">Create a new user</a>
       
    

    <!-- filter -->
    <form action="">
        <label for="FirstName">First Name: </label>
        <input type="text" name="FirstName" id="FirstName">

        <label for="Email">Email: </label>
        <input type="email" name="Email" id="Email">

        <button type="submit">Filter</button>
    </form>

    <?php if (empty($users)): ?>
        <p>No users.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped table-hover">
            <tr>
                <th>Avatar</th>
                <th>UserID</th>
                <th>FirstName</th>
                <th>LastName</th>
                <th>Email</th>
                <th>PhoneNumber</th>
                <th>City</th>
                <th>Country</th>
                <th>Follower</th>
                <th>Following</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($users as $user): ?>
            <tr>
            <td>
                <img src="<?php echo $user['Avatar']; ?> " width="300"  alt="Avatar">
                
            </td>
                <td><?php echo $user['UserID']; ?></td>
                <td><?php echo $user['FirstName']; ?></td>
                <td><?php echo $user['LastName']; ?></td>
                <td><?php echo $user['Email']; ?></td>
                <td><?php echo $user['PhoneNumber']; ?></td>
                <td><?php echo $user['City']; ?></td>
                <td><?php echo $user['Country']; ?></td>
                <td><?php echo $user['Follower']; ?></td>
                <td><?php echo $user['Following']; ?></td>
                <td>
                

                    <a href="update.php?id=<?php echo $user['UserID'] ?>">Update</a> |
                    <a href="delete.php?id=<?php echo $user['UserID'] ?>">Delete</a>
                </td>
                
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- pagination -->
    <nav aria-label="Page navigation example">
        <ul class="pagination">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item">
                <a class="page-link <?php echo ($i == $page) ? 'active' : '' ?>" href="?page=<?php echo $i; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        <?php endif; ?>
        </ul>
    </nav>
</body>
</html>


