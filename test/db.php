<?php
// Database connection
$servername = "localhost";  // or "127.0.0.1"
$username = "root";         // default user for XAMPP/WAMP
$password = "";             // leave blank if no password
$dbname = "testdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>