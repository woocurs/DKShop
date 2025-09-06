<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Change if you have password
$db   = 'encryption_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
