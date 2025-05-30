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
$sql = "SELECT * FROM `category` WHERE 1=1";

// filter by Name
if (isset($_GET['Name']) && !empty($_GET['Name'])) {
    $name = mysqli_real_escape_string($conn, $_GET['Name']);
    $sql .= " AND Name LIKE '%$name%'";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $sql .= " AND Category LIKE '%$category%'";
}

// filter by Created_at range
if (isset($_GET['created_at_start']) && !empty($_GET['created_at_start'])) {
    $created_at_start = mysqli_real_escape_string($conn, $_GET['created_at_start']);
    $sql .= " AND Created_at >= '$created_at_start'";
}
if (isset($_GET['created_at_end']) && !empty($_GET['created_at_end'])) {
    $created_at_end = mysqli_real_escape_string($conn, $_GET['created_at_end']);
    $sql .= " AND Created_at <= '$created_at_end'";
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
$category = @mysqli_fetch_all($result, MYSQLI_ASSOC);

// // Lấy dữ liệu từ bảng category_destination
// $category_destinations = [];
// $destination_sql = "SELECT * FROM `category_destination`";
// $destination_result = @mysqli_query($conn, $destination_sql);

// if ($destination_result) {
//     while ($row = mysqli_fetch_assoc($destination_result)) {
//         $category_destinations[$row['CategoryID']][] = $row['DestinationID'];
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
    <title>Categories</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
</head>
<body class="container">
    <?php include("../inc/_navbar.php"); ?>
    <h1>All category</h1>

    <a href="createcategory.php" class="btn btn-success btn-sm mt-3 mb-3">
        Create a new category
    </a>

    <!-- filter -->
    <form action="">
        <label for="Name">Name: </label>
        <input type="text" name="Name" id="Name" value="<?php echo isset($_GET['Name']) ? htmlspecialchars($_GET['Name']) : ''; ?>">

        <label for="created_at_start">Created After: </label>
        <input type="date" name="created_at_start" id="created_at_start" value="<?php echo isset($_GET['created_at_start']) ? htmlspecialchars($_GET['created_at_start']) : ''; ?>">

        <label for="created_at_end">Created Before: </label>
        <input type="date" name="created_at_end" id="created_at_end" value="<?php echo isset($_GET['created_at_end']) ? htmlspecialchars($_GET['created_at_end']) : ''; ?>">

        <button type="submit">Filter</button>
    </form>

    <?php if (empty($category)): ?>
        <p>No data.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped table-hover">
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Created_at</th>
                <th>Updated_at</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($category as $category): ?>
            <tr>
            <td>
                <img src="<?php echo $category['Image']; ?>" width="300">
                
            </td>
                <td><?php echo $category['Name']; ?></td>
                <td><?php echo $category['Category']; ?></td>
                <td><?php echo $category['Description']; ?></td>
                <td><?php echo $category['Created_at']; ?></td>
                <td><?php echo $category['Updated_at']; ?></td>
                <td>
                    <a href="updatecategory.php?id=<?php echo $category['CategoryID'] ?>">Update</a> |
                    <a href="deletecategory.php?id=<?php echo $category['CategoryID'] ?>">Delete</a>
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