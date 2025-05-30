<?php 

include('settings.php');

// connect db
$conn = @mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);

if (!$conn) {
    header('location: ../errors/500.php');
}