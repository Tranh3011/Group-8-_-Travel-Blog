<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}
$id = $_SESSION['user_id'];

// connect db
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname) 
    or die ('Failed to connect to db.');

// Get the user data
$sql = "SELECT * FROM user WHERE UserID = '$id'";
$_result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($_result);

// redirect if user not exist
if (!$user) {
    header('Location: ../auth/logout.php');
    exit();
}

// Lấy danh sách bài viết của user
$sql_posts = "SELECT * FROM posts WHERE UserID = '$id' ORDER BY Created_at DESC";
$result_posts = mysqli_query($conn, $sql_posts);
$user_posts = [];
if ($result_posts) {
    $user_posts = mysqli_fetch_all($result_posts, MYSQLI_ASSOC);
}

// Lấy danh sách booking của user
$sql_bookings = "SELECT * FROM booking WHERE UserID = '$id' ORDER BY BookingDate DESC";
$result_bookings = @mysqli_query($conn, $sql_bookings);
$user_bookings = [];
if ($result_bookings) {
    $user_bookings = mysqli_fetch_all($result_bookings, MYSQLI_ASSOC);
}

// Process POST data for updating user info
$errors = [];
$firstName = $user['FirstName'];
$lastName = $user['LastName'];
$email = $user['Email'];
$phoneNumber = $user['PhoneNumber'];
$city = $user['City'];
$country = $user['Country'];
$avatar = $user['Avatar'];
$fileAvatar = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // -- get user data
    if (isset($_POST['FirstName'])) {
        $firstName = $_POST['FirstName'];
    }
    if (isset($_POST['LastName'])) {
        $lastName = $_POST['LastName'];
    }
    if (isset($_POST['Email'])) {
        $email = $_POST['Email'];
    }
    if (isset($_POST['PhoneNumber'])) {
        $phoneNumber = $_POST['PhoneNumber'];
    }
    if (isset($_POST['City'])) {
        $city = $_POST['City'];
    }
    if (isset($_POST['Country'])) {
        $country = $_POST['Country'];
    }
    if (isset($_FILES['fileAvatar'])) {
        $fileAvatar = $_FILES['fileAvatar'];
    }

    // -- clean user data
    $firstName = trim($firstName);
    $firstName = htmlspecialchars($firstName);
    $firstName = addslashes($firstName);

    // -- validate user data
    if (empty($firstName)) {
        $errors['FirstName'] = 'First Name is required';
    }

    if (empty($email)) {
        $errors['Email'] = 'Email is required';
    }

    // File validation
    if (isset($_FILES['fileAvatar']) && $_FILES['fileAvatar']['error'] == 0) {
        $fileAvatar = $_FILES['fileAvatar'];
        $fileType = strtolower(pathinfo($fileAvatar['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            $errors['fileAvatar'] = "Invalid file type, only JPG, JPEG, PNG are allowed.";
        }
    }

    if (empty($errors)) {
        // Handle file upload
        if (!empty($fileAvatar) && isset($fileAvatar['tmp_name']) && $fileAvatar['tmp_name']) {
            $targetDir = "../uploads/";
            $targetFile = $targetDir . basename($fileAvatar["name"]);
            move_uploaded_file($fileAvatar["tmp_name"], $targetFile);
            $avatar = $targetFile;
        }

        // Update user data in the database
        $sql = "UPDATE `user`
                SET `FirstName` = '$firstName',
                    `LastName` = '$lastName',
                    `Email` = '$email',
                    `PhoneNumber` = '$phoneNumber',
                    `City` = '$city',
                    `Country` = '$country',
                    `Avatar` = '$avatar'
                WHERE `UserID` = $id";
        $result = @mysqli_query($conn, $sql);

        if ($result) {
            echo "<script>
                    alert('User updated successfully!');
                    window.location.href = 'profile.php';
                  </script>";
            exit();
        }
    }
}

// Handle create post
$post_create_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $title = trim($_POST['post_title'] ?? '');
    $content = trim($_POST['post_content'] ?? '');
    $destination_id = intval($_POST['destination_id'] ?? 0);
    if ($title && $content && $destination_id) {
        $title = addslashes(htmlspecialchars($title));
        $content = addslashes(htmlspecialchars($content));
        $created_at = date('Y-m-d H:i:s');
        $sql_insert_post = "INSERT INTO posts (UserID, Title, Content, DestinationID, Created_at) VALUES ($id, '$title', '$content', $destination_id, '$created_at')";
        if (mysqli_query($conn, $sql_insert_post)) {
            $post_create_msg = '<div class="alert alert-success">Post created successfully!</div>';
        } else {
            $post_create_msg = '<div class="alert alert-danger">Failed to create post.</div>';
        }
    } else {
        $post_create_msg = '<div class="alert alert-danger">Please fill all fields.</div>';
    }
}

// Fetch destinations for post creation
$destinations = [];
$res_dest = mysqli_query($conn, "SELECT DestinationID, Name FROM destination ORDER BY Name ASC");
if ($res_dest) {
    while ($row = mysqli_fetch_assoc($res_dest)) {
        $destinations[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 80px;
            background-color: #F1FEFC;
        }
        .main-content-center {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 32px 24px 24px 24px;
        }
        .nav-tabs .nav-link {
            color: #123458;
            font-weight: 600;
            text-transform: uppercase;
        }
        .nav-tabs .nav-link.active {
            background-color: #123458;
            color: #fff;
            border-color: #123458 #123458 #fff;
        }
        .tab-pane {
            padding-top: 24px;
        }
        /* Navbar styles (match homepage) */
        .navbar {
            background-color: #123458 !important;
            border-bottom: 3px solid #D4C9BE !important;
            position: fixed !important;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        }
        .navbar-logo img {
            height: 50px !important;
            max-width: 50px !important;
            margin-right: 10px;
        }
        .navbar-logo h1 {
            font-size: 22px !important;
            font-weight: bold;
            color: #F1FEFC;
            margin: 0;
            letter-spacing: 1px;
        }
        .navbar-links a,
        .navbar-links .dropdown-toggle {
            color: #F1FEFC !important;
            font-weight: 600;
            text-transform: uppercase;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s, color 0.3s;
            text-decoration: none !important;
        }
        .navbar-links a:hover,
        .navbar-links .dropdown-toggle:hover {
            background-color: #D4C9BE !important;
            color: #030303 !important;
            text-decoration: none !important;
        }
        .dropdown-menu {
            background-color: #123458 !important;
            border: none;
            min-width: 200px;
            border-radius: 5px;
        }
        .dropdown-menu .dropdown-item {
            color: #F1FEFC !important;
            padding: 10px 20px;
            font-size: 1rem;
            text-transform: uppercase;
            text-decoration: none !important;
        }
        .dropdown-menu .dropdown-item:hover {
            background-color: #D4C9BE !important;
            color: #030303 !important;
            text-decoration: none !important;
        }
        @media (max-width: 768px) {
            .main-content-center {
                padding: 16px 4px 16px 4px;
            }
            .navbar-logo img {
                height: 40px !important;
                max-width: 40px !important;
            }
            .navbar-logo h1 {
                font-size: 18px !important;
            }
            .navbar-links a,
            .navbar-links .dropdown-toggle {
                font-size: 15px;
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <?php include("../../inc/_navbar.php"); ?>

    <div class="main-content-center">
        <ul class="nav nav-tabs" id="profileTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-pane" type="button" role="tab">Profile</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="post-tab" data-bs-toggle="tab" data-bs-target="#post-pane" type="button" role="tab">Post</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="booking-tab" data-bs-toggle="tab" data-bs-target="#booking-pane" type="button" role="tab">Booking</button>
            </li>
        </ul>
        <div class="tab-content" id="profileTabContent">
            <!-- Profile Tab -->
            <div class="tab-pane fade show active" id="profile-pane" role="tabpanel">
                <h1 class="mt-4 mb-4 text-center">User Profile</h1>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="" method="POST" enctype="multipart/form-data" class="mb-5">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <?php if ($avatar && file_exists($avatar)): ?>
                                <img src="<?php echo $avatar; ?>" class="img-thumbnail mb-3" style="max-width:150px;">
                            <?php else: ?>
                                <img src="../uploads/default_avatar.png" class="img-thumbnail mb-3" style="max-width:150px;">
                            <?php endif; ?>
                            <input type="file" id="fileAvatar" name="fileAvatar" class="form-control">
                        </div>
                        <div class="col-md-9">
                            <div class="form-group mb-2">
                                <label for="FirstName" class="form-label">First Name</label>
                                <input type="text" id="FirstName" name="FirstName" class="form-control" value="<?php echo $firstName; ?>">
                            </div>
                            <div class="form-group mb-2">
                                <label for="LastName" class="form-label">Last Name</label>
                                <input type="text" id="LastName" name="LastName" class="form-control" value="<?php echo $lastName; ?>">
                            </div>
                            <div class="form-group mb-2">
                                <label for="Email" class="form-label">Email</label>
                                <input type="email" id="Email" name="Email" class="form-control" value="<?php echo $email; ?>">
                            </div>
                            <div class="form-group mb-2">
                                <label for="PhoneNumber" class="form-label">Phone Number</label>
                                <input type="text" id="PhoneNumber" name="PhoneNumber" class="form-control" value="<?php echo $phoneNumber; ?>">
                            </div>
                            <div class="form-group mb-2">
                                <label for="City" class="form-label">City</label>
                                <input type="text" id="City" name="City" class="form-control" value="<?php echo $city; ?>">
                            </div>
                            <div class="form-group mb-2">
                                <label for="Country" class="form-label">Country</label>
                                <input type="text" id="Country" name="Country" class="form-control" value="<?php echo $country; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary mt-2">Update</button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Post Tab -->
            <div class="tab-pane fade" id="post-pane" role="tabpanel">
                <h2 class="text-center">Create Post</h2>
                <?php if ($post_create_msg) echo $post_create_msg; ?>
                <form action="" method="POST" class="mb-4">
                    <input type="hidden" name="create_post" value="1">
                    <div class="mb-3">
                        <label for="post_title" class="form-label">Title</label>
                        <input type="text" name="post_title" id="post_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="post_content" class="form-label">Content</label>
                        <textarea name="post_content" id="post_content" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="destination_id" class="form-label">Destination</label>
                        <select name="destination_id" id="destination_id" class="form-control" required>
                            <option value="">Select destination</option>
                            <?php foreach ($destinations as $dest): ?>
                                <option value="<?php echo $dest['DestinationID']; ?>"><?php echo htmlspecialchars($dest['Name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Create Post</button>
                </form>
                <hr>
                <h2 class="text-center">Your Posts</h2>
                <?php if (!empty($user_posts)): ?>
                    <?php foreach ($user_posts as $post): ?>
                        <div class="card mb-3">
                            <div class="row g-0">
                                <?php
                                // Lấy ảnh từ bảng destination nếu có DestinationID
                                $post_image = '';
                                if (!empty($post['DestinationID'])) {
                                    $dest_id = intval($post['DestinationID']);
                                    $sql_img = "SELECT Image FROM destination WHERE DestinationID = $dest_id LIMIT 1";
                                    $result_img = mysqli_query($conn, $sql_img);
                                    if ($result_img && $img_row = mysqli_fetch_assoc($result_img)) {
                                        $img_path = '../uploads/' . $img_row['Image'];
                                        if (file_exists($img_path)) {
                                            $post_image = $img_path;
                                        }
                                    }
                                }
                                if (!$post_image) {
                                    $post_image = '../uploads/default_image.jpg';
                                }
                                ?>
                                <div class="col-md-4 d-flex align-items-center justify-content-center">
                                    <img src="<?php echo htmlspecialchars($post_image); ?>" class="img-fluid rounded-start" alt="Post Image" style="max-height:180px;object-fit:cover;width:100%;">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($post['Title']); ?></h5>
                                        <div class="card-text"><?php echo nl2br(htmlspecialchars($post['Content'])); ?></div>
                                        <small class="text-muted">Created at: <?php echo htmlspecialchars($post['Created_at']); ?></small>
                                    </div>
                                    <div class="card-footer">
                                        <!-- Display comments for this post -->
                                        <strong>Comments:</strong>
                                        <ul class="list-group mb-2" id="comments-list-<?php echo $post['PostID']; ?>">
                                            <?php
                                            $post_id = $post['PostID'];
                                            $sql_comments = "SELECT c.*, u.FirstName, u.LastName FROM comments c JOIN user u ON c.UserID = u.UserID WHERE c.PostID = $post_id ORDER BY c.Created_at DESC";
                                            $result_comments = mysqli_query($conn, $sql_comments);
                                            if ($result_comments && mysqli_num_rows($result_comments) > 0):
                                                while ($comment = mysqli_fetch_assoc($result_comments)):
                                            ?>
                                                <li class="list-group-item">
                                                    <strong><?php echo htmlspecialchars($comment['FirstName'] . ' ' . $comment['LastName']); ?>:</strong>
                                                    <?php echo htmlspecialchars($comment['Content']); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($comment['Created_at']); ?></small>
                                                </li>
                                            <?php endwhile; else: ?>
                                                <li class="list-group-item">No comments yet.</li>
                                            <?php endif; ?>
                                        </ul>
                                        <!-- Add comment form -->
                                        <form action="" method="POST" class="d-flex" onsubmit="return addComment(event, <?php echo $post_id; ?>);">
                                            <input type="hidden" name="add_comment" value="1">
                                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                            <input type="text" name="comment_content" id="comment_content_<?php echo $post_id; ?>" class="form-control me-2" placeholder="Add a comment..." required>
                                            <button type="submit" class="btn btn-secondary">Comment</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No posts found.</p>
                <?php endif; ?>
                <script>
                function addComment(e, postId) {
                    e.preventDefault();
                    var input = document.getElementById('comment_content_' + postId);
                    var content = input.value.trim();
                    if (!content) return false;
                    var formData = new FormData();
                    formData.append('add_comment', 1);
                    formData.append('post_id', postId);
                    formData.append('comment_content', content);
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '', true);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            var commentsList = document.getElementById('comments-list-' + postId);
                            try {
                                var res = JSON.parse(xhr.responseText);
                                if (res.success) {
                                    var li = document.createElement('li');
                                    li.className = 'list-group-item';
                                    li.innerHTML = '<strong>' + res.name + ':</strong> ' + res.content + '<br><small class="text-muted">' + res.created_at + '</small>';
                                    var first = commentsList.firstElementChild;
                                    if (first && first.textContent.trim() === 'No comments yet.') {
                                        commentsList.innerHTML = '';
                                    }
                                    commentsList.insertBefore(li, commentsList.firstChild);
                                    input.value = '';
                                }
                            } catch (e) {
                                window.location.reload();
                            }
                        }
                    };
                    xhr.send(formData);
                    return false;
                }
                </script>
                <?php
                // AJAX handler for comment submission
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment']) && isset($_POST['post_id']) && isset($_POST['comment_content']) && !empty($_POST['comment_content'])) {
                    $post_id = intval($_POST['post_id']);
                    $comment_content = trim($_POST['comment_content']);
                    $comment_content = addslashes(htmlspecialchars($comment_content));
                    $user_id = $id;
                    $created_at = date('Y-m-d H:i:s');
                    $sql_comment = "INSERT INTO comments (PostID, UserID, Content, Created_at) VALUES ($post_id, $user_id, '$comment_content', '$created_at')";
                    if (mysqli_query($conn, $sql_comment)) {
                        $name = htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']);
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'name' => $name,
                            'content' => htmlspecialchars($_POST['comment_content']),
                            'created_at' => $created_at
                        ]);
                        exit;
                    }
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false]);
                    exit;
                }
                ?>
            </div>
            <!-- Booking Tab -->
            <div class="tab-pane fade" id="booking-pane" role="tabpanel">
                <h2 class="text-center">User's Bookings</h2>
                <?php if (!empty($user_bookings)): ?>
                    <ul class="list-group mb-4">
                        <?php foreach ($user_bookings as $booking): ?>
                            <li class="list-group-item">
                                <?php foreach ($booking as $key => $value): ?>
                                    <strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?> &nbsp;
                                <?php endforeach; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center">No bookings found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
