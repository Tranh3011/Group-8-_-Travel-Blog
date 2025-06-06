<?php
// process logic
// prepare data for html

// connect db
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel_blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
    or die ('Failed to connect to db.');

// select
$sql = "SELECT t.id, t.title, t.description, t.images, t.created_at, t.updated_at,
        c.Name as category, d.Name as destination
        FROM `tour_posts` as t
        join `category` as c ON t.category_id = c.CategoryID
        join `destination` as d ON t.destination = d.DestinationID
        WHERE 1=1
        ";

// filter by Name
// if (isset($_GET['Category']) && !empty($_GET['Name'])) {
//     $name = mysqli_real_escape_string($conn, $_GET['Name']);
//     $sql .= " AND Name LIKE '%$name%'";
// }

// if (isset($_GET['category']) && !empty($_GET['category'])) {
//     $tours = mysqli_real_escape_string($conn, $_GET['category']);
//     $sql .= " AND Category LIKE '%$tours%'";
// }

// filter by Created_at range
if (isset($_GET['created_at']) && !empty($_GET['created_at'])) {
    $created_at = mysqli_real_escape_string($conn, $_GET['created_at']);
    $sql .= " AND created_at >= '$created_at'";
}
if (isset($_GET['updated_at']) && !empty($_GET['updated_at'])) {
    $updated_at = mysqli_real_escape_string($conn, $_GET['updated_at']);
    $sql .= " AND created_at <= '$updated_at'";
}

// pagination
$limit = 10;

// total pages
$countSql = "SELECT COUNT(*) AS total from ($sql) t";
$result = mysqli_query($conn, $countSql);
$row = mysqli_fetch_assoc($result);
$totalRecords = $row['total'];

$totalPages = ceil($totalRecords / $limit);

$page = 1;
// get data from url
if (isset($_GET['page'])) {
    $page = $_GET['page'];
}
$offset = ($page - 1) * $limit;
$sql .= " LIMIT $limit OFFSET $offset";

$result = @mysqli_query($conn, $sql);
$tours = @mysqli_fetch_all($result, MYSQLI_ASSOC);

// // Lấy dữ liệu từ bảng category_destination
// $tours_destinations = [];
// $destination_sql = "SELECT * FROM `category_destination`";
// $destination_result = @mysqli_query($conn, $destination_sql);

// if ($destination_result) {
//     while ($row = mysqli_fetch_assoc($destination_result)) {
//         $tours_destinations[$row['CategoryID']][] = $row['DestinationID'];
//     }
// }

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
    <title>Tours</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
</head>
<body class="container">
    <?php include("../../inc/_navbar.php"); ?>
    <h1>All Tour</h1>

    <a href="create.php" class="btn btn-success btn-sm mt-3 mb-3">
        Create a new tour
    </a>

    <!-- filter -->
    <form action="">
        <label for="Title">Title: </label>
        <input type="text" name="Title" id="Title" value="<?php echo isset($_GET['Title']) ? htmlspecialchars($_GET['Title']) : ''; ?>">

        <label for="created_at">Created After: </label>
        <input type="date" name="created_at" id="created_at" value="<?php echo isset($_GET['created_at']) ? htmlspecialchars($_GET['created_at']) : ''; ?>">

        <label for="updated_at">Created Before: </label>
        <input type="date" name="updated_at" id="updated_at" value="<?php echo isset($_GET['updated_at']) ? htmlspecialchars($_GET['updated_at']) : ''; ?>">

        <button type="submit">Filter</button>
    </form>

    <?php if (empty($tours)): ?>
        <p>No data.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped table-hover">
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Destination</th>
                <th>Description</th>
                <th>Created_at</th>
                <th>Updated_at</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($tours as $tours): ?>
            <tr>
            <td>
                <img src="<?php echo $tours['images']; ?>" width="300">
                
            </td>
                <td><?php echo $tours['title']; ?></td>
                <td><?php echo $tours['category']; ?></td>
                <td><?php echo $tours['destination']; ?></td>
                <td><?php echo $tours['description']; ?></td>
                <td><?php echo $tours['created_at']; ?></td>
                <td><?php echo $tours['updated_at']; ?></td>
                <td>
                    <a href="update.php?id=<?php echo $tours['id'] ?>">Update</a> |
                    <a href="delete.php?id=<?php echo $tours['id'] ?>">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- html pages -->
    <nav aria-label="Page navigation example">
        <ul class="pagination">
        <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item">
                <a 
                class="page-link <?php echo ($i == $page) ? 'active' : '' ?>" 
                href="?page=<?php echo $i; ?>">
                <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
        <?php endif; ?>
        </ul>
    </nav>
</body>
</html>