<?php
// Database connection
$dbhost = 'localhost:3307';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'travel blog';

$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname)
    or die ('Failed to connect to db.');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validate status
    $valid_statuses = ['waiting_response', 'responded', 'planning', 'done', 'cancelled'];
    if (in_array($status, $valid_statuses)) {
        $sql = "UPDATE tour_booking SET status = '$status', updated_at = CURRENT_TIMESTAMP WHERE id = '$id'";
        if (@mysqli_query($conn, $sql)) {
            // Redirect back to the listing page with filters preserved
            $query_params = $_GET;
            header('Location: tour_booking_list.php?' . http_build_query($query_params));
            exit;
        }
    }
}

@mysqli_close($conn);
?>