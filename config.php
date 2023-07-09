<?php
// MySQL database configuration
$hostname = "localhost"; // Replace with your MySQL server hostname
$username = "root"; // Replace with your MySQL server username
$password = ""; // Replace with your MySQL server password
$database = "komsuluklar"; // Replace with your MySQL database name

// Create a connection to the MySQL server
$connection = mysqli_connect($hostname, $username, $password, $database);

// Check if the connection was successful
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
