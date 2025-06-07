<?php
// Database connection
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
    or die('Failed to connect to db.');

// Fetch all categories
$categoryQuery = "SELECT * FROM category";
$categoryResult = mysqli_query($conn, $categoryQuery);
$categories = mysqli_fetch_all($categoryResult, MYSQLI_ASSOC);

// Fetch all destinations
$destinationQuery = "SELECT * FROM destination";
$destinationResult = mysqli_query($conn, $destinationQuery);
$destinations = mysqli_fetch_all($destinationResult, MYSQLI_ASSOC);

// Select tour posts
$sql = "SELECT t.id, t.title, t.description, t.images, t.created_at,
        c.Name as category, d.Name as destination
        FROM `tour_posts` as t
        JOIN `category` as c ON t.category_id = c.CategoryID
        JOIN `destination` as d ON t.destination = d.DestinationID
        WHERE 1=1
        AND t.status = 'approved'";

// Filter by category
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $sql .= " AND c.CategoryID = '$category'";
}

// Filter by destination
if (isset($_GET['destination']) && !empty($_GET['destination'])) {
    $destination = mysqli_real_escape_string($conn, $_GET['destination']);
    $sql .= " AND d.DestinationID = '$destination'";
}

// Filter by created_at range
if (isset($_GET['created_at']) && !empty($_GET['created_at'])) {
    $created_at = mysqli_real_escape_string($conn, $_GET['created_at']);
    $sql .= " AND t.created_at >= '$created_at'";
}
if (isset($_GET['updated_at']) && !empty($_GET['updated_at'])) {
    $updated_at = mysqli_real_escape_string($conn, $_GET['updated_at']);
    $sql .= " AND t.created_at <= '$updated_at'";
}

// Pagination
$limit = 9;
$countSql = "SELECT COUNT(*) AS total FROM ($sql) t";
$result = mysqli_query($conn, $countSql);
$row = mysqli_fetch_assoc($result);
$totalRecords = $row['total'];

$totalPages = ceil($totalRecords / $limit);

$page = 1;
if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) {
    $page = (int)$_GET['page'];
}
$offset = ($page - 1) * $limit;
$sql .= " LIMIT $limit OFFSET $offset";

$result = @mysqli_query($conn, $sql);
$tours = @mysqli_fetch_all($result, MYSQLI_ASSOC);

// Free result and close connection
@mysqli_free_result($result);
@mysqli_close($conn);

// Function to build query string for pagination
function getQueryString($page) {
    $queryParams = $_GET;
    $queryParams['page'] = $page;
    return '?' . http_build_query($queryParams);
}

// Function to truncate description
function truncateDescription($text, $maxLength = 100) {
    if (strlen($text) <= $maxLength) {
        return $text;
    }
    return substr($text, 0, $maxLength) . '...';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Posts</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #F1FEFC;
            color: #030303;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        .hero-section {
            background-color: #123458;
            color: #F1FEFC;
            background-image: url('https://tiesinstitute.com/wp-content/uploads/2021/01/shutterstock_268004744-2.jpg');
            background-size: cover;
            background-position: center;
            height: 400px;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.7);
        }

        .hero-section h1 {
            font-size: 2rem;
            font-weight: bold;
        }

        .container {
            margin-top: 50px;
        }

        .card {
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .card-title {
            color: #123458;
        }

        .card-body {
            background-color: #D4C9BE;
            color: #030303;
            border-radius: 5px;
        }

        .row-cols-md-3 .col {
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .card-img-top {
                height: 150px;
            }
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
            padding-top: 20px;
            padding-bottom: 20px;
            margin-top: 20px;
        }

        footer h5 {
            font-weight: bold;
            font-size: 1.25rem;
        }

        footer .footer-link {
            color: #F1FEFC;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        footer .footer-link:hover {
            color: #D4C9BE;
            text-decoration: underline;
        }

        footer .text-white-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        @media (max-width: 768px) {
            footer h5 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <?php include("../../inc/_navbar.php"); ?>

    <div class="hero-section">
        <h1>Explore Our Tours</h1>
    </div>

    <div class="container filter-section">
        <form action="" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select class="form-control" name="category" id="category">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['CategoryID']; ?>"
                                    <?php echo (isset($_GET['category']) && $_GET['category'] == $category['CategoryID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="destination">Destination:</label>
                        <select class="form-control" name="destination" id="destination">
                            <option value="">All Destinations</option>
                            <?php foreach($destinations as $destination): ?>
                                <option value="<?php echo $destination['DestinationID']; ?>"
                                    <?php echo (isset($_GET['destination']) && $_GET['destination'] == $destination['DestinationID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($destination['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="created_at">Created After:</label>
                        <input type="date" class="form-control" name="created_at" id="created_at" 
                            value="<?php echo isset($_GET['created_at']) ? htmlspecialchars($_GET['created_at']) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="updated_at">Created Before:</label>
                        <input type="date" class="form-control" name="updated_at" id="updated_at" 
                            value="<?php echo isset($_GET['updated_at']) ? htmlspecialchars($_GET['updated_at']) : ''; ?>">
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary form-control">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="container">
        <h1 class="mb-4">Our Tours</h1>
        <?php if (empty($tours)): ?>
            <p>No tours available.</p>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($tours as $tour): ?>
                    <div class="col">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($tour['images']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($tour['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($tour['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(truncateDescription($tour['description'])); ?></p>
                                <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($tour['category']); ?></p>
                                <p class="card-text"><strong>Destination:</strong> <?php echo htmlspecialchars($tour['destination']); ?></p>
                                <p class="card-text"><small class="text-muted">Posted on: <?php echo htmlspecialchars($tour['created_at']); ?></small></p>
                                <a href="tour_details.php?id=<?php echo $tour['id']; ?>" class="btn btn-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="container mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo getQueryString($page - 1); ?>">Previous</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo getQueryString($i); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo getQueryString($page + 1); ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <footer>
        <div class="container py-3">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="#" class="footer-link">Our Services</a></li>
                        <li><a href="#" class="footer-link">Privacy Policy</a></li>
                        <li><a href="#" class="footer-link">Support</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white mb-3">Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><a href="mailto:info@travelblog.com" class="footer-link">Email: info@travelblog.com</a></li>
                        <li><a href="tel:+123456789" class="footer-link">Phone: +123 456 789</a></li>
                        <li><a href="#" class="footer-link">Address: 123 Travel St, City, Country</a></li>
                    </ul>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col text-center">
                    <p class="text-white-50">© 2025 Travel Blog. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>