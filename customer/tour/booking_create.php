<?php
session_start();
require_once '../../database/connect-db.php';

// Redirect if not logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: ../auth/login.php");
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tour_id = (int)($_POST['tour_id'] ?? 0);
    $customer_id = (int)$_SESSION['UserID'];
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $participants = (int)($_POST['participants'] ?? 0);
    $booking_date = $_POST['booking_date'] ?? '';
    $special_requests = trim($_POST['special_requests'] ?? '');
    $total_price = (float)($_POST['total_price'] ?? 0);

    // Validate inputs
    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (empty($phone) || !preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = "Valid phone number (10-15 digits) is required.";
    }
    if ($participants < 1) {
        $errors[] = "At least one participant is required.";
    }
    if (empty($booking_date) || strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $errors[] = "Valid future booking date is required.";
    }

    // Fetch tour details
    $sql = "SELECT user_id, price, group_size FROM tour_posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tour_id);
    $stmt->execute();
    $tour = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$tour) {
        $errors[] = "Tour not found.";
    } elseif ($participants > $tour['group_size']) {
        $errors[] = "Participants exceed group size limit.";
    } elseif ($total_price != $tour['price'] * $participants) {
        $errors[] = "Invalid total price.";
    }

    // Insert booking if no errors
    if (empty($errors)) {
        $owner_id = $tour['user_id'];
        $sql = "INSERT INTO bookings (tour_id, customer_id, owner_id, booking_date, participants, total_price, full_name, email, phone, special_requests, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisidssss", $tour_id, $customer_id, $owner_id, $booking_date, $participants, $total_price, $full_name, $email, $phone, $special_requests);
        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            header("Location: payment.php?booking_id=$booking_id");
            exit();
        } else {
            $errors[] = "Failed to create booking: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();

// Display errors if any (fallback)
if (!empty($errors)) {
    echo "<div class='alert alert-danger'>" . implode("<br>", array_map('htmlspecialchars', $errors)) . "</div>";
}
?>