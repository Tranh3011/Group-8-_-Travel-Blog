<?php
session_start();

require_once '../../database/connect-db.php';
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
$errors = [];
$success_message = '';
$user_data = [];
$tour_posts = [];
$bookings = [];


// Lấy thông tin user
// --- DEVELOPMENT MODE START ---
// Comment out the line below and uncomment the session check for production
// $user_id = 1; // Default UserID for development
// --- DEVELOPMENT MODE END ---

// --- PRODUCTION MODE START ---
// Uncomment the lines below for production
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
// --- PRODUCTION MODE END ---

$sql_user = "SELECT * FROM user WHERE UserID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$stmt_user->close();

// Xử lý cập nhật thông tin cá nhân
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);

    if (empty($first_name)) $errors['first_name'] = "First name is required";
    if (empty($last_name)) $errors['last_name'] = "Last name is required";
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    if (empty($errors)) {
        $sql_update = "UPDATE user SET FirstName=?, LastName=?, Email=?, City=?, Country=? WHERE UserID=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssssi", $first_name, $last_name, $email, $city, $country, $user_id);
        
        if ($stmt_update->execute()) {
            $success_message = "Profile updated successfully!";
            $_SESSION['email'] = $email;
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();
            $user_data = $result_user->fetch_assoc();
            $stmt_user->close();
        } else {
            $errors['database'] = "Error updating profile: " . $conn->error;
        }
        $stmt_update->close();
    }
}

// Lấy danh sách tour posts của user
$sql_tours = "SELECT t.id, t.title, t.description, t.images, t.created_at, t.status, c.Name as category, d.Name as destination
              FROM tour_posts t
              JOIN category c ON t.category_id = c.CategoryID
              JOIN destination d ON t.destination = d.DestinationID
              WHERE t.user_id = ? ORDER BY t.created_at DESC";
$stmt_tours = $conn->prepare($sql_tours);
$stmt_tours->bind_param("i", $user_id);
$stmt_tours->execute();
$result_tours = $stmt_tours->get_result();
$tour_posts = $result_tours->fetch_all(MYSQLI_ASSOC);
$stmt_tours->close();

// Xử lý tạo tour post mới
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_tour'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = trim($_POST['category_id']);
    $destination_id = trim($_POST['destination_id']);
    $duration = trim($_POST['duration']);
    $price = trim($_POST['price']);
    $inclusions = trim($_POST['inclusions']);
    $exclusions = trim($_POST['exclusions']);
    $difficulty_level = trim($_POST['difficulty_level']);
    $group_size = trim($_POST['group_size']);
    $availability = trim($_POST['availability']);
    $fileImage = isset($_FILES['fileImage']) ? $_FILES['fileImage'] : null;

    $title = htmlspecialchars($title);
    $description = htmlspecialchars($description);

    if (empty($title)) $errors['tour_title'] = "Title is required";
    if (empty($description)) $errors['tour_description'] = "Description is required";
    if (empty($category_id)) $errors['tour_category'] = "Category is required";
    if (empty($destination_id)) $errors['tour_destination'] = "Destination is required";
    if ($fileImage && $fileImage['error'] == UPLOAD_ERR_OK) {
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            $errors['tour_image'] = "Invalid file type, only JPG, JPEG, PNG are allowed.";
        }
        if ($fileImage["size"] > 20 * 1024 * 1024) {
            $errors['tour_image'] = "File is too large, expect smaller than 20MB";
        }
    } else {
        $errors['tour_image'] = "Image is required";
    }

    if (empty($errors)) {
        $upload_dir = "../../../uploads/";
        $image_name = uniqid() . '_' . basename($fileImage["name"]);
        $image_path = $upload_dir . $image_name;
        
        if (move_uploaded_file($fileImage["tmp_name"], $image_path)) {
            $sql_insert = "INSERT INTO tour_posts (user_id, title, description, category_id, destination, images, duration, price, inclusions, exclusions, difficulty_level, group_size, availability, status, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ississsdsssss", $user_id, $title, $description, $category_id, $destination_id, $image_path, $duration, $price, $inclusions, $exclusions, $difficulty_level, $group_size, $availability);
            
            if ($stmt_insert->execute()) {
                $success_message = "Tour post created and sent for admin approval!";
                $stmt_tours = $conn->prepare($sql_tours);
                $stmt_tours->bind_param("i", $user_id);
                $stmt_tours->execute();
                $result_tours = $stmt_tours->get_result();
                $tour_posts = $result_tours->fetch_all(MYSQLI_ASSOC);
                $stmt_tours->close();
            } else {
                $errors['database'] = "Error creating tour post: " . $conn->error;
            }
            $stmt_insert->close();
        } else {
            $errors['tour_image'] = "Error uploading image";
        }
    }
}

// Xử lý cập nhật tour post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_tour'])) {
    $tour_id = (int)$_POST['tour_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = trim($_POST['category_id']);
    $destination_id = trim($_POST['destination_id']);
    $duration = trim($_POST['duration']);
    $price = trim($_POST['price']);
    $inclusions = trim($_POST['inclusions']);
    $exclusions = trim($_POST['exclusions']);
    $difficulty_level = trim($_POST['difficulty_level']);
    $group_size = trim($_POST['group_size']);
    $availability = trim($_POST['availability']);
    $fileImage = isset($_FILES['fileImage']) ? $_FILES['fileImage'] : null;

    $title = htmlspecialchars($title);
    $description = htmlspecialchars($description);

    if (empty($title)) $errors['tour_title'] = "Title is required";
    if (empty($description)) $errors['tour_description'] = "Description is required";
    if (empty($category_id)) $errors['tour_category'] = "Category is required";
    if (empty($destination_id)) $errors['tour_destination'] = "Destination is required";

    $image_path = null;
    if ($fileImage && $fileImage['error'] == UPLOAD_ERR_OK) {
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            $errors['tour_image'] = "Invalid file type, only JPG, JPEG, PNG are allowed.";
        }
        if ($fileImage["size"] > 20 * 1024 * 1024) {
            $errors['tour_image'] = "File is too large, expect smaller than 20MB";
        }
        if (empty($errors)) {
            $upload_dir = "../../../uploads/";
            $image_name = uniqid() . '_' . basename($fileImage["name"]);
            $image_path = $upload_dir . $image_name;
            move_uploaded_file($fileImage["tmp_name"], $image_path);
        }
    }

    if (empty($errors)) {
        if ($image_path) {
            $sql_update = "UPDATE tour_posts SET title=?, description=?, category_id=?, destination=?, duration=?, price=?, inclusions=?, exclusions=?, difficulty_level=?, group_size=?, availability=?, images=? WHERE id=? AND user_id=?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssisssdsssssii", $title, $description, $category_id, $destination_id, $duration, $price, $inclusions, $exclusions, $difficulty_level, $group_size, $availability, $image_path, $tour_id, $user_id);
        } else {
            $sql_update = "UPDATE tour_posts SET title=?, description=?, category_id=?, destination=?, duration=?, price=?, inclusions=?, exclusions=?, difficulty_level=?, group_size=?, availability=? WHERE id=? AND user_id=?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssisssdssssii", $title, $description, $category_id, $destination_id, $duration, $price, $inclusions, $exclusions, $difficulty_level, $group_size, $availability, $tour_id, $user_id);
        }
        
        if ($stmt_update->execute()) {
            $success_message = "Tour post updated successfully!";
            $stmt_tours = $conn->prepare($sql_tours);
            $stmt_tours->bind_param("i", $user_id);
            $stmt_tours->execute();
            $result_tours = $stmt_tours->get_result();
            $tour_posts = $result_tours->fetch_all(MYSQLI_ASSOC);
            $stmt_tours->close();
        } else {
            $errors['database'] = "Error updating tour post: " . $conn->error;
        }
        $stmt_update->close();
    }
}

// Xử lý xóa tour post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_tour'])) {
    $tour_id = (int)$_POST['tour_id'];
    $sql_delete = "DELETE FROM tour_posts WHERE id=? AND user_id=?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $tour_id, $user_id);
    
    if ($stmt_delete->execute()) {
        $success_message = "Tour post deleted successfully!";
        $stmt_tours = $conn->prepare($sql_tours);
        $stmt_tours->bind_param("i", $user_id);
        $stmt_tours->execute();
        $result_tours = $stmt_tours->get_result();
        $tour_posts = $result_tours->fetch_all(MYSQLI_ASSOC);
        $stmt_tours->close();
    } else {
        $errors['database'] = "Error deleting tour post: " . $conn->error;
    }
    $stmt_delete->close();
}
// Lấy danh sách bookings
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql_bookings = "SELECT b.id, b.tour_post_id, b.guest_full_name, b.num_people, b.travel_date, b.end_date, t.price, b.status, b.created_at, b.email, b.phone, b.special_requests, 
                 t.title, c.Name as category, d.Name as destination
                 FROM tour_booking b
                 JOIN tour_posts t ON b.tour_post_id = t.id
                 JOIN category c ON t.category_id = c.CategoryID
                 JOIN destination d ON t.destination = d.DestinationID
                 WHERE b.owner_user_id = ?";

$filter_params = [];
$filter_types = "i";
if (isset($_GET['tour_name']) && !empty($_GET['tour_name'])) {
    $sql_bookings .= " AND t.title LIKE ?";
    $filter_params[] = '%' . $_GET['tour_name'] . '%';
    $filter_types .= "s";
}
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $sql_bookings .= " AND c.CategoryID = ?";
    $filter_params[] = $_GET['category'];
    $filter_types .= "i";
}
if (isset($_GET['destination']) && !empty($_GET['destination'])) {
    $sql_bookings .= " AND d.DestinationID = ?";
    $filter_params[] = $_GET['destination'];
    $filter_types .= "i";
}
if (isset($_GET['request_date']) && !empty($_GET['request_date'])) {
    $sql_bookings .= " AND DATE(b.created_at) = ?";
    $filter_params[] = $_GET['request_date'];
    $filter_types .= "s";
}
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $sql_bookings .= " AND b.status = ?";
    $filter_params[] = $_GET['status'];
    $filter_types .= "s";
}

$filter_params = array_merge([$user_id], $filter_params);

$count_sql = "SELECT COUNT(*) as total FROM ($sql_bookings) as temp";
$stmt_count = $conn->prepare($count_sql);
$stmt_count->bind_param($filter_types, ...$filter_params);
$stmt_count->execute();
$total_bookings = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_bookings / $limit);
$stmt_count->close();

$sql_bookings .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
$filter_params[] = $limit;
$filter_params[] = $offset;
$filter_types .= "ii";
$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param($filter_types, ...$filter_params);
$stmt_bookings->execute();
$bookings = $stmt_bookings->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_bookings->close();

// Lấy danh sách my posts (bài viết của user)
$sql_my_posts = "SELECT p.PostID, p.Title, p.Content, p.Image, c.Name AS category_name, d.Name AS destination_name, p.Created_at, p.Updated_at
                 FROM posts p
                 LEFT JOIN category c ON p.CategoryID = c.CategoryID
                 LEFT JOIN destination d ON p.DestinationID = d.DestinationID
                 WHERE p.UserID = ?
                 ORDER BY p.Created_at DESC";
$stmt_my_posts = $conn->prepare($sql_my_posts);
$stmt_my_posts->bind_param("i", $user_id);
$stmt_my_posts->execute();
$my_posts = $stmt_my_posts->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_my_posts->close();

// Xử lý tạo bài viết mới
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_post'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = trim($_POST['category_id']);
    $destination_id = trim($_POST['destination_id']);
    $fileImage = isset($_FILES['fileImage']) ? $_FILES['fileImage'] : null;

    $title = htmlspecialchars($title);
    $content = htmlspecialchars($content);

    if (empty($title)) $errors['post_title'] = "Title is required";
    if (empty($content)) $errors['post_content'] = "Content is required";
    if (empty($category_id)) $errors['post_category'] = "Category is required";
    if (empty($destination_id)) $errors['post_destination'] = "Destination is required";
    if ($fileImage && $fileImage['error'] == UPLOAD_ERR_OK) {
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            $errors['post_image'] = "Invalid file type, only JPG, JPEG, PNG are allowed.";
        }
        if ($fileImage["size"] > 20 * 1024 * 1024) {
            $errors['post_image'] = "File is too large, expect smaller than 20MB";
        }
    } else {
        $errors['post_image'] = "Image is required";
    }

    if (empty($errors)) {
        $upload_dir = "../../../uploads/";
        $image_name = uniqid() . '_' . basename($fileImage["name"]);
        $image_path = $upload_dir . $image_name;
        
        if (move_uploaded_file($fileImage["tmp_name"], $image_path)) {
            $sql_insert = "INSERT INTO posts (UserID, Title, Content, CategoryID, DestinationID, Image, Created_at, Updated_at) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("issssi", $user_id, $title, $content, $category_id, $destination_id, $image_path);
            
            if ($stmt_insert->execute()) {
                $success_message = "Post created successfully!";
                $stmt_my_posts = $conn->prepare($sql_my_posts);
                $stmt_my_posts->bind_param("i", $user_id);
                $stmt_my_posts->execute();
                $my_posts = $stmt_my_posts->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt_my_posts->close();
            } else {
                $errors['database'] = "Error creating post: " . $conn->error;
            }
            $stmt_insert->close();
        } else {
            $errors['post_image'] = "Error uploading image";
        }
    }
}

// Xử lý cập nhật bài viết
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_post'])) {
    $post_id = (int)$_POST['post_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = trim($_POST['category_id']);
    $destination_id = trim($_POST['destination_id']);
    $fileImage = isset($_FILES['fileImage']) ? $_FILES['fileImage'] : null;

    $title = htmlspecialchars($title);
    $content = htmlspecialchars($content);

    if (empty($title)) $errors['post_title'] = "Title is required";
    if (empty($content)) $errors['post_content'] = "Content is required";
    if (empty($category_id)) $errors['post_category'] = "Category is required";
    if (empty($destination_id)) $errors['post_destination'] = "Destination is required";

    $image_path = null;
    if ($fileImage && $fileImage['error'] == UPLOAD_ERR_OK) {
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            $errors['post_image'] = "Invalid file type, only JPG, JPEG, PNG are allowed.";
        }
        if ($fileImage["size"] > 20 * 1024 * 1024) {
            $errors['post_image'] = "File is too large, expect smaller than 20MB";
        }
        if (empty($errors)) {
            $upload_dir = "../../../uploads/";
            $image_name = uniqid() . '_' . basename($fileImage["name"]);
            $image_path = $upload_dir . $image_name;
            move_uploaded_file($fileImage["tmp_name"], $image_path);
        }
    }

    if (empty($errors)) {
        if ($image_path) {
            $sql_update = "UPDATE posts SET Title=?, Content=?, CategoryID=?, DestinationID=?, Image=?, Updated_at=NOW() WHERE PostID=? AND UserID=?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssissii", $title, $content, $category_id, $destination_id, $image_path, $post_id, $user_id);
        } else {
            $sql_update = "UPDATE posts SET Title=?, Content=?, CategoryID=?, DestinationID=?, Updated_at=NOW() WHERE PostID=? AND UserID=?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssissi", $title, $content, $category_id, $destination_id, $post_id, $user_id);
        }
        
        if ($stmt_update->execute()) {
            $success_message = "Post updated successfully!";
            $stmt_my_posts = $conn->prepare($sql_my_posts);
            $stmt_my_posts->bind_param("i", $user_id);
            $stmt_my_posts->execute();
            $my_posts = $stmt_my_posts->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt_my_posts->close();
        } else {
            $errors['database'] = "Error updating post: " . $conn->error;
        }
        $stmt_update->close();
    }
}

// Xử lý xóa bài viết
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_post'])) {
    $post_id = (int)$_POST['post_id'];
    $sql_delete = "DELETE FROM posts WHERE PostID=? AND UserID=?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $post_id, $user_id);
    
    if ($stmt_delete->execute()) {
        $success_message = "Post deleted successfully!";
        $stmt_my_posts = $conn->prepare($sql_my_posts);
        $stmt_my_posts->bind_param("i", $user_id);
        $stmt_my_posts->execute();
        $my_posts = $stmt_my_posts->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_my_posts->close();
    } else {
        $errors['database'] = "Error deleting post: " . $conn->error;
    }
    $stmt_delete->close();
}

// Lấy danh sách categories và destinations
$sql_categories = "SELECT * FROM category";
$result_categories = $conn->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

$sql_destinations = "SELECT * FROM destination";
$result_destinations = $conn->query($sql_destinations);
$destinations = $result_destinations->fetch_all(MYSQLI_ASSOC);

$conn->close();

// Function to build query string for pagination
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
    <title>User Profile - TravelBlog</title>
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

        .navbar-nav .dropdown-menu {
            background-color: #123458;
            border: none;
        }

        .navbar-nav .dropdown-item {
            color: #F1FEFC;
            padding: 10px 20px;
        }

        .navbar-nav .dropdown-item:hover {
            background-color: #D4C9BE;
            color: #030303;
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

        .avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }

        .post-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
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
            padding-top: 40px;
            padding-bottom: 40px;
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
            .card-img-top {
                height: 150px;
            }

            .avatar {
                width: 100px;
                height: 100px;
            }

            footer h5 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../../inc/_navbar.php'; ?>
    <div class="container py-5">
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['database'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errors['database']) ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="profile-section text-center">
                    <img src="<?= htmlspecialchars($user_data['Avatar'] ?? '../../ploads/default-avatar.jpg') ?>" 
                         alt="Profile Picture" class="avatar mb-3">
                    <h3><?= htmlspecialchars($user_data['FirstName'] . ' ' . $user_data['LastName']) ?></h3>
                    <p class="text-muted"><?= htmlspecialchars($user_data['Email']) ?></p>
                    <p>
                        <i class="bi bi-geo-alt"></i> 
                        <?= htmlspecialchars($user_data['City'] . ', ' . $user_data['Country']) ?>
                    </p>
                </div>
            </div>
            
            <div class="col-md-8">
                <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" 
                                data-bs-target="#edit-profile" type="button" role="tab">Edit Profile</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tours-tab" data-bs-toggle="tab" 
                                data-bs-target="#my-tours" type="button" role="tab">My Tours</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="create-tour-tab" data-bs-toggle="tab" 
                                data-bs-target="#create-tour" type="button" role="tab">Create Tour</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" 
                                data-bs-target="#tour-bookings" type="button" role="tab">Tour Bookings</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="my-requests-tab" data-bs-toggle="tab" 
                                data-bs-target="#my-requests" type="button" role="tab">My Requests</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="my-posts-tab" data-bs-toggle="tab"
                                data-bs-target="#my-posts" type="button" role="tab">My Posts</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="create-post-tab" data-bs-toggle="tab"
                                data-bs-target="#create-post" type="button" role="tab">Create Post</button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- Tab Edit Profile -->
                    <div class="tab-pane fade show active" id="edit-profile" role="tabpanel">
                        <div class="profile-section">
                            <h4>Edit Profile</h4>
                            <form method="POST" action="">
                                <input type="hidden" name="update_profile" value="1">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                               id="first_name" name="first_name" 
                                               value="<?= htmlspecialchars($user_data['FirstName'] ?? '') ?>">
                                        <?php if (isset($errors['first_name'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                               id="last_name" name="last_name" 
                                               value="<?= htmlspecialchars($user_data['LastName'] ?? '') ?>">
                                        <?php if (isset($errors['last_name'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                           id="email" name="email" 
                                           value="<?= htmlspecialchars($user_data['Email'] ?? '') ?>">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                               value="<?= htmlspecialchars($user_data['City'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" 
                                               value="<?= htmlspecialchars($user_data['Country'] ?? '') ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tab My Tours -->
                    <div class="tab-pane fade" id="my-tours" role="tabpanel">
                        <div class="posts-section">
                            <h4>My Tours</h4>
                            <?php if (empty($tour_posts)): ?>
                                <div class="alert alert-info">You haven't created any tour posts yet.</div>
                            <?php else: ?>
                                <div class="row row-cols-1 row-cols-md-2 g-4">
                                    <?php foreach ($tour_posts as $tour): ?>
                                        <div class="col">
                                            <div class="card">
                                                <img src="<?= htmlspecialchars($tour['images']) ?>" class="card-img-top" alt="<?= htmlspecialchars($tour['title']) ?>">
                                                <div class="card-body">
                                                    <h5 class="card-title"><?= htmlspecialchars($tour['title']) ?></h5>
                                                    <p class="card-text"><?= htmlspecialchars(substr($tour['description'], 0, 100)) ?>...</p>
                                                    <p class="card-text"><strong>Category:</strong> <?= htmlspecialchars($tour['category']) ?></p>
                                                    <p class="card-text"><strong>Destination:</strong> <?= htmlspecialchars($tour['destination']) ?></p>
                                                    <p class="card-text"><strong>Status:</strong> <?= htmlspecialchars($tour['status']) ?></p>
                                                    <p class="card-text"><small class="text-muted">Posted on: <?= date('M d, Y', strtotime($tour['created_at'])) ?></small></p>
                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editTourModal<?= $tour['id'] ?>">Edit</button>
                                                    <form method="POST" action="" style="display:inline;">
                                                        <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                                                        <input type="hidden" name="delete_tour" value="1">
                                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this tour?')">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Edit Tour Modal -->
                                        <div class="modal fade" id="editTourModal<?= $tour['id'] ?>" tabindex="-1" aria-labelledby="editTourModalLabel<?= $tour['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editTourModalLabel<?= $tour['id'] ?>">Edit Tour: <?= htmlspecialchars($tour['title']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST" action="" enctype="multipart/form-data">
                                                            <input type="hidden" name="update_tour" value="1">
                                                            <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                                                            <div class="mb-3">
                                                                <label for="title<?= $tour['id'] ?>" class="form-label">Title</label>
                                                                <input type="text" class="form-control" id="title<?= $tour['id'] ?>" name="title" value="<?= htmlspecialchars($tour['title']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="description<?= $tour['id'] ?>" class="form-label">Description</label>
                                                                <textarea class="form-control" id="description<?= $tour['id'] ?>" name="description" rows="5" required><?= htmlspecialchars($tour['description']) ?></textarea>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <label for="category_id<?= $tour['id'] ?>" class="form-label">Category</label>
                                                                    <select class="form-control" id="category_id<?= $tour['id'] ?>" name="category_id" required>
                                                                        <option value="">Select Category</option>
                                                                        <?php foreach ($categories as $category): ?>
                                                                            <option value="<?= $category['CategoryID'] ?>" <?= $category['Name'] == $tour['category'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($category['Name']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="destination_id<?= $tour['id'] ?>" class="form-label">Destination</label>
                                                                    <select class="form-control" id="destination_id<?= $tour['id'] ?>" name="destination_id" required>
                                                                        <option value="">Select Destination</option>
                                                                        <?php foreach ($destinations as $destination): ?>
                                                                            <option value="<?= $destination['DestinationID'] ?>" <?= $destination['Name'] == $tour['destination'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($destination['Name']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="duration<?= $tour['id'] ?>" class="form-label">Duration</label>
                                                                <input type="text" class="form-control" id="duration<?= $tour['id'] ?>" name="duration" value="<?= htmlspecialchars($tour['duration'] ?? '') ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="price<?= $tour['id'] ?>" class="form-label">Price</label>
                                                                <input type="number" step="0.01" class="form-control" id="price<?= $tour['id'] ?>" name="price" value="<?= htmlspecialchars($tour['price'] ?? '') ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="inclusions<?= $tour['id'] ?>" class="form-label">Inclusions</label>
                                                                <textarea class="form-control" id="inclusions<?= $tour['id'] ?>" name="inclusions" rows="3"><?= htmlspecialchars($tour['inclusions'] ?? '') ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="exclusions<?= $tour['id'] ?>" class="form-label">Exclusions</label>
                                                                <textarea class="form-control" id="exclusions<?= $tour['id'] ?>" name="exclusions" rows="3"><?= htmlspecialchars($tour['exclusions'] ?? '') ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="difficulty_level<?= $tour['id'] ?>" class="form-label">Difficulty Level</label>
                                                                <input type="text" class="form-control" id="difficulty_level<?= $tour['id'] ?>" name="difficulty_level" value="<?= htmlspecialchars($tour['difficulty_level'] ?? '') ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="group_size<?= $tour['id'] ?>" class="form-label">Group Size</label>
                                                                <input type="text" class="form-control" id="group_size<?= $tour['id'] ?>" name="group_size" value="<?= htmlspecialchars($tour['group_size'] ?? '') ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="availability<?= $tour['id'] ?>" class="form-label">Availability</label>
                                                                <textarea class="form-control" id="availability<?= $tour['id'] ?>" name="availability" rows="3"><?= htmlspecialchars($tour['availability'] ?? '') ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="fileImage<?= $tour['id'] ?>" class="form-label">Image</label>
                                                                <input type="file" class="form-control" id="fileImage<?= $tour['id'] ?>" name="fileImage" accept="image/jpeg,image/png">
                                                                <small class="text-muted">Current image: <img src="<?= htmlspecialchars($tour['images']) ?>" alt="Current image" style="max-width: 100px; margin-top: 10px;"></small>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">Update Tour</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Tab Create Tour -->
                    <div class="tab-pane fade" id="create-tour" role="tabpanel">
                        <div class="posts-section">
                            <h4>Create New Tour</h4>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="create_tour" value="1">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control <?= isset($errors['tour_title']) ? 'is-invalid' : '' ?>" 
                                           id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                                    <?php if (isset($errors['tour_title'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['tour_title']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control <?= isset($errors['tour_description']) ? 'is-invalid' : '' ?>" 
                                              id="description" name="description" rows="5"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                    <?php if (isset($errors['tour_description'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['tour_description']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-control <?= isset($errors['tour_category']) ? 'is-invalid' : '' ?>" 
                                                id="category_id" name="category_id">
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['CategoryID'] ?>" <?= isset($_POST['category_id']) && $_POST['category_id'] == $category['CategoryID'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['Name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['tour_category'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['tour_category']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="destination_id" class="form-label">Destination</label>
                                        <select class="form-control <?= isset($errors['tour_destination']) ? 'is-invalid' : '' ?>" 
                                                id="destination_id" name="destination_id">
                                            <option value="">Select Destination</option>
                                            <?php foreach ($destinations as $destination): ?>
                                                <option value="<?= $destination['DestinationID'] ?>" <?= isset($_POST['destination_id']) && $_POST['destination_id'] == $destination['DestinationID'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($destination['Name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['tour_destination'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['tour_destination']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="duration" class="form-label">Duration</label>
                                    <input type="text" class="form-control" id="duration" name="duration" value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= $_POST['price'] ?? '' ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="inclusions" class="form-label">Inclusions</label>
                                    <textarea class="form-control" id="inclusions" name="inclusions" rows="3"><?= htmlspecialchars($_POST['inclusions'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="exclusions" class="form-label">Exclusions</label>
                                    <textarea class="form-control" id="exclusions" name="exclusions" rows="3"><?= htmlspecialchars($_POST['exclusions'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="difficulty_level" class="form-label">Difficulty Level</label>
                                    <input type="text" class="form-control" id="difficulty_level" name="difficulty_level" value="<?= htmlspecialchars($_POST['difficulty_level'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="group_size" class="form-label">Group Size</label>
                                    <input type="text" class="form-control" id="group_size" name="group_size" value="<?= htmlspecialchars($_POST['group_size'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="availability" class="form-label">Availability</label>
                                    <textarea class="form-control" id="availability" name="availability" rows="3"><?= htmlspecialchars($_POST['availability'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="fileImage" class="form-label">Image</label>
                                    <input type="file" class="form-control <?= isset($errors['tour_image']) ?>" id="input-file" type="file" name="fileImage" accept="image/jpeg,image/png">
                                    <?php if (isset($errors['tour_image'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['tour_image']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary">Create Tour</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tab Tour Bookings -->
                    <div class="tab-pane fade" id="tour-bookings" role="tabpanel">
                        <div class="posts-section">
                            <h4>Tour Bookings</h4>
                            <div class="filter-section">
                                <form method="GET" action="">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="tour_name" class="form-label">Tour Name</label>
                                            <input type="text" class="form-control" id="tour_name" name="tour_name" value="<?= htmlspecialchars($_GET['tour_name'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <select class="form-control" id="category" name="category">
                                                <option value="">All Categories</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= $category['CategoryID'] ?>" <?= isset($_GET['category']) && $_GET['category'] == $category['CategoryID'] ? 'selected' : '' ?>>
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
                                                    <option value="<?= $destination['DestinationID'] ?>" <?= isset($_GET['destination']) && $_GET['destination'] == $destination['DestinationID'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($destination['Name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="request_date" class="form-label">Request Date</label>
                                            <input type="date" class="form-control" id="request_date" name="request_date" value="<?= htmlspecialchars($_GET['request_date'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="">All Statuses</option>
                                                <option value="waiting_response" <?= isset($_GET['status']) && $_GET['status'] == 'waiting_response' ? 'selected' : '' ?>>Waiting Response</option>
                                                <option value="responded" <?= isset($_GET['status']) && $_GET['status'] == 'responded' ? 'selected' : '' ?>>Responded</option>
                                                <option value="planning" <?= isset($_GET['status']) && $_GET['status'] == 'planning' ? 'selected' : '' ?>>Planning</option>
                                                <option value="done" <?= isset($_GET['status']) && $_GET['status'] == 'done' ? 'selected' : '' ?>>Done</option>
                                                <option value="cancelled" <?= isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </form>
                            </div>
                            <?php if (empty($bookings)): ?>
                                <div class="alert alert-info">No booking requests found.</div>
                            <?php else: ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tour</th>
                                            <th>Guest</th>
                                            <th>People</th>
                                            <th>Travel Dates</th>
                                            <th>Price Per Person</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($booking['title']) ?></td>
                                                <td><?= htmlspecialchars($booking['guest_full_name']) ?></td>
                                                <td><?= htmlspecialchars($booking['num_people']) ?></td>
                                                <td><?= htmlspecialchars(date('Y-m-d', strtotime($booking['travel_date'])) . ' to ' . date('Y-m-d', strtotime($booking['end_date']))) ?></td>
                                                <td><?= htmlspecialchars($booking['price'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $booking['status']))) ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?= $booking['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?= $booking['id'] ?>">
                                                            <li>
                                                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#ownerViewModal<?= $booking['id'] ?>">View Details</a>
                                                            </li>
                                                            <li>
                                                                <form action="profile.php" method="POST" class="d-inline-block">
                                                                    <input type="hidden" name="action" value="change_status">
                                                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                                                        <option value="" disabled selected>Change Status</option>
                                                                        <option value="waiting_response">Waiting Response</option>
                                                                        <option value="responded">Responded</option>
                                                                        <option value="planning">Planning</option>
                                                                        <option value="done">Done</option>
                                                                        <option value="cancelled">Cancelled</option>
                                                                    </select>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- View Modal -->
                                            <div class="modal fade" id="ownerViewModal<?= $booking['id'] ?>" tabindex="-1" aria-labelledby="ownerViewModalLabel<?= $booking['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="ownerViewModalLabel<?= $booking['id'] ?>">Request Details: <?= htmlspecialchars($booking['title']) ?></h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>Tour:</strong> <?= htmlspecialchars($booking['title']) ?></p>
                                                            <p><strong>Guest Full Name:</strong> <?= htmlspecialchars($booking['guest_full_name']) ?></p>
                                                            <p><strong>Email:</strong> <?= htmlspecialchars($booking['email']) ?></p>
                                                            <p><strong>Phone:</strong> <?= htmlspecialchars($booking['phone']) ?></p>
                                                            <p><strong>Number of People:</strong> <?= htmlspecialchars($booking['num_people']) ?></p>
                                                            <p><strong>Travel Date:</strong> <?= htmlspecialchars($booking['travel_date']) ?></p>
                                                            <p><strong>End Date:</strong> <?= htmlspecialchars($booking['end_date']) ?></p>
                                                            <p><strong>Price:</strong> <?= htmlspecialchars($booking['price'] ?? 'None') ?></p>
                                                            <p><strong>Special Requests:</strong> <?= htmlspecialchars($booking['special_requests'] ?? 'None') ?></p>
                                                            <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $booking['status']))) ?></p>
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
                                <!-- Pagination -->
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= getQueryString($page - 1) ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="<?= getQueryString($i) ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= getQueryString($page + 1) ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Tab My Requests -->
                    <div class="tab-pane fade" id="my-requests" role="tabpanel">
    <div class="posts-section">
        <h4>My Requests</h4>
        <?php if (empty($my_requests)): ?>
            <div class="alert alert-info">No booking requests found.</div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tour</th>
                        <th>Contact Name</th>
                        <th>People</th>
                        <th>Travel Dates</th>
                        <th>Price Per Person</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['title']) ?></td>
                            <td><?= htmlspecialchars($request['full_name']) ?></td>
                            <td><?= htmlspecialchars($request['num_people']) ?></td>
                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($request['travel_date'])) . ' to ' . date('Y-m-d', strtotime($request['end_date']))) ?></td>
                            <td><?= htmlspecialchars($request['price'] ?? '-') ?></td>
                            <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $request['status']))) ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?= $request['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?= $request['id'] ?>">
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#guestViewModal<?= $request['id'] ?>">View Details</a></li>
                                        <?php if ($request['status'] == 'waiting_response'): ?>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editRequestModal<?= $request['id'] ?>">Edit Request</a></li>
                                            <li><a class="dropdown-item" href="profile.php?action=cancel&booking_id=<?= $request['id'] ?>" onclick="return confirm('Are you sure you want to cancel this request?')">Delete Request</a></li>
                                        <?php endif; ?>
                                        <li>
                                            <a class="dropdown-item <?= $request['status'] == 'planning' ? '' : 'disabled' ?>" 
                                               href="<?= $request['status'] == 'planning' ? 'payment.php?booking_id=' . $request['id'] : '#' ?>" 
                                               <?= $request['status'] == 'planning' ? '' : 'aria-disabled="true"' ?>>
                                               Make Payment
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <!-- View Modal -->
                        <div class="modal fade" id="guestViewModal<?= $request['id'] ?>" tabindex="-1" aria-labelledby="guestViewModalLabel<?= $request['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="guestViewModalLabel<?= $request['id'] ?>">Request Details: <?= htmlspecialchars($request['title']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Tour:</strong> <?= htmlspecialchars($request['title']) ?></p>
                                        <p><strong>Guest Full Name:</strong> <?= htmlspecialchars($request['guest_full_name']) ?></p>
                                        <p><strong>Contact Name:</strong> <?= htmlspecialchars($request['full_name']) ?></p>
                                        <p><strong>Email:</strong> <?= htmlspecialchars($request['email']) ?></p>
                                        <p><strong>Phone:</strong> <?= htmlspecialchars($request['phone']) ?></p>
                                        <p><strong>Number of People:</strong> <?= htmlspecialchars($request['num_people']) ?></p>
                                        <p><strong>Travel Date:</strong> <?= htmlspecialchars($request['travel_date']) ?></p>
                                        <p><strong>End Date:</strong> <?= htmlspecialchars($request['end_date']) ?></p>
                                        <p><strong>Price:</strong> <?= htmlspecialchars($request['price'] ?? 'None') ?></p>
                                        <p><strong>Notes:</strong> <?= htmlspecialchars($request['notes'] ?? 'None') ?></p>
                                        <p><strong>Special Requests:</strong> <?= htmlspecialchars($request['special_requests'] ?? 'None') ?></p>
                                        <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $request['status']))) ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Edit Request Modal -->
                        <?php if ($request['status'] == 'waiting_response'): ?>
                            <div class="modal fade" id="editRequestModal<?= $request['id'] ?>" tabindex="-1" aria-labelledby="editRequestModalLabel<?= $request['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editRequestModalLabel<?= $request['id'] ?>">Edit Request: <?= htmlspecialchars($request['title']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="">
                                                <input type="hidden" name="action" value="edit_request">
                                                <input type="hidden" name="booking_id" value="<?= $request['id'] ?>">
                                                <div class="mb-3">
                                                    <label for="full_name<?= $request['id'] ?>" class="form-label">Contact Name</label>
                                                    <input type="text" class="form-control" id="full_name<?= $request['id'] ?>" name="full_name" value="<?= htmlspecialchars($request['full_name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="guest_full_name<?= $request['id'] ?>" class="form-label">Guest Full Name</label>
                                                    <input type="text" class="form-control" id="guest_full_name<?= $request['id'] ?>" name="guest_full_name" value="<?= htmlspecialchars($request['guest_full_name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email<?= $request['id'] ?>" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email<?= $request['id'] ?>" name="email" value="<?= htmlspecialchars($request['email']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="phone<?= $request['id'] ?>" class="form-label">Phone</label>
                                                    <input type="text" class="form-control" id="phone<?= $request['id'] ?>" name="phone" value="<?= htmlspecialchars($request['phone']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="num_people<?= $request['id'] ?>" class="form-label">Number of People</label>
                                                    <input type="number" class="form-control" id="num_people<?= $request['id'] ?>" name="num_people" value="<?= htmlspecialchars($request['num_people']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="travel_date<?= $request['id'] ?>" class="form-label">Travel Date</label>
                                                    <input type="date" class="form-control" id="travel_date<?= $request['id'] ?>" name="travel_date" value="<?= htmlspecialchars($request['travel_date']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="end_date<?= $request['id'] ?>" class="form-label">End Date</label>
                                                    <input type="date" class="form-control" id="end_date<?= $request['id'] ?>" name="end_date" value="<?= htmlspecialchars($request['end_date']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="notes<?= $request['id'] ?>" class="form-label">Notes</label>
                                                    <textarea class="form-control" id="notes<?= $request['id'] ?>" name="notes" rows="4"><?= htmlspecialchars($request['notes'] ?? '') ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="price<?= $request['id'] ?>" class="form-label">Price</label>
                                                    <input type="text" class="form-control" id="price<?= $request['id'] ?>" name="price" value="<?= htmlspecialchars($request['price'] ?? '') ?>" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="special_requests<?= $request['id'] ?>" class="form-label">Special Requests</label>
                                                    <textarea class="form-control" id="special_requests<?= $request['id'] ?>" name="special_requests" rows="4"><?= htmlspecialchars($request['special_requests'] ?? '') ?></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Update Request</button>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
                    
                    <!-- Tab My Posts -->
                    <div class="tab-pane fade" id="my-posts" role="tabpanel">
    <div class="posts-section">
        <h4>My Posts</h4>
        <?php if (empty($my_posts)): ?>
            <div class="alert alert-info">You haven't created any posts yet.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($my_posts as $post): ?>
                    <div class="col">
                        <div class="card">
                            <img src="<?= htmlspecialchars($post['Image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($post['Title']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($post['Title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars(mb_strimwidth($post['Content'], 0, 100, '...')) ?></p>
                                <p class="card-text"><strong>Category:</strong> <?= htmlspecialchars($post['category_name']) ?></p>
                                <p class="card-text"><strong>Destination:</strong> <?= htmlspecialchars($post['destination_name']) ?></p>
                                <p class="card-text"><small class="text-muted">Created: <?= htmlspecialchars($post['Created_at']) ?></small></p>
                                <a href="post_detail.php?post_id=<?= $post['PostID'] ?>" class="btn btn-primary btn-sm">View</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

                    <!-- Tab Create Post -->
                    <div class="tab-pane fade" id="create-post" role="tabpanel">
    <div class="posts-section">
        <h4>Create New Post</h4>
        <form method="POST" action="user_createpost.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['CategoryID'] ?>"><?= htmlspecialchars($category['Name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="destination_id" class="form-label">Destination</label>
                    <select class="form-control" id="destination_id" name="destination_id" required>
                        <option value="">Select Destination</option>
                        <?php foreach ($destinations as $destination): ?>
                            <option value="<?= $destination['DestinationID'] ?>"><?= htmlspecialchars($destination['Name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="fileImage" class="form-label">Image</label>
                <input type="file" class="form-control" id="fileImage" name="fileImage" accept="image/jpeg,image/png" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Post</button>
        </form>
    </div>
</div>
                </div>
            </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash) {
                const tabTrigger = new bootstrap.Tab(document.querySelector(
                    `[data-bs-target="${window.location.hash}"]`
                ));
                tabTrigger.show();
            }
        });
    </script>
</body>
</html>