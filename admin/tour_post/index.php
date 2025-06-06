<?php
session_start();

// Check admin login (adjust path as needed)
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: ../auth/login.php");
//     exit();
// }

// Connect to database
require_once '../../database/connect-db.php';
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $tour_id = (int)$_POST['tour_id'];
    $action = $_POST['action'];
    if (in_array($action, ['approve', 'reject'])) {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $sql_update = "UPDATE tour_posts SET status = ?, authorized_date = NOW() WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update === false) {
            die('Prepare failed for update: ' . htmlspecialchars($conn->error));
        }
        $stmt_update->bind_param("si", $status, $tour_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
    exit();
}

// Fetch categories
$sql_categories = "SELECT * FROM category";
$result_categories = $conn->query($sql_categories);
if ($result_categories === false) {
    die('Query failed for categories: ' . htmlspecialchars($conn->error));
}
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

// Fetch destinations
$sql_destinations = "SELECT * FROM destination";
$result_destinations = $conn->query($sql_destinations);
if ($result_destinations === false) {
    die('Query failed for destinations: ' . htmlspecialchars($conn->error));
}
$destinations = $result_destinations->fetch_all(MYSQLI_ASSOC);

// Build tour posts query with filters
$sql = "SELECT t.id, t.title, t.description, t.images, t.created_at, t.updated_at, t.status, t.authorized_date,
        t.duration, t.price, t.inclusions, t.exclusions, t.difficulty_level, t.group_size, t.availability, t.additional_images,
        c.Name AS category, d.Name AS destination, CONCAT(u.FirstName, ' ', u.LastName) AS user_name
        FROM tour_posts t
        LEFT JOIN category c ON t.category_id = c.CategoryID
        LEFT JOIN destination d ON t.destination = d.DestinationID
        LEFT JOIN user u ON t.user_id = u.UserID
        WHERE 1=1";
$params = [];
$types = "";

// Filter by category
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $sql .= " AND c.CategoryID = ?";
    $params[] = (int)$_GET['category'];
    $types .= "i";
}

// Filter by destination
if (isset($_GET['destination']) && !empty($_GET['destination'])) {
    $sql .= " AND d.DestinationID = ?";
    $params[] = (int)$_GET['destination'];
    $types .= "i";
}

// Filter by status
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $sql .= " AND t.status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

// Filter by created_at range
if (isset($_GET['created_at']) && !empty($_GET['created_at'])) {
    $sql .= " AND t.created_at >= ?";
    $params[] = $_GET['created_at'];
    $types .= "s";
}
if (isset($_GET['updated_at']) && !empty($_GET['updated_at'])) {
    $sql .= " AND t.updated_at <= ?";
    $params[] = $_GET['updated_at'];
    $types .= "s";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total records
$count_sql = "SELECT COUNT(*) AS total FROM ($sql) t";
$stmt_count = $conn->prepare($count_sql);
if ($stmt_count === false) {
    die('Prepare failed for count: ' . htmlspecialchars($conn->error));
}
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
$stmt_count->close();

// Fetch tours
$sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Prepare failed for tours: ' . htmlspecialchars($conn->error));
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$tours = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

function getQueryString($page) {
    $queryParams = $_GET;
    $queryParams['page'] = $page;
    return '?' . http_build_query($queryParams);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Tours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <style>
        body {
            background-color: #F1FEFC;
            color: #030303;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 80px;
        }
        .navbar {
            background-color: #123458;
            border-bottom: 3px solid #D4C9BE;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .navbar a {
            color: #F1FEFC !important;
            font-weight: 600;
        }
        .navbar-nav .nav-link {
            padding: 10px 15px;
            text-transform: uppercase;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .navbar-nav .nav-link:hover {
            background-color: #D4C9BE;
            color: #030303;
            border-radius: 5px;
        }
        .container {
            margin-top: 50px;
        }
        .table {
            background-color: #D4C9BE;
            border-radius: 10px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .filter-section {
            background-color: #F1FEFC;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        footer {
            background-color: #123458;
            color: #F1FEFC;
            padding: 40px 0;
            margin-top: 50px;
        }
        footer a {
            color: #F1FEFC;
            text-decoration: none;
        }
        footer a:hover {
            color: #D4C9BE;
            text-decoration: underline;
        }
        .tour-image {
            max-width: 100px;
            height: auto;
            border-radius: 5px;
        }
        .dropdown-menu {
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .dropdown-item:hover {
            background-color: #D4C9BE;
            color: #030303;
        }
        .modal-content {
            border-radius: 10px;
        }
        .modal-header {
            background-color: #123458;
            color: #F1FEFC;
        }
        .modal-footer {
            border-top: none;
        }
        @media (max-width: 768px) {
            .tour-image {
                max-width: 50px;
            }
            .table {
                font-size: 0.9rem;
            }
            .dropdown-menu {
                font-size: 0.9rem;
            }
        }
    </style> -->
</head>
<body>
    <!-- <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index_homepage.php">
                <img src="../../../uploads/logo.jpg" alt="Travel Blog Logo" class="img-fluid" style="max-width: 50px; margin-right: 10px;">
                Let's Travel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index_homepage.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_tours.php">Manage Tours</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav> -->
      <?php include("../../inc/_navbar.php"); ?>

    <div class="container py-5">
        <h1 class="mb-4">Manage Tour Posts</h1>

        <div class="filter-section">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['CategoryID']) ?>" <?= isset($_GET['category']) && $_GET['category'] == $category['CategoryID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="destination" class="form-label">Destination</label>
                        <select class="form-control" id="destination" name="destination">
                            <option value="">All Destinations</option>
                            <?php foreach ($destinations as $destination): ?>
                                <option value="<?= htmlspecialchars($destination['DestinationID']) ?>" <?= isset($_GET['destination']) && $_GET['destination'] == $destination['DestinationID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($destination['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= isset($_GET['status']) && $_GET['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= isset($_GET['status']) && $_GET['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary form-control">Filter</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="created_at" class="form-label">Created After</label>
                        <input type="date" class="form-control" id="created_at" name="created_at" value="<?= htmlspecialchars($_GET['created_at'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="updated_at" class="form-label">Created Before</label>
                        <input type="date" class="form-control" id="updated_at" name="updated_at" value="<?= htmlspecialchars($_GET['updated_at'] ?? '') ?>">
                    </div>
                </div>
            </form>
        </div>

        <?php if (empty($tours)): ?>
            <div class="alert alert-info">No tour posts found.</div>
        <?php else: ?>
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>User</th>
                        <th>Category</th>
                        <th>Destination</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Authorized</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tours as $tour): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($tour['images'] ?? '../uploads/default.jpg') ?>"
                                     class="tour-image"
                                     alt="<?= htmlspecialchars($tour['title']) ?>"
                                     style="width:100px;height:70px;object-fit:cover;border-radius:5px;">
                            </td>
                            <td><?= htmlspecialchars($tour['title']) ?></td>
                            <td><?= htmlspecialchars($tour['user_name'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($tour['category'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($tour['destination'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars(substr($tour['description'], 0, 100)) ?>...</td>
                            <td><?= htmlspecialchars($tour['status']) ?></td>
                            <td><?= htmlspecialchars($tour['authorized_date'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($tour['created_at']) ?></td>
                            <td><?= htmlspecialchars($tour['updated_at']) ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenu_<?= $tour['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu_<?= $tour['id'] ?>">
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#detailsModal_<?= $tour['id'] ?>">View Details</a></li>
                                        <?php if ($tour['status'] === 'pending'): ?>
                                            <li>
                                                <form method="POST" action="" class="m-0">
                                                    <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="dropdown-item">Approve</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="" class="m-0">
                                                    <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="dropdown-item">Reject</button>
                                                </form>
                                            </li>
                                        <?php endif; ?>
                                        <li><a class="dropdown-item" href="update.php?id=<?= $tour['id'] ?>">Update</a></li>
                                        <li><a class="dropdown-item text-danger" href="delete.php?id=<?= $tour['id'] ?>" onclick="return confirm('Are you sure you want to delete this tour?')">Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Details Modal -->
                        <div class="modal fade" id="detailsModal_<?= $tour['id'] ?>" tabindex="-1" aria-labelledby="detailsModalLabel_<?= $tour['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="detailsModalLabel_<?= $tour['id'] ?>">Tour Details: <?= htmlspecialchars($tour['title']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <img src="<?= htmlspecialchars($tour['images'] ?? '../../../uploads/default.jpg') ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($tour['title']) ?>">
                                                <?php if ($tour['additional_images']): ?>
                                                    <div class="mt-2">
                                                        <h6>Additional Images:</h6>
                                                        <?php $additional_images = explode(',', $tour['additional_images']); ?>
                                                        <?php foreach ($additional_images as $img): ?>
                                                            <img src="<?= htmlspecialchars(trim($img)) ?>" class="img-fluid rounded mt-1" style="max-width: 100px;" alt="Additional Image">
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Title:</strong> <?= htmlspecialchars($tour['title']) ?></p>
                                                <p><strong>User:</strong> <?= htmlspecialchars($tour['user_name'] ?? 'Unknown') ?></p>
                                                <p><strong>Category:</strong> <?= htmlspecialchars($tour['category'] ?? 'N/A') ?></p>
                                                <p><strong>Destination:</strong> <?= htmlspecialchars($tour['destination'] ?? 'N/A') ?></p>
                                                <p><strong>Description:</strong> <?= htmlspecialchars($tour['description']) ?></p>
                                                <p><strong>Duration:</strong> <?= htmlspecialchars($tour['duration'] ?? 'N/A') ?></p>
                                                <p><strong>Price:</strong> $<?= htmlspecialchars(number_format($tour['price'] ?? 0, 2)) ?></p>
                                                <p><strong>Inclusions:</strong> <?= htmlspecialchars($tour['inclusions'] ?? 'N/A') ?></p>
                                                <p><strong>Exclusions:</strong> <?= htmlspecialchars($tour['exclusions'] ?? 'N/A') ?></p>
                                                <p><strong>Difficulty Level:</strong> <?= htmlspecialchars($tour['difficulty_level'] ?? 'N/A') ?></p>
                                                <p><strong>Group Size:</strong> <?= htmlspecialchars($tour['group_size'] ?? 'N/A') ?></p>
                                                <p><strong>Availability:</strong> <?= htmlspecialchars($tour['availability'] ?? 'N/A') ?></p>
                                                <p><strong>Status:</strong> <?= htmlspecialchars($tour['status']) ?></p>
                                                <p><strong>Authorized Date:</strong> <?= htmlspecialchars($tour['authorized_date'] ?? '-') ?></p>
                                                <p><strong>Created:</strong> <?= htmlspecialchars($tour['created_at']) ?></p>
                                                <p><strong>Updated:</strong> <?= htmlspecialchars($tour['updated_at']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="<?= getQueryString($page - 1) ?>">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= getQueryString($i) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="<?= getQueryString($page + 1) ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- <footer>
        <div class="container py-3">
            <div class="row">
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="#" class="footer-link">Our Services</a></li>
                        <li><a href="#" class="footer-link">Privacy Policy</a></li>
                        <li><a href="#" class="footer-link">Support</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><a href="mailto:info@travelblog.com" class="footer-link">Email: info@travelblog.com</a></li>
                        <li><a href="tel:+123456789" class="footer-link">Phone: +123 456 789</a></li>
                        <li><a href="#" class="footer-link">Address: 123 Travel St</a></li>
                    </ul>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col text-center">
                    <p class="text-white-50">© 2025 Travel Blog. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer> -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>