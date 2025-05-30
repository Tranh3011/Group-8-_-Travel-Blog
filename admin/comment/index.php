<?php
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel_blog';

$conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);



if (!$conn) {
    echo 'Failed to connect to db';
} else {
    echo 'Connect successfully';
// or die ("Failed to connect to db");
}
// // require login
// include('../shared/_required-login.php');

// // connect db
// include('../db/connect-db.php');

// initial SQL statement
$sql = "SELECT * FROM `comment` WHERE 1";
// $result = mysqli_query($conn, $sql);

$user_id = '';
$post_id = '';
$comment = '';
$create_at = '';

// filter conditions by UserID and PostID and Created_at
if (isset($_GET['UserID']) && !empty($_GET['UserID'])) {
    $user_id =  $_GET['UserID'];
    $sql .= " AND UserID = '$user_id'";
}

if (isset($_GET['PostID']) && !empty($_GET['PostID'])) {
    $post_id =  $_GET['PostID'];
    $sql .= " AND PostID = '$post_id'";
}

if (isset($_GET['Created_at']) && !empty($_GET['Created_at'])) {
    $create_at = $_GET['Created_at'];
    $sql .= " AND Created_at LIKE '%$create_at%'";
}

// order result
$sql .= " ORDER BY CommentID";

// -- pagination

$page = 1;
if(isset($_GET['page'])) {
    $page = $_GET['page'];
}
$limit = 4; // no. cars per page
$offset = ($page-1)*$limit;

// count total results
$sqlCount = "SELECT COUNT(*) AS noResults FROM ($sql) AS filteredResults";
$resultCount = mysqli_query($conn, $sqlCount);
$noResults = mysqli_fetch_assoc($resultCount)['noResults'];
$noPages = ceil($noResults / $limit);

// limit and offset for pagination
$sql .= " LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// process results
$comments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// free result
mysqli_free_result($resultCount);
mysqli_free_result($result);

// close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment</title>

    <!-- bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
    <div class="container mt-5">
        <?php include('../inc/_navbar.php'); ?> 

        
        <br/><br/>
        <h1>All Comments</h1>
        <a class="btn btn-success" href="create.php">Create a new comment</a>
        <br/><br/>
        <form action="" method="get">
        <div class="row my-4">
            <div class="col-4">
                <input class="form-control" type="text" name="UserID" placeholder="User ID" value="<?php echo $user_id; ?>">
            </div>
            <div class="col-4">
                <input class="form-control" type="text" name="PostID" placeholder="Post ID" value="<?php echo $post_id; ?>">
            </div>
            <div class="col-4">
                <input class="form-control" type="text" name="Created_at" placeholder="Create at" value="<?php echo $create_at; ?>">
            </div>
            <div class="col-4">
                <button class="btn btn-primary" type="submit">Filter</button>
            </div>
        </div>
        </form>

        <?php if (empty($comments)): ?>
            <p>No data.</p>
        <?php else: ?>
            <table class="table table-bordered table-striped table-hover">
                <tr class="table-primary">
                    <th>ID</th>
                    <th>User</th>
                    <th>Post</th>
                    <th>Comment</th>
                    <th>Created at</th>
                    <th>Actions</th>
                </tr>

                <?php foreach ($comments as $cmt) : ?>
                    <tr class="align-middle text-center">
                        <td><?php echo htmlspecialchars($cmt['CommentID']); ?></td>
                        <td><?php echo htmlspecialchars($cmt['UserID']); ?></td>
                        <td><?php echo htmlspecialchars($cmt['PostID']); ?></td>
                        <td><?php echo htmlspecialchars($cmt['Content']); ?></td>
                        <td><?php echo htmlspecialchars($cmt['Created_at']); ?></td>
                        <td>
                            <a href="update.php?id=<?php echo $cmt['CommentID']; ?>">Update</a> |
                            <a href="delete.php?id=<?php echo $cmt['CommentID']; ?>">Delete</a>            
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page-1; ?>&user_id=<?php echo htmlspecialchars($user_id); ?>&post_id=<?php echo htmlspecialchars($post_id); ?>&comment=<?php echo htmlspecialchars($comment); ?>&create_at=<?php echo htmlspecialchars($create_at); ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $noPages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&user_id=<?php echo htmlspecialchars($user_id); ?>&post_id=<?php echo htmlspecialchars($post_id); ?>&comment=<?php echo htmlspecialchars($comment); ?>&create_at=<?php echo htmlspecialchars($create_at); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $noPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page+1; ?>&user_id=<?php echo htmlspecialchars($user_id); ?>&post_id=<?php echo htmlspecialchars($post_id); ?>&comment=<?php echo htmlspecialchars($comment); ?>&create_at=<?php echo htmlspecialchars($create_at); ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        
    </div>
</body>
</html>