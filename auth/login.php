<?php
session_start();

// Khởi tạo biến
$email = $password = '';
$errors = [];

// Xử lý thông báo từ session
$success_message = $_SESSION['success_message'] ?? '';
$error_message_redirect = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Xử lý form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($email)) {
        $errors['email'] = 'Please enter your email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }
    if (empty($password)) {
        $errors['password'] = 'Please enter your password.';
    }

    // Nếu không có lỗi validation
    if (empty($errors)) {
        require_once '../database/connect-db.php';

        // Khởi tạo biến $stmt để có thể kiểm tra sau
        $stmt = null;
        
        try {
            $sql = "SELECT UserID, Email, Password, user_type FROM user WHERE Email = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if (!$user) {
                    $errors['email'] = 'Incorrect email or password.';
                } elseif ($user['Password'] != $password) {
                    $errors['password'] = 'Incorrect email or password.';
                } else {
                    // Đăng nhập thành công
                    // Start session if not already started
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    // Set session variables (đồng bộ key với các file khác)
                    $_SESSION['user_id'] = $user['UserID'];
                    $_SESSION['email'] = $user['Email']; 
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['LoggedIn'] = true;
                    
                    // Optional: Set session cookie parameters for longer persistence
                    $lifetime = 30 * 24 * 60 * 60; // 30 days
                    session_set_cookie_params($lifetime);
                    setcookie(session_name(), session_id(), time() + $lifetime);
                    
                    // Redirect based on user type
                    header('Location: ' . ($user['user_type'] == 'admin' ? 
                        '../admin/index_homeAdmin.php' : 
                        '../customer/Home_user/index_homepage.php'));
                    exit();
                }
            } else {
                $errors['database'] = "Database error. Please try again.";
            }
        } catch (Exception $e) {
            $errors['database'] = "An error occurred. Please try again.";
error_log("Login error: " . $e->getMessage());
        } finally {
            // Luôn đóng kết nối và statement trong khối finally
            if ($stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if ($conn instanceof mysqli) {
                $conn->close();
            }
        }
    }
}

// After successful login, add this before the redirect:
if (isset($_SESSION['requested_page'])) {
    $redirect_to = $_SESSION['requested_page'];
    unset($_SESSION['requested_page']);
    header("Location: " . $redirect_to);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Let's Travel</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { 
            margin: 0; 
            font-family: Arial, sans-serif; 
            background-color: #030303; 
            color: #F1EFEC;
        }
        .hero { 
            position: relative; 
            height: calc(100vh - 70px); /* Chiều cao màn hình trừ navbar */ 
            overflow: hidden; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
        }
        .hero iframe { 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            width: 120%; 
            height: 120%; 
            transform: translate(-50%, -50%); 
            pointer-events: none; 
            z-index: -1; 
        }
        .login-container { 
            position: relative; 
            z-index: 2; 
            background: rgba(18, 52, 88, 0.85); 
            padding: 35px 40px; 
            border-radius: 12px; 
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3); 
            width: 100%; 
            max-width: 420px; 
            color: #F1EFEC; 
        }
        .login-container h1 { text-align: center; margin-bottom: 25px; font-weight: bold; }
        .form-control { border-radius: 5px; background-color: rgba(3, 3, 3, 0.7); color: #F1EFEC; border: 1px solid #D4C9BE; padding: 10px 15px; }
        .form-control:focus { background-color: rgba(3, 3, 3, 0.8); color: #F1EFEC; border-color: #ffffff; box-shadow: 0 0 0 0.2rem rgba(212, 201, 190, 0.25); }
        .btn-primary { width: 100%; padding: 12px; font-size: 1.1rem; border-radius: 5px; background-color: #D4C9BE; color: #030303; border: none; font-weight: bold; transition: background-color 0.3s ease, color 0.3s ease; }
        .btn-primary:hover { background-color: #bdae9f; color: #030303; }
        .text-danger { font-size: 0.9rem; color: #ffdddd; margin-top: 5px; display: block; }
        .register-link a { color: #D4C9BE; font-weight:bold; text-decoration: none; }
        .register-link a:hover { text-decoration: underline;}
        @media (max-width: 768px) {
            .login-container { padding: 20px 10px; }
        }
    </style>
</head>
<body>
<?php include("../inc/_navbar.php"); ?>

    <!-- Hero Section chứa Video và Form Login -->
    <div class="hero">
        <!-- Video Background -->
        <iframe
            src="https://www.youtube.com/embed/35npVaFGHMY?autoplay=1&mute=1&loop=1&playlist=35npVaFGHMY&controls=0&showinfo=0&modestbranding=1"
            frameborder="1"
            allow="autoplay;"
            allowfullscreen>
        </iframe>

        <!-- Form Login -->
        <div id="login-section" class="login-container">
            <h1>Login</h1>

            <?php // Hiển thị thông báo thành công/lỗi từ redirect (nếu có)
            if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
             <?php if (!empty($error_message_redirect)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message_redirect); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
             <?php // Hiển thị lỗi database chung
             if (isset($errors['database'])): ?>
                 <div class="alert alert-danger"><?php echo htmlspecialchars($errors['database']); ?></div>
             <?php endif; ?>


            <form action="login.php" method="POST" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-danger"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" name="password" id="password" required>
                     <?php if (isset($errors['password'])): ?>
                        <div class="text-danger"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Login</button>
            </form>
             <!-- Link đăng ký -->
             <p class="text-center mt-3 mb-0 register-link">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>