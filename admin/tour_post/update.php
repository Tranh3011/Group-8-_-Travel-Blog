<?php
session_start();

// Check admin login
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: ../auth/admin_login.php");
//     exit();
// }

// Connect to database
require_once '../../database/connect-db.php';
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Initialize variables
$errors = [];
$tour = [
    'id' => '',
    'title' => '',
    'category_id' => '',
    'destination' => '',
    'description' => '',
    'images' => '',
    'duration' => '',
    'price' => '',
    'inclusions' => '',
    'exclusions' => '',
    'difficulty_level' => '',
    'group_size' => '',
    'availability' => '',
    'additional_images' => ''
];

// Fetch categories
$sql_categories = "SELECT CategoryID, Name FROM category";
$result_categories = $conn->query($sql_categories) or die("Category query failed: " . $conn->error);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

// Fetch destinations
$sql_destinations = "SELECT DestinationID, Name FROM destination";
$result_destinations = $conn->query($sql_destinations) or die("Destination query failed: " . $conn->error);
$destinations = $result_destinations->fetch_all(MYSQLI_ASSOC);

// Fetch tour post data if ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $tour_id = (int)$_GET['id'];
    $sql = "SELECT id, title, category_id, destination, description, images, duration, price, inclusions, exclusions,
            difficulty_level, group_size, availability, additional_images
            FROM tour_posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $tour_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $tour = $result->fetch_assoc();
    } else {
        $errors['general'] = 'Tour post not found.';
    }
    $stmt->close();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $tour_id = (int)($_POST['id'] ?? 0);
    $tour['title'] = trim($_POST['title'] ?? '');
    $tour['category_id'] = trim($_POST['category_id'] ?? '');
    $tour['destination'] = trim($_POST['destination'] ?? '');
    $tour['description'] = trim($_POST['description'] ?? '');
    $tour['duration'] = trim($_POST['duration'] ?? '');
    $tour['price'] = trim($_POST['price'] ?? '');
    $tour['inclusions'] = trim($_POST['inclusions'] ?? '');
    $tour['exclusions'] = trim($_POST['exclusions'] ?? '');
    $tour['difficulty_level'] = trim($_POST['difficulty_level'] ?? '');
    $tour['group_size'] = trim($_POST['group_size'] ?? '');
    $tour['availability'] = trim($_POST['availability'] ?? '');
    $fileImage = $_FILES['fileImage'] ?? null;
    $additionalImages = $_FILES['additional_images'] ?? null;

    // Validate required fields
    if (empty($tour['title'])) {
        $errors['title'] = 'Title is required.';
    }
    if (empty($tour['category_id'])) {
        $errors['category_id'] = 'Category is required.';
    }
    if (empty($tour['destination'])) {
        $errors['destination'] = 'Destination is required.';
    }
    if (empty($tour['description'])) {
        $errors['description'] = 'Description is required.';
    }
    if (empty($tour['duration'])) {
        $errors['duration'] = 'Duration is required.';
    }
    if (empty($tour['price']) || !is_numeric($tour['price'])) {
        $errors['price'] = 'Valid price is required.';
    }

    // Validate main image
    if ($fileImage && !empty($fileImage['tmp_name'])) {
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'png', 'jpeg'])) {
            $errors['fileImage'] = 'Invalid file type, expect png, jpg, jpeg.';
        }
        if ($fileImage["size"] > 20 * 1024 * 1024) {
            $errors['fileImage'] = 'File too large, expect <= 20MB.';
        }
    }

    // Validate additional images
    $additional_images_array = [];
    if ($additionalImages && !empty($additionalImages['tmp_name'][0])) {
        foreach ($additionalImages['tmp_name'] as $index => $tmp_name) {
            if (!empty($tmp_name)) {
                $fileType = strtolower(pathinfo($additionalImages['name'][$index], PATHINFO_EXTENSION));
                if (!in_array($fileType, ['jpg', 'png', 'jpeg'])) {
                    $errors['additional_images'] = 'Invalid file type for additional images, expect png, jpg, jpeg.';
                    break;
                }
                if ($additionalImages["size"][$index] > 20 * 1024 * 1024) {
                    $errors['additional_images'] = 'Additional image too large, expect <= 20MB.';
                    break;
                }
            }
        }
    }

    // Process form if no errors
    if (empty($errors)) {
        // Handle main image upload
        $image_path = $tour['images'];
        if ($fileImage && !empty($fileImage['tmp_name'])) {
            $image_path = "../../uploads/" . basename($fileImage["name"]);
            if (!move_uploaded_file($fileImage["tmp_name"], $image_path)) {
                $errors['fileImage'] = 'Failed to upload main image.';
            }
        }

        // Handle additional images upload
        if ($additionalImages && !empty($additionalImages['tmp_name'][0]) && empty($errors)) {
            foreach ($additionalImages['tmp_name'] as $index => $tmp_name) {
                if (!empty($tmp_name)) {
                    $add_image_path = "../../uploads/" . basename($additionalImages["name"][$index]);
                    if (move_uploaded_file($tmp_name, $add_image_path)) {
                        $additional_images_array[] = $add_image_path;
                    } else {
                        $errors['additional_images'] = 'Failed to upload additional image.';
                        break;
                    }
                }
            }
            $tour['additional_images'] = implode(',', $additional_images_array);
        }

        // Update database
        if (empty($errors)) {
            $sql = "UPDATE tour_posts SET title = ?, category_id = ?, destination = ?, description = ?, images = ?,
                    duration = ?, price = ?, inclusions = ?, exclusions = ?, difficulty_level = ?, group_size = ?,
                    availability = ?, additional_images = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param(
                "siissssssssssi",
                $tour['title'],
                $tour['category_id'],
                $tour['destination'],
                $tour['description'],
                $image_path,
                $tour['duration'],
                $tour['price'],
                $tour['inclusions'],
                $tour['exclusions'],
                $tour['difficulty_level'],
                $tour['group_size'],
                $tour['availability'],
                $tour['additional_images'],
                $tour_id
            );
            if ($stmt->execute()) {
                $success = "Tour post updated successfully! Redirecting to index.php in 3 seconds...";
            } else {
                $errors['general'] = 'Failed to update tour post: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Update Tour Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
        .form-section {
            background-color: #F1FEFC;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
        .current-image {
            max-width: 100px;
            border-radius: 5px;
        }
        @media (max-width: 768px) {
            .form-section {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include '../../inc/_navbar.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Update Tour Post</h1>

        <div class="form-section">
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                    <script>
                        setTimeout(() => { window.location.href = 'index.php'; }, 3000);
                    </script>
                </div>
            <?php elseif (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($tour['id']) && empty($errors)): ?>
                <div class="alert alert-warning">No tour post selected. <a href="index.php">Return to tour list</a>.</div>
            <?php else: ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($tour['id']) ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($tour['title']) ?>">
                            <?php if (isset($errors['title'])): ?>
                                <span class="text-danger"><?= $errors['title'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select id="category_id" name="category_id" class="form-control">
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['CategoryID'] ?>" <?= $tour['category_id'] == $cat['CategoryID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['Name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                                <span class="text-danger"><?= $errors['category_id'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="destination" class="form-label">Destination</label>
                            <select id="destination" name="destination" class="form-control">
                                <option value="">Select a destination</option>
                                <?php foreach ($destinations as $dest): ?>
                                    <option value="<?= $dest['DestinationID'] ?>" <?= $tour['destination'] == $dest['DestinationID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dest['Name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['destination'])): ?>
                                <span class="text-danger"><?= $errors['destination'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="duration" class="form-label">Duration</label>
                            <input type="text" id="duration" name="duration" class="form-control" value="<?= htmlspecialchars($tour['duration']) ?>">
                            <?php if (isset($errors['duration'])): ?>
                                <span class="text-danger"><?= $errors['duration'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price ($)</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" value="<?= htmlspecialchars($tour['price']) ?>">
                            <?php if (isset($errors['price'])): ?>
                                <span class="text-danger"><?= $errors['price'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="difficulty_level" class="form-label">Difficulty Level</label>
                            <input type="text" id="difficulty_level" name="difficulty_level" class="form-control" value="<?= htmlspecialchars($tour['difficulty_level']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="5"><?= htmlspecialchars($tour['description']) ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="text-danger"><?= $errors['description'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="inclusions" class="form-label">Inclusions</label>
                        <textarea id="inclusions" name="inclusions" class="form-control" rows="3"><?= htmlspecialchars($tour['inclusions']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="exclusions" class="form-label">Exclusions</label>
                        <textarea id="exclusions" name="exclusions" class="form-control" rows="3"><?= htmlspecialchars($tour['exclusions']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="group_size" class="form-label">Group Size</label>
                            <input type="text" id="group_size" name="group_size" class="form-control" value="<?= htmlspecialchars($tour['group_size']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="availability" class="form-label">Availability</label>
                            <input type="text" id="availability" name="availability" class="form-control" value="<?= htmlspecialchars($tour['availability']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fileImage" class="form-label">Main Image</label>
                        <?php if ($tour['images']): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($tour['images']) ?>" class="current-image" alt="Current Image">
                            </div>
                        <?php endif; ?>
                        <input type="file" id="fileImage" name="fileImage" class="form-control">
                        <?php if (isset($errors['fileImage'])): ?>
                            <span class="text-danger"><?= $errors['fileImage'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="additional_images" class="form-label">Additional Images</label>
                        <?php if ($tour['additional_images']): ?>
                            <div class="mb-2">
                                <?php $add_images = explode(',', $tour['additional_images']); ?>
                                <?php foreach ($add_images as $img): ?>
                                    <img src="<?= htmlspecialchars(trim($img)) ?>" class="current-image me-2" alt="Additional Image">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="additional_images" name="additional_images[]" class="form-control" multiple>
                        <?php if (isset($errors['additional_images'])): ?>
                            <span class="text-danger"><?= $errors['additional_images'] ?></span>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Tour</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <footer>
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
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>