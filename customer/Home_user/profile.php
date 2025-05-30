<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../database/connect-db.php';
// filepath: c:\xampp\htdocs\PHP\TravelBlog\customer\Home_user\profile.php

// Khởi tạo biến
$errors = [];
$success_message = '';
$user_data = [];
$posts = [];

// Lấy thông tin user
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM user WHERE UserID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$stmt_user->close();

// Lấy danh sách bài viết của user
$sql_posts = "SELECT * FROM posts WHERE UserID = ? ORDER BY Created_at DESC";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("i", $user_id);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();
$posts = $result_posts->fetch_all(MYSQLI_ASSOC);
$stmt_posts->close();

// Xử lý cập nhật thông tin cá nhân
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);

    // Validate
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
            // Cập nhật lại session
            $_SESSION['email'] = $email;
            // Lấy lại thông tin user
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

// Xử lý tạo bài viết mới
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_post'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $fileImage = isset($_FILES['fileImage']) ? $_FILES['fileImage'] : null;

    // Clean data
    $title = htmlspecialchars($title);
    $content = htmlspecialchars($content);

    // Validate
    if (empty($title)) $errors['post_title'] = "Title is required";
    if (empty($content)) $errors['post_content'] = "Content is required";

    // Validate image
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
        // Upload image
        $upload_dir = "../uploads/";
        $image_name = uniqid() . '_' . basename($fileImage["name"]);
        $image_path = $upload_dir . $image_name;
        
        if (move_uploaded_file($fileImage["tmp_name"], $image_path)) {
            // Insert post
            $sql_insert = "INSERT INTO posts (UserID, Title, Content, image, Created_at, Updated_at) 
                          VALUES (?, ?, ?, ?, NOW(), NOW())";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("isss", $user_id, $title, $content, $image_path);
            
            if ($stmt_insert->execute()) {
                $success_message = "Post created successfully!";
                // Làm mới danh sách bài viết
                $stmt_posts = $conn->prepare($sql_posts);
                $stmt_posts->bind_param("i", $user_id);
                $stmt_posts->execute();
                $result_posts = $stmt_posts->get_result();
                $posts = $result_posts->fetch_all(MYSQLI_ASSOC);
                $stmt_posts->close();
            } else {
                $errors['database'] = "Error creating post: " . $conn->error;
            }
            $stmt_insert->close();
        } else {
            $errors['post_image'] = "Error uploading image";
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
    <title>User Profile - TravelBlog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
body {
    background-color: #F1FEFC; /* Light background color */
    color: #030303; /* Dark text color */
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    padding-top: 80px; /* Đảm bảo nội dung không bị navbar che khuất */
}

/* Navbar */
.navbar {
    background-color: #123458; /* Dark blue background */
    border-bottom: 3px solid #D4C9BE; /* Beige border */
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Thêm hiệu ứng bóng đổ */
}

.navbar a {
    color: #F1FEFC !important; /* Light text color */
    font-weight: 600; /* Chữ đậm */
}

/* Liên kết trong navbar */
.navbar-nav .nav-item .nav-link {
    color: #F1FEFC !important; /* Light text color */
    padding: 10px 15px;
    text-transform: uppercase;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Màu nền khi hover */
.navbar-nav .nav-item .nav-link:hover {
    background-color: #D4C9BE; /* Beige hover background */
    color: #030303; /* Dark text on hover */
    border-radius: 5px;
}

/* Cập nhật dropdown menu */
.navbar-nav .nav-item.dropdown .dropdown-menu {
    background-color: #123458; /* Dark blue background */
    border: none; /* Loại bỏ viền mặc định của dropdown */
}

.navbar-nav .nav-item.dropdown .dropdown-item {
    color: #F1FEFC; /* Light text color */
    padding: 10px 20px;
    font-size: 1rem;
}

/* Màu nền khi hover trên các mục dropdown */
.navbar-nav .nav-item.dropdown .dropdown-item:hover {
    background-color: #D4C9BE; /* Beige hover background */
    color: #030303; /* Dark text on hover */
}

/* Hero Section */
.hero-section {
    background-color: #123458; /* Dark blue background */
    color: #F1FEFC; /* Light text color */
    background-image: url('https://tiesinstitute.com/wp-content/uploads/2021/01/shutterstock_268004744-2.jpg');
    background-size: cover;
    background-position: center;
    height: 400px;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    margin-top: 80px; /* Khoảng cách giữa navbar và hero section */
    text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.7);
}

.hero-section h1 {
    font-size: 2rem;
    font-weight: bold;
}

/* Recent Posts Section */
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
    color: #123458; /* Dark blue for card titles */
}

.card-body {
    background-color: #D4C9BE; /* Beige background for cards */
    color: #030303; /* Dark text color */
    border-radius: 5px;
}

/* Thêm khoảng cách giữa các bài viết */
.row-cols-md-3 .col {
    margin-bottom: 30px;
}

/* Thiết lập responsive cho các bài viết */
@media (max-width: 768px) {
    .card-img-top {
        height: 150px; /* Giảm chiều cao hình ảnh cho màn hình nhỏ */
    }
}


/* General footer styles */
footer {
    background-color: #123458; /* Dark blue background */
    color: #F1FEFC; /* Light text color */
    padding-top: 40px;
    padding-bottom: 40px;
    margin-top: 50px;
}

footer h5 {
    font-weight: bold;
    font-size: 1.25rem;
}

footer .footer-link {
    color: #F1FEFC; /* Light text color */
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.3s ease;
}

footer .footer-link:hover {
    color: #D4C9BE; /* Beige hover color */
    text-decoration: underline;
}

footer .social-icons {
    margin-top: 20px;
}

footer .social-icon {
    color: #F1FEFC; /* Light icon color */
    font-size: 2rem; /* Kích thước lớn hơn cho biểu tượng */
    margin: 0 15px;
    transition: all 0.3s ease;
}

footer .social-icon:hover {
    color: #D4C9BE; /* Beige hover color */
    transform: scale(1.2);
}

footer .text-white-50 {
    color: rgba(255, 255, 255, 0.7) !important; /* Màu chữ nhạt cho phần bản quyền */
}

/* Responsive Design */
@media (max-width: 768px) {
    footer h5 {
        font-size: 1.1rem;
    }

    footer .social-icon {
        font-size: 1.5rem;
        margin: 0 10px;
    }
}


    /* .hero-section h1 {
        font-size: 2rem; 
    } */
/* } */

/* Xóa màu nền trắng hoặc overlay cho phần danh mục */
.categories-details {
    background-color: transparent; /* Đảm bảo không có overlay trắng */
    padding: 20px;
    border-radius: 10px;
    box-shadow: none;
}


    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #123458; position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
    <div class="container">
        <a class="navbar-brand" href="category/indexcategory.php">
            <img src="../uploads/logo.jpg" alt="Travel Blog Logo" class="img-fluid" style="max-width: 50px; margin-right: 10px;">
            Let's Travel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index_homepage.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Categories
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Cities">Cities</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Beaches">Beaches</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Cultural%20Sites">Cultural Sites</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Adventure%20Spots">Adventure Spots</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Natural%20Wonders">Natural Wonders</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Luxury%20Destinations">Luxury Destinations</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Family-friendly%20Locations">Family-friendly Locations</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Romantic%20Getaways">Romantic Getaways</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Wildlife">Wildlife</a></li>
                        <li><a class="dropdown-item" href="/TravelBlog/category/indexcategory.php?category=Culinary%20Destinations">Culinary Destinations</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarPosts" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Posts
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarPosts">
                        <li><a class="dropdown-item" href="post_Paris.php">Exploring Paris</a></li>
                        <li><a class="dropdown-item" href="post_NewYork.php">New York Adventures</a></li>
                        <li><a class="dropdown-item" href="post_Tokyo.php">Tokyo: The traditional and modern city</a></li>
                        <li><a class="dropdown-item" href="#">Rome: The Eternal City</a></li>
                        <li><a class="dropdown-item" href="#">London: A City of History</a></li>
                        <li><a class="dropdown-item" href="#">Sydney: Sun and Surf</a></li>
                        <li><a class="dropdown-item" href="#">Singapore: A City of Luxury</a></li>
                        <li><a class="dropdown-item" href="#">Bali</a></li>
                        <li><a class="dropdown-item" href="#">Sapa - A perfect sightseeing and cultural trip</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>   
    <div class="container py-5">
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['database'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errors['database']) ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="profile-section text-center">
                    <img src="<?= htmlspecialchars($user_data['Avatar'] ?? '../uploads/default-avatar.jpg') ?>" 
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
                                data-bs-target="#edit-profile" type="button" role="tab">
                            Edit Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="posts-tab" data-bs-toggle="tab" 
                                data-bs-target="#my-posts" type="button" role="tab">
                            My Posts
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="create-tab" data-bs-toggle="tab" 
                                data-bs-target="#create-post" type="button" role="tab">
                            Create Post
                        </button>
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
                    
                    <!-- Tab My Posts -->
                    <div class="tab-pane fade" id="my-posts" role="tabpanel">
                        <div class="posts-section">
                            <h4>My Posts</h4>
                            <?php if (empty($posts)): ?>
                                <div class="alert alert-info">You haven't created any posts yet.</div>
                            <?php else: ?>
                                <?php foreach ($posts as $post): ?>
                                    <div class="post-card">
                                        <h5><?= htmlspecialchars($post['Title']) ?></h5>
                                        <p class="text-muted small">
                                            Posted on <?= date('M d, Y', strtotime($post['Created_at'])) ?>
                                        </p>
                                        <?php if ($post['image']): ?>
                                            <img src="<?= htmlspecialchars($post['image']) ?>" 
                                                 alt="Post image" class="img-fluid post-image mb-3">
                                        <?php endif; ?>
                                        <p><?= nl2br(htmlspecialchars($post['Content'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Tab Create Post -->
                    <div class="tab-pane fade" id="create-post" role="tabpanel">
                        <div class="posts-section">
                            <h4>Create New Post</h4>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="create_post" value="1">
                                
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control <?= isset($errors['post_title']) ? 'is-invalid' : '' ?>" 
                                           id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                                    <?php if (isset($errors['post_title'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['post_title']) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="content" class="form-label">Content</label>
                                    <textarea class="form-control <?= isset($errors['post_content']) ? 'is-invalid' : '' ?>" 
                                              id="content" name="content" rows="5"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                                    <?php if (isset($errors['post_content'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['post_content']) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="fileImage" class="form-label">Image</label>
                                    <input type="file" class="form-control <?= isset($errors['post_image']) ? 'is-invalid' : '' ?>" 
                                           id="fileImage" name="fileImage" accept="image/jpeg, image/png">
                                    <?php if (isset($errors['post_image'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['post_image']) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Create Post</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Kích hoạt tab từ URL hash
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