<?php

// connect db
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname) 
    or die ('Failed to connect to db.');

// select
$sql = "SELECT * FROM `posts` WHERE 1";

// filter by title
if (isset($_GET['title']) && !empty($_GET['title'])) {
    $title = $_GET['title'];
    $sql .= " AND Title LIKE '%$title%'";
}
//FILTER BY CREATE AT 
if (isset($_GET['created_at']) && !empty($_GET['created_at'])) {
    $created_at = $_GET['created_at'];
    $sql .= " AND Created_at >= '$created_at'"; // Lọc theo Created_at
}

// pagination
$limit = 3; // number of items / page

// total pages
$countSql = "SELECT COUNT(*) AS total FROM ($sql) t";
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

$result = @mysqli_query($conn, $sql);

$posts = @mysqli_fetch_all($result, MYSQLI_ASSOC);

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
    <title>All Posts</title>

    <!-- bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="container">
    <?php include("../../inc/_navbar.php"); ?>

    <h1>All Posts</h1>

    <a href="create.php" class="btn btn-success btn-sm mt-3 mb-3">
        Create a new Post
    </a>

    <!-- filter -->
    <form action="">
        <label for="title">Title: </label>
        <input type="text" name="title" id="title">
        <label for="created_at">Created After: </label>
        <input type="date" name="created_at" id="created_at"> <!-- Thay đổi thành input date -->
        <button type="submit">Filter</button>
    </form>

<br>

    <?php if (empty($posts)): ?>
        <p>No data.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped table-hover">
        <tr>
                <th>Image</th>
                <th>ID</th>
                <th>Title</th>
                <th>Content</th>
                <th>Datetime</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($posts as $post): ?>
            <tr>
                <td><img src="<?php echo $post['image']; ?>"width="150" ></td>
                <td style="text-align: center;"><?php echo $post['PostID']; ?></td>
                <td><?php echo $post['Title']; ?></td>
                <td><?php echo $post['Content']; ?></td>
                <td><?php echo $post['Created_at']; ?></td>
                <td>
                <a href="update.php?id=<?php echo $post['PostID']; ?>">Update</a> |
                <a href="delete.php?id=<?php echo $post['PostID']; ?>">Delete</a></td>
        </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- html bootstrap pagination -->
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php if ($page > 1): ?> 
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item"><a class="page-link <?php echo ($i == $page) ? 'active' : '' ?>" 
                    href="?page=<?php echo $i; ?>">
                    <?php echo $i; ?>
                </a></li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</body>
</html>