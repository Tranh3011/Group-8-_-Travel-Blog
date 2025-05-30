<?php
session_start();

session_unset();

// clear session
session_destroy();

// redirect to login
header('location: login.php');