<?php
require 'config.php';

$searchQuery = $_GET['searchQuery'];

$query = "SELECT * FROM cities WHERE cityName LIKE '%$searchQuery%' OR plateCode LIKE '%$searchQuery%'";
$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) > 0) {
    echo '<h3>Search Result</h3>';
    echo '<ul>';
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<li>' . $row['cityName'] . ' (Plate Code: ' . $row['plateCode'] . ')</li>';
    }
    echo '</ul>';
} else {
    echo '<p>No cities found.</p>';
}
?>
