<?php
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';

$conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
if (!$conn) {
    die('Failed to connect to db: ' . mysqli_connect_error());
} else {
    echo 'Connected successfully';
}

// Initialize the base SQL query
$sql = "SELECT * FROM `posts` WHERE 1=1";

// FILTER BY TITLE
if (isset($_GET['title']) && !empty($_GET['title'])) {
    $title = mysqli_real_escape_string($conn, $_GET['title']);
    $sql .= " AND `title` LIKE '%$title%'";
}

// FILTER BY CREATED AT
if (isset($_GET['created_at']) && !empty($_GET['created_at'])) {
    $created_at = mysqli_real_escape_string($conn, $_GET['created_at']);
    $sql .= " AND `Created_at` >= '$created_at'";
}

// PAGINATION
$limit = 3; // Number of items per page
$page = 1;
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page = intval($_GET['page']);
}
$offset = ($page - 1) * $limit;

// Count total records
$countSql = "SELECT COUNT(*) AS total FROM ($sql) AS t";
$result = mysqli_query($conn, $countSql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalRecords = $row['total'];
    mysqli_free_result($result);
} else {
    die('Error executing count query: ' . mysqli_error($conn));
}

$totalPages = ceil($totalRecords / $limit);

// Add pagination to the SQL query
$sql .= " LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);
if ($result) {
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
} else {
    die('Error executing main query: ' . mysqli_error($conn));
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<body>
    <?php include('../inc/_navbar.php'); ?> 
    <h1>All posts</h1>
    <!-- Filtering -->
    <form action="">
        <label for="title">Title: </label>
        <input type="text" name="title" id="title">
        <label for="created_at">Created After: </label>
        <input type="date" name="created_at" id="created_at">
        <button type="submit">Filter</button>
    </form>
    <?php if (empty($posts)): ?>
        <p>No data.</p>
    <?php else: ?>
    <table class="table table-bordered table-striped table-hover">
        <tr>
            <th>PostID</th>
            <th>UserID</th>
            <th>DestinationID</th>
            <th>Title</th>
            <th>Content</th>
            <th>ImageURL</th>
            <th>Post Created at</th>
            <th>Updated at</th>
        </tr>
        <?php foreach ($posts as $post): ?>
        <tr>
            <td><?php echo htmlspecialchars($post['PostID']); ?></td>
            <td><?php echo htmlspecialchars($post['UserID']); ?></td>
            <td><?php echo htmlspecialchars($post['DestinationID']); ?></td>
            <td><?php echo htmlspecialchars($post['Title']); ?></td>
            <td><?php echo htmlspecialchars($post['Content']); ?></td>
            <td><?php echo htmlspecialchars($post['Image']); ?></td>
            <td><?php echo htmlspecialchars($post['Created_at']); ?></td>
            <td><?php echo htmlspecialchars($post['Updated_at']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <!-- Pagination -->
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php if ($page > 1): ?> 
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</body>
</html>
