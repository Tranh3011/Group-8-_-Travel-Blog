<?php
session_start();
require_once '../../database/connect-db.php';

// Fix session check: use 'user_id' and 'LoggedIn'
if (!isset($_SESSION['user_id']) || !isset($_SESSION['LoggedIn'])) {
    // Store the requested URL in session
    $_SESSION['requested_page'] = $_SERVER['REQUEST_URI'];
    header("Location: ../../auth/login.php");
    exit();
}

// Get tour ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$tour_id = (int)$_GET['id'];

// Fetch tour details
$sql = "SELECT t.id, t.title, t.description, t.images, t.created_at, t.duration, t.price, t.inclusions, t.exclusions, t.difficulty_level, t.group_size, t.availability, t.additional_images, t.category_id, t.destination,
        c.Name as category, d.Name as destination_name
        FROM tour_posts t
        JOIN category c ON t.category_id = c.CategoryID
        JOIN destination d ON t.destination = d.DestinationID
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$result = $stmt->get_result();
$tour = $result->fetch_assoc();

if (!$tour) {
    header("Location: index.php");
    exit;
}

// Fetch user details for pre-filling
$user_sql = "SELECT FirstName, LastName, Email, PhoneNumber FROM user WHERE UserID = ?";
$user_stmt = $conn->prepare($user_sql);
if (!$user_stmt) {
    die('Prepare failed: ' . $conn->error);
}
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Fetch related tours (same category or destination, excluding current tour)
$related_sql = "SELECT t.id, t.title, t.images, c.Name as category, d.Name as destination
                FROM tour_posts t
                JOIN category c ON t.category_id = c.CategoryID
                JOIN destination d ON t.destination = d.DestinationID
                WHERE (t.category_id = ? OR t.destination = ?) AND t.id != ? AND t.status = 'approved'
                LIMIT 3";
$related_stmt = $conn->prepare($related_sql);
if (!$related_stmt) {
    die('Prepare failed: ' . $conn->error);
}
$related_stmt->bind_param("iii", $tour['category_id'], $tour['destination'], $tour_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
$related_tours = $related_result->fetch_all(MYSQLI_ASSOC);

// Close connections
$related_stmt->close();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tour['title']); ?> - Tour Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #F1FEFC;
            color: #030303;
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
        }
        .hero-section {
            background-color: #123458;
            color: #F1FEFC;
            background-image: url('<?php echo htmlspecialchars($tour['images']); ?>');
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
            font-size: 2.5rem;
            font-weight: bold;
        }
        .container {
            margin-top: 50px;
            max-width: 800px;
        }
        .article-header {
            border-bottom: 2px solid #123458;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .article-header h1 {
            font-size: 2rem;
            color: #123458;
        }
        .article-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        .article-content {
            font-size: 1.1rem;
            line-height: 1.6;
            text-align: justify;
        }
        .article-content img {
            max-width: 100%;
            height: auto;
            margin: 20px 0;
            border-radius: 10px;
        }
        .tour-details {
            background-color: #D4C9BE;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .tour-details h3 {
            color: #123458;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .tour-details ul {
            list-style-type: none;
            padding: 0;
        }
        .tour-details li {
            margin-bottom: 10px;
        }
        .gallery img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .social-share {
            margin: 20px 0;
        }
        .social-share a {
            color: #123458;
            font-size: 1.5rem;
            margin-right: 15px;
            text-decoration: none;
        }
        .social-share a:hover {
            color: #D4C9BE;
        }
        .map-placeholder {
            background-color: #e0e0e0;
            height: 300px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .related-tours {
            margin-top: 40px;
        }
        .related-tours .card {
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
        }
        .related-tours .card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .related-tours .card-img-top {
            height: 150px;
            object-fit: cover;
        }
        .related-tours .card-body {
            background-color: #D4C9BE;
            color: #030303;
            border-radius: 5px;
        }
        .btn-booking {
            background-color: #28a745;
            color: #F1FEFC;
            font-weight: bold;
        }
        .btn-booking:hover {
            background-color: #218838;
        }
        .modal-header {
            background-color: #123458;
            color: #F1FEFC;
        }
        .modal-footer {
            border-top: none;
        }
        footer {
            background-color: #123458;
            color: #F1FEFC;
            padding-top: 20px;
            padding-bottom: 20px;
            margin-top: 50px;
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
            .hero-section h1 {
                font-size: 1.8rem;
            }
            .article-header h1 {
                font-size: 1.5rem;
            }
            footer h5 {
                font-size: 1.1rem;
            }
            .gallery img {
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <?php include("../../inc/_navbar.php"); ?>

    <div class="hero-section">
        <h1><?php echo htmlspecialchars($tour['title']); ?></h1>
    </div>

    <div class="container">
        <div class="article-header">
            <h1><?php echo htmlspecialchars($tour['title']); ?></h1>
            <div class="article-meta">
                <p><strong>Category:</strong> <?php echo htmlspecialchars($tour['category']); ?> | 
                   <strong>Destination:</strong> <?php echo htmlspecialchars($tour['destination_name']); ?> | 
                   <strong>Posted on:</strong> <?php echo htmlspecialchars($tour['created_at']); ?></p>
            </div>
        </div>

        <div class="tour-details">
            <h3>Tour Details</h3>
            <ul>
                <?php if ($tour['duration']): ?>
                    <li><strong>Duration:</strong> <?php echo htmlspecialchars($tour['duration']); ?></li>
                <?php endif; ?>
                <?php if ($tour['price']): ?>
                    <li><strong>Price:</strong> $<?php echo number_format($tour['price'], 2); ?></li>
                <?php endif; ?>
                <?php if ($tour['inclusions']): ?>
                    <li><strong>Inclusions:</strong> <?php echo htmlspecialchars($tour['inclusions']); ?></li>
                <?php endif; ?>
                <?php if ($tour['exclusions']): ?>
                    <li><strong>Exclusions:</strong> <?php echo htmlspecialchars($tour['exclusions']); ?></li>
                <?php endif; ?>
                <?php if ($tour['difficulty_level']): ?>
                    <li><strong>Difficulty Level:</strong> <?php echo htmlspecialchars($tour['difficulty_level']); ?></li>
                <?php endif; ?>
                <?php if ($tour['group_size']): ?>
                    <li><strong>Group Size:</strong> <?php echo htmlspecialchars($tour['group_size']); ?></li>
                <?php endif; ?>
                <?php if ($tour['availability']): ?>
                    <li><strong>Availability:</strong> <?php echo htmlspecialchars($tour['availability']); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="article-content">
            <img src="<?php echo htmlspecialchars($tour['images']); ?>" alt="<?php echo htmlspecialchars($tour['title']); ?>">
            <p><?php echo htmlspecialchars($tour['description']); ?></p>
            <?php if ($tour['additional_images']): ?>
                <div class="gallery">
                    <h3>Gallery</h3>
                    <?php
                    $images = explode(',', $tour['additional_images']);
                    foreach ($images as $image):
                    ?>
                        <img src="<?php echo htmlspecialchars(trim($image)); ?>" alt="Tour Image">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="social-share">
                <h3>Share This Tour</h3>
                <a href="https://twitter.com/intent/tweet?text=Check out this tour: <?php echo urlencode($tour['title']); ?>&url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank"><i class="bi bi-twitter"></i> Twitter</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank"><i class="bi bi-facebook"></i> Facebook</a>
            </div>
            <button type="button" class="btn btn-booking mt-3" data-bs-toggle="modal" data-bs-target="#bookingModal">Book Now</button>
        </div>

        <?php if (!empty($related_tours)): ?>
            <div class="related-tours">
                <h3>Related Tours</h3>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($related_tours as $related_tour): ?>
                        <div class="col">
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($related_tour['images']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($related_tour['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($related_tour['title']); ?></h5>
                                    <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($related_tour['category']); ?></p>
                                    <p class="card-text"><strong>Destination:</strong> <?php echo htmlspecialchars($related_tour['destination']); ?></p>
                                    <a href="tour_details.php?id=<?php echo $related_tour['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Book Tour: <?php echo htmlspecialchars($tour['title']); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="booking_create.php" method="POST" id="bookingForm">
                    <div class="modal-body">
                        <input type="hidden" name="tour_id" value="<?php echo $tour['id']; ?>">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars(($user['FirstName'] ?? '') . ' ' . ($user['LastName'] ?? '')); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['PhoneNumber'] ?? ''); ?>" pattern="[0-9]{10,15}" required>
                            <small class="form-text text-muted">Enter 10-15 digits (e.g., 1234567890).</small>
                        </div>
                        <div class="mb-3">
                            <label for="participants" class="form-label">Number of Participants</label>
                            <input type="number" id="participants" name="participants" class="form-control" 
                                   min="1" max="<?php echo htmlspecialchars($tour['group_size']); ?>" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="booking_date" class="form-label">Booking Date</label>
                            <input type="date" id="booking_date" name="booking_date" class="form-control" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Special Requests</label>
                            <textarea id="special_requests" name="special_requests" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="total_price" class="form-label">Total Price ($)</label>
                            <input type="text" id="total_price" name="total_price" class="form-control" 
                                   value="<?php echo number_format($tour['price'], 2); ?>" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-booking">Proceed to Payment</button>
                    </div>
                </form>
            </div>
        </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        const pricePerPerson = <?php echo $tour['price'] ?? 0; ?>;
        const participantsInput = document.getElementById('participants');
        const totalPriceInput = document.getElementById('total_price');

        participantsInput.addEventListener('input', function() {
            const participants = parseInt(this.value) || 1;
            totalPriceInput.value = (pricePerPerson * participants).toFixed(2);
        });

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            if (!/^[0-9]{10,15}$/.test(phone)) {
                e.preventDefault();
                alert('Please enter a valid phone number (10-15 digits).');
            }
        });
    </script>
</body>
</html>