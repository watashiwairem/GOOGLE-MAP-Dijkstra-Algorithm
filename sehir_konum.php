<?php
require 'config.php';

$cityId = $_GET['id'];

$query = "SELECT latitude, longitude FROM sehirler WHERE id = '$cityId'";
$result = mysqli_query($connection, $query);
$city = mysqli_fetch_assoc($result);

// Return city coordinates as JSON
header('Content-Type: application/json');
echo json_encode($city);
?>
