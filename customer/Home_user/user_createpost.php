<?php
session_start();
require_once '../../database/connect-db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$errors = [];
$success_message = '';
$title = '';
$content = '';
$category_id = '';
$destination_id = '';
$image_path = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $destination_id = trim($_POST['destination_id'] ?? '');
    $fileImage = $_FILES['fileImage'] ?? null;

    if (empty($title)) $errors['title'] = "Title is required";
    if (empty($content)) $errors['content'] = "Content is required";
    if (empty($category_id)) $errors['category'] = "Category is required";
    if (empty($destination_id)) $errors['destination'] = "Destination is required";
    if (!$fileImage || $fileImage['error'] != UPLOAD_ERR_OK) {
        $errors['image'] = "Image is required";
    } else {
        $fileType = strtolower(pathinfo($fileImage['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            $errors['image'] = "Invalid file type, only JPG, JPEG, PNG allowed.";
        }
        if ($fileImage["size"] > 20 * 1024 * 1024) {
            $errors['image'] = "File is too large, expect smaller than 20MB";
        }
    }

    if (empty($errors)) {
        $upload_dir = "../../uploads/";
        $image_name = uniqid() . '_' . basename($fileImage["name"]);
        $image_path = $upload_dir . $image_name;
        if (move_uploaded_file($fileImage["tmp_name"], $image_path)) {
            $sql = "INSERT INTO posts (UserID, Title, Content, Image, CategoryID, DestinationID, Created_at, Updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssii", $_SESSION['user_id'], $title, $content, $image_path, $category_id, $destination_id);
            if ($stmt->execute()) {
                $success_message = "Post created successfully!";
                $title = $content = $category_id = $destination_id = '';
            } else {
                $errors['database'] = "Error creating post: " . $conn->error;
            }
            $stmt->close();
        } else {
            $errors['image'] = "Error uploading image";
        }
    }
}

// Lấy danh sách category và destination
$categories = [];
$destinations = [];
$cat_rs = $conn->query("SELECT CategoryID, Name FROM category ORDER BY Name");
if ($cat_rs) while ($row = $cat_rs->fetch_assoc()) $categories[] = $row;
$dest_rs = $conn->query("SELECT DestinationID, Name FROM destination ORDER BY Name");
if ($dest_rs) while ($row = $dest_rs->fetch_assoc()) $destinations[] = $row;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h3>Create New Post</h3>
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
        </div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($title) ?>">
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" id="content" name="content" rows="5"><?= htmlspecialchars($content) ?></textarea>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-control" id="category_id" name="category_id">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['CategoryID'] ?>" <?= $category_id == $cat['CategoryID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="destination_id" class="form-label">Destination</label>
                <select class="form-control" id="destination_id" name="destination_id">
                    <option value="">Select Destination</option>
                    <?php foreach ($destinations as $dest): ?>
                        <option value="<?= $dest['DestinationID'] ?>" <?= $destination_id == $dest['DestinationID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dest['Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label for="fileImage" class="form-label">Image</label>
            <input type="file" class="form-control" id="fileImage" name="fileImage" accept="image/jpeg,image/png">
        </div>
        <button type="submit" class="btn btn-primary">Create Post</button>
        <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
    </form>
</div>
</body>
</html>
