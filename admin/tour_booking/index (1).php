<?php
// Database connection
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
    or die ('Failed to connect to db.');

// Base SQL query
$sql = "SELECT t.id, t.tour_post_id, t.guest_full_name, t.owner_full_name, t.num_people, 
        t.travel_date, t.end_date, t.status, t.created_at, t.updated_at, 
        t.full_name, t.email, t.phone, t.price, t.notes, t.special_requests, 
        p.title as tour_post
        FROM `tour_booking` as t
        JOIN `tour_posts` as p ON t.tour_post_id = p.id
        WHERE 1=1";

// Filters
// if (isset($_GET['full_name']) && !empty($_GET['full_name'])) {
//     $full_name = mysqli_real_escape_string($conn, $_GET['full_name']);
//     $sql .= " AND t.guest_full_name LIKE '%$full_name%'";
// }

if (isset($_GET['tour_post']) && !empty($_GET['tour_post'])) {
    $tour_post = mysqli_real_escape_string($conn, $_GET['tour_post']);
    $sql .= " AND p.title LIKE '%$tour_post%'";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $sql .= " AND t.status = '$status'";
}

if (isset($_GET['created_at']) && !empty($_GET['created_at'])) {
    $created_at = mysqli_real_escape_string($conn, $_GET['created_at']);
    $sql .= " AND t.created_at >= '$created_at'";
}

if (isset($_GET['updated_at']) && !empty($_GET['updated_at'])) {
    $updated_at = mysqli_real_escape_string($conn, $_GET['updated_at']);
    $sql .= " AND t.created_at <= '$updated_at'";
}

// Pagination
$limit = 10;
$countSql = "SELECT COUNT(*) AS total FROM ($sql) t";
$result = mysqli_query($conn, $countSql);
$row = mysqli_fetch_assoc($result);
$totalRecords = $row['total'];
$totalPages = ceil($totalRecords / $limit);

$page = 1;
if (isset($_GET['page'])) {
    $page = $_GET['page'];
}
$offset = ($page - 1) * $limit;
$sql .= " LIMIT $limit OFFSET $offset";

$result = @mysqli_query($conn, $sql);
$tours = @mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get all tour posts for filter dropdown
$tour_posts_sql = "SELECT id, title FROM tour_posts";
$tour_posts_result = @mysqli_query($conn, $tour_posts_sql);
$tour_posts = @mysqli_fetch_all($tour_posts_result, MYSQLI_ASSOC);

// Status options
$status_options = ['waiting_response', 'responded', 'planning', 'done', 'cancelled'];

// Free result and close connection
@mysqli_free_result($result);
@mysqli_free_result($tour_posts_result);
@mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Bookings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <style>
        .modal-lg {
            max-width: 800px;
        }
    </style>
</head>
<body>
    <?php include("../../inc/_navbar.php"); ?>
    <div class="container mt-4">
    <h1>All Tour Bookings</h1>

    <!-- <a href="create.php" class="btn btn-success btn-sm mt-3 mb-3">
        Create a new tour booking
    </a> -->

    <!-- Filter Form -->
    <form action="" class="mb-4">
        <div class="form-row">
            <div class="col">
                <label for="full_name">Guest Name</label>
                <input type="text" class="form-control" name="full_name" id="full_name" value="<?php echo isset($_GET['full_name']) ? htmlspecialchars($_GET['full_name']) : ''; ?>">
            </div>
            <div class="col">
                <label for="tour_post">Tour</label>
                <select class="form-control" name="tour_post" id="tour_post">
                    <option value="">All Tours</option>
                    <?php foreach ($tour_posts as $post): ?>
                        <option value="<?php echo htmlspecialchars($post['title']); ?>" <?php echo (isset($_GET['tour_post']) && $_GET['tour_post'] == $post['title']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($post['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <label for="status">Status</label>
                <select class="form-control" name="status" id="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($status_options as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo (isset($_GET['status']) && $_GET['status'] == $status) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="created_at">Created After</label>
                <input type="date" class="form-control" name="created_at" id="created_at" value="<?php echo isset($_GET['created_at']) ? htmlspecialchars($_GET['created_at']) : ''; ?>">
            </div>
            <div class="col">
                <label for="updated_at">Created Before</label>
                <input type="date" class="form-control" name="updated_at" id="updated_at" value="<?php echo isset($_GET['updated_at']) ? htmlspecialchars($_GET['updated_at']) : ''; ?>">
            </div>
            <div class="col align-self-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <?php if (empty($tours)): ?>
        <p>No bookings found.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped table-hover">
            <tr>
                <th>Guest Name</th>
                <th>Owner Name</th>
                <th>Tour</th>
                <th>Number of People</th>
                <th>Travel Date</th>
                <th>End Date</th>
                <th>Price</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($tours as $tour): ?>
            <tr>
                <td><?php echo htmlspecialchars($tour['guest_full_name']); ?></td>
                <td><?php echo htmlspecialchars($tour['owner_full_name']); ?></td>
                <td><?php echo htmlspecialchars($tour['tour_post']); ?></td>
                <td><?php echo htmlspecialchars($tour['num_people']); ?></td>
                <td><?php echo htmlspecialchars($tour['travel_date']); ?></td>
                <td><?php echo htmlspecialchars($tour['end_date']); ?></td>
               <!-- poles -->
                <td><?php echo htmlspecialchars($tour['price']); ?></td>
                <td>
                    <form action="update.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                        <select name="status" onchange="this.form.submit()">
                            <?php foreach ($status_options as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo ($tour['status'] == $status) ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($status); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td><?php echo htmlspecialchars($tour['created_at']); ?></td>
                <td>
                    <button class="btn btn-info btn-sm view-details" 
                            data-toggle="modal" 
                            data-target="#detailsModal"
                            data-id="<?php echo $tour['id']; ?>"
                            data-guest="<?php echo htmlspecialchars($tour['guest_full_name']); ?>"
                            data-owner="<?php echo htmlspecialchars($tour['owner_full_name']); ?>"
                            data-tour="<?php echo htmlspecialchars($tour['tour_post']); ?>"
                            data-numpeople="<?php echo htmlspecialchars($tour['num_people']); ?>"
                            data-traveldate="<?php echo htmlspecialchars($tour['travel_date']); ?>"
                            data-enddate="<?php echo htmlspecialchars($tour['end_date']); ?>"
                            data-price="<?php echo htmlspecialchars($tour['price']); ?>"
                            data-status="<?php echo htmlspecialchars($tour['status']); ?>"
                            data-created="<?php echo htmlspecialchars($tour['created_at']); ?>"
                            data-updated="<?php echo htmlspecialchars($tour['updated_at']); ?>"
                            data-email="<?php echo htmlspecialchars($tour['email']); ?>"
                            data-phone="<?php echo htmlspecialchars($tour['phone']); ?>"
                            data-notes="<?php echo htmlspecialchars($tour['notes'] ?? ''); ?>"
                            data-requests="<?php echo htmlspecialchars($tour['special_requests'] ?? ''); ?>">
                        View Details
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Booking Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>ID:</strong> <span id="modal-id"></span></p>
                    <p><strong>Guest Name:</strong> <span id="modal-guest"></span></p>
                    <p><strong>Owner Name:</strong> <span id="modal-owner"></span></p>
                    <p><strong>Tour:</strong> <span id="modal-tour"></span></p>
                    <p><strong>Number of People:</strong> <span id="modal-numpeople"></span></p>
                    <p><strong>Travel Date:</strong> <span id="modal-traveldate"></span></p>
                    <p><strong>End Date:</strong> <span id="modal-enddate"></span></p>
                    <p><strong>Price:</strong> <span id="modal-price"></span></p>
                    <p><strong>Status:</strong> <span id="modal-status"></span></p>
                    <p><strong>Email:</strong> <span id="modal-email"></span></p>
                    <p><strong>Phone:</strong> <span id="modal-phone"></span></p>
                    <p><strong>Notes:</strong> <span id="modal-notes"></span></p>
                    <p><strong>Special Requests:</strong> <span id="modal-requests"></span></p>
                    <p><strong>Created At:</strong> <span id="modal-created"></span></p>
                    <p><strong>Updated At:</strong> <span id="modal-updated"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&full_name=<?php echo isset($_GET['full_name']) ? htmlspecialchars($_GET['full_name']) : ''; ?>&tour_post=<?php echo isset($_GET['tour_post']) ? htmlspecialchars($_GET['tour_post']) : ''; ?>&status=<?php echo isset($_GET['status']) ? htmlspecialchars($_GET['status']) : ''; ?>&created_at=<?php echo isset($_GET['created_at']) ? htmlspecialchars($_GET['created_at']) : ''; ?>&updated_at=<?php echo isset($_GET['updated_at']) ? htmlspecialchars($_GET['updated_at']) : ''; ?>">Previous</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&full_name=<?php echo isset($_GET['full_name']) ? htmlspecialchars($_GET['full_name']) : ''; ?>&tour_post=<?php echo isset($_GET['tour_post']) ? htmlspecialchars($_GET['tour_post']) : ''; ?>&status=<?php echo isset($_GET['status']) ? htmlspecialchars($_GET['status']) : ''; ?>&created_at=<?php echo isset($_GET['created_at']) ? htmlspecialchars($_GET['created_at']) : ''; ?>&updated_at=<?php echo isset($_GET['updated_at']) ? htmlspecialchars($_GET['updated_at']) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&full_name=<?php echo isset($_GET['full_name']) ? htmlspecialchars($_GET['full_name']) : ''; ?>&tour_post=<?php echo isset($_GET['tour_post']) ? htmlspecialchars($_GET['tour_post']) : ''; ?>&status=<?php echo isset($_GET['status']) ? htmlspecialchars($_GET['status']) : ''; ?>&created_at=<?php echo isset($_GET['created_at']) ? htmlspecialchars($_GET['created_at']) : ''; ?>&updated_at=<?php echo isset($_GET['updated_at']) ? htmlspecialchars($_GET['updated_at']) : ''; ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.view-details').click(function() {
                var button = $(this);
                $('#modal-id').text(button.data('id'));
                $('#modal-guest').text(button.data('guest'));
                $('#modal-owner').text(button.data('owner'));
                $('#modal-tour').text(button.data('tour'));
                $('#modal-numpeople').text(button.data('numpeople'));
                $('#modal-traveldate').text(button.data('traveldate'));
                $('#modal-enddate').text(button.data('enddate'));
                $('#modal-price').text(button.data('price'));
                $('#modal-status').text(button.data('status'));
                $('#modal-email').text(button.data('email'));
                $('#modal-phone').text(button.data('phone'));
                $('#modal-notes').text(button.data('notes') || 'None');
                $('#modal-requests').text(button.data('requests') || 'None');
                $('#modal-created').text(button.data('created'));
                $('#modal-updated').text(button.data('updated'));
            });
        });
    </script>
</body>
</html>