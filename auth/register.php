<?php
// Bắt đầu session để dùng flash messages
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include file kết nối CSDL
require_once '../database/connect-db.php'; // <-- KIỂM TRA LẠI ĐƯỜNG DẪN NÀY!

// Khởi tạo biến
$email = '';
$errors = []; // Mảng chứa lỗi

// Xử lý khi form được submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Lấy dữ liệu (đảm bảo key 'email', 'password', 'password_confirm' khớp với thuộc tính 'name' trong form)
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    // --- Validate dữ liệu ---
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) { // Kiểm tra độ dài tối thiểu (ví dụ 6)
        $errors['password'] = "Password must be at least 6 characters long.";
    }

    if (empty($password_confirm)) {
        $errors['password_confirm'] = "Please confirm your password.";
    } elseif ($password !== $password_confirm) {
        $errors['password_confirm'] = "Passwords do not match.";
    }

    // --- Nếu không có lỗi validation cơ bản -> Kiểm tra email tồn tại ---
    if (empty($errors)) {
        // Sử dụng prepared statement để kiểm tra email
        $sql_check_email = "SELECT UserID FROM user WHERE Email = ?"; // Sử dụng đúng tên cột 'Email' và 'UserID'
        $stmt_check = mysqli_prepare($conn, $sql_check_email);

        if ($stmt_check === false) {
            // Lỗi prepare -> Ghi log và báo lỗi chung
            error_log("MySQLi prepare failed for email check: " . mysqli_error($conn));
            $errors['database'] = "A database error occurred. Please try again later (check prep).";
        } else {
            mysqli_stmt_bind_param($stmt_check, "s", $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $errors['email'] = "This email address is already registered.";
            }
            mysqli_stmt_close($stmt_check); // Đóng statement kiểm tra
        }
    }

    // --- Nếu vẫn không có lỗi nào -> Tiến hành đăng ký ---
    if (empty($errors)) {
        // 1. Hash mật khẩu (QUAN TRỌNG!)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 2. Chuẩn bị câu lệnh INSERT (Chỉ Email và Password)
        // Sử dụng đúng tên cột 'Email' và 'Password' từ ảnh CSDL
        $sql_insert = "INSERT INTO user (Email, Password) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($conn, $sql_insert);

        if ($stmt_insert === false) {
            // Lỗi prepare -> Ghi log và báo lỗi chung
             error_log("MySQLi prepare failed for user insert: " . mysqli_error($conn));
             $errors['database'] = "A database error occurred. Please try again later (insert prep).";
        } else {
            // Bind parameters: 's' là string
            mysqli_stmt_bind_param($stmt_insert, "ss", $email, $hashed_password);

            // 3. Thực thi
            if (mysqli_stmt_execute($stmt_insert)) {
                // Đăng ký thành công!
                $_SESSION['success_message'] = "Registration successful! Please log in.";

                // Đóng statement và connection
                mysqli_stmt_close($stmt_insert);
                mysqli_close($conn);

                // Chuyển hướng đến trang đăng nhập
                header("Location: login.php");
                exit();
            } else {
                // Lỗi execute -> Ghi log và báo lỗi chung
                error_log("MySQLi execute failed for user insert: " . mysqli_stmt_error($stmt_insert));
                $errors['database'] = "An error occurred during registration. Please try again later (insert exec).";
                 mysqli_stmt_close($stmt_insert); // Đóng statement ngay cả khi lỗi execute
            }
        }
    }

    // Đóng kết nối nếu chưa đóng (ví dụ khi có lỗi validation sớm)
    if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) {
       @mysqli_close($conn); // Dùng @ để tránh warning nếu kết nối đã đóng
    }

} // Kết thúc if ($_SERVER["REQUEST_METHOD"] == "POST")

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up- Let's Travel</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #030303;
            color: #F1EFEC;
        }
        .navbar { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            background-color: #123458; 
            padding: 10px 20px; 
            color: #F1EFEC; 
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); 
            position: relative; z-index: 10; 
        }
        .navbar-logo { 
            display: flex; 
            align-items: center; 
        }
        .navbar-logo img { 
            height: 50px; 
            width: auto; 
            margin-right: 10px; 
        }
        .navbar-logo span { 
            font-size: 1.5rem; 
            font-weight: bold; 
            color: #F1EFEC; 
        }
        .navbar-links { 
            display: flex; 
            gap: 20px; 
        }
        .navbar-links a { 
            color: #F1EFEC; 
            text-decoration: none; 
            font-weight: bold; 
            transition: color 0.3s ease; 
        }
        .navbar-links a:hover { color: #D4C9BE; }
        .register-hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
        }
        .register-container {
            background: rgba(18, 52, 88, 0.85);
            padding: 35px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            color: #F1EFEC;
        }
        .register-container h1 { text-align: center; margin-bottom: 25px; font-weight: bold; }
        .form-control { border-radius: 5px; background-color: rgba(3, 3, 3, 0.7); color: #F1EFEC; border: 1px solid #D4C9BE; padding: 10px 15px; }
        .form-control:focus { background-color: rgba(3, 3, 3, 0.8); color: #F1EFEC; border-color: #ffffff; box-shadow: 0 0 0 0.2rem rgba(212, 201, 190, 0.25); }
        .btn-register {
            width: 100%;
            padding: 12px;
            font-size: 1.1rem;
            border-radius: 5px;
            background-color: #D4C9BE;
            color: #030303;
            border: none;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .btn-register:hover { background-color: #bdae9f; color: #030303; }
        .text-danger { font-size: 0.9rem; color: #ffdddd; margin-top: 5px; display: block; }
        .login-link a { color: #D4C9BE; font-weight:bold; text-decoration: none; }
        .login-link a:hover { text-decoration: underline;}
        .alert-database-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #030303;
        }
        @media (max-width: 768px) {
            .register-container { padding: 20px 10px; }
            .navbar { flex-direction: column; align-items: flex-start; }
            .navbar-links { flex-direction: column; gap: 10px; align-items: flex-start; width: 100%; margin-top: 10px; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-logo">
            <img src="../uploads/logo.jpg" alt="Logo">
            <span>Let's Travel</span>
        </div>
        <div class="navbar-links">
            <a href="../auth/aboutus.php">About us</a>
            <a href="../auth/login.php">Login</a>
            <a href="../auth/register.php">Sign Up</a>
        </div>
    </div>

    <div class="register-hero">
        <div class="register-container">
            <h1 class="mb-4">Sign Up</h1>
            <?php
            if (isset($errors['database'])) {
                echo '<div class="alert alert-danger alert-database-error">' . htmlspecialchars($errors['database']) . '</div>';
            }
            ?>
            <form action="register.php" method="POST" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-danger"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required aria-describedby="passwordHelpBlock">
                    <div id="passwordHelpBlock" class="form-text" style="color:#bdae9f;">
                        Your password must be at least 6 characters long.
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="text-danger"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>" id="password_confirm" name="password_confirm" required>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <div class="text-danger"><?php echo $errors['password_confirm']; ?></div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-register">Register</button>
            </form>
            <p class="text-center mt-4 login-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>