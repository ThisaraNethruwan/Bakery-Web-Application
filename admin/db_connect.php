<?php
// Database connection parameters
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "Nishan_Bakery";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to safely close database connection
function closeConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>