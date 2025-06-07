<?php

require_once '../../database/connect-db.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Clean and validate input data
$tour_post_id = filter_input(INPUT_POST, 'tour_post_id', FILTER_VALIDATE_INT);
$guest_user_id = (int)$_SESSION['UserID'];
$guest_full_name = trim(filter_input(INPUT_POST, 'guest_full_name', FILTER_SANITIZE_STRING));
$full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
$num_people = filter_input(INPUT_POST, 'num_people', FILTER_VALIDATE_INT);
$travel_date = trim(filter_input(INPUT_POST, 'travel_date', FILTER_SANITIZE_STRING));
$end_date = trim(filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING));
$notes = trim(filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING));
$special_requests = trim(filter_input(INPUT_POST, 'special_requests', FILTER_SANITIZE_STRING));
$price = trim($_POST['price'] ?? '0');  // keep commas, no math

$owner_user_id = filter_input(INPUT_POST, 'owner_user_id', FILTER_VALIDATE_INT);

// Input validation
// $errors = [];
// if (!$tour_post_id) {
//     $errors[] = 'Invalid tour selection.';
// }
// if (empty($guest_full_name)) {
//     $errors[] = 'Guest full name is required.';
// }
// if (empty($full_name)) {
//     $errors[] = 'Contact name is required.';
// }
// if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//     $errors[] = 'Valid email address is required.';
// }
// if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
//     $errors[] = 'Valid phone number (10-15 digits) is required.';
// }
// if (!$num_people || $num_people < 1) {
//     $errors[] = 'At least one participant is required.';
// }
// if (empty($travel_date) || strtotime($travel_date) < strtotime(date('Y-m-d'))) {
//     $errors[] = 'Valid future travel start date is required.';
// }
// if (empty($end_date) || strtotime($end_date) <= strtotime($travel_date)) {
//     $errors[] = 'End date must be after travel start date.';
// }


// Return errors if any
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Check tour availability and details
    $tour_sql = "SELECT t.*, u.FirstName, u.LastName 
                 FROM tour_posts t 
                 JOIN user u ON t.user_id = u.UserID 
                 WHERE t.id = ? AND t.status = 'approved'";
    $stmt_tour = $conn->prepare($tour_sql);
    if (!$stmt_tour) {
        throw new Exception('Failed to prepare tour query: ' . $conn->error);
    }
    $stmt_tour->bind_param("i", $tour_post_id);
    $stmt_tour->execute();
    $tour = $stmt_tour->get_result()->fetch_assoc();
    $stmt_tour->close();

    if (!$tour) {
        throw new Exception('Tour not found or not available.');
    }


    // Use tour's user_id if owner_user_id is not provided
    $owner_user_id = $owner_user_id ?: $tour['user_id'];
    $owner_full_name = $tour['FirstName'] . ' ' . $tour['LastName'];

    // Insert booking
    $booking_sql = "INSERT INTO tour_booking (
        tour_post_id, guest_user_id, guest_full_name, owner_user_id, owner_full_name, 
        full_name, email, phone, num_people, travel_date, end_date, notes, 
        special_requests, price, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'waiting_response', NOW())";

    $stmt_booking = $conn->prepare($booking_sql);
    if (!$stmt_booking) {
        throw new Exception('Failed to prepare booking query: ' . $conn->error);
    }
    $stmt_booking->bind_param("iisissssisssds", 
        $tour_post_id, 
        $guest_user_id,
        $guest_full_name,
        $owner_user_id,
        $owner_full_name,
        $full_name,
        $email,
        $phone,
        $num_people,
        $travel_date,
        $end_date,
        $notes,
        $special_requests,
        $price
    );

    if (!$stmt_booking->execute()) {
        throw new Exception('Failed to create booking: ' . $stmt_booking->error);
    }

    $booking_id = $conn->insert_id;
    $stmt_booking->close();

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Booking request sent successfully! Booking ID: ' . $booking_id]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Booking error: ' . $e->getMessage()]);
}

$conn->close();
?>