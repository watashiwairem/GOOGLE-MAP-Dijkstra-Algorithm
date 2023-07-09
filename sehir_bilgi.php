<?php
require 'config.php';

$cityId = $_GET['id'];

$query = "SELECT * FROM sehirler WHERE id = '$cityId'";
$result = mysqli_query($connection, $query);
$city = mysqli_fetch_assoc($result);

# get the neighbours of the city and left join to sehirler table for names
$query = "SELECT sehirler.cityName AS neighbourName FROM komsular LEFT JOIN sehirler ON komsular.city2_id = sehirler.id WHERE komsular.city1_id = '$cityId'";
$result = mysqli_query($connection, $query);    
$neighbours = mysqli_fetch_all($result, MYSQLI_ASSOC);

# define a str_neighbor variable and join the neighbours' names
$str_neighbor = "";
foreach ($neighbours as $neighbour) {
    $str_neighbor .= $neighbour['neighbourName'] . ", ";
}

# remove the last comma and space
$str_neighbor = substr($str_neighbor, 0, -2);

// Display city information
echo '<h3>City Information</h3>';
echo '<p><strong>City Name:</strong> ' . $city['cityName'] . '</p>';
echo '<p><strong>Plate Code:</strong> ' . $city['plateCode'] . '</p>';
echo '<p><strong>Latitude:</strong> ' . $city['latitude'] . '</p>';
echo '<p><strong>Longitude:</strong> ' . $city['longitude'] . '</p>';
echo '<p><strong>Neighbours:</strong> ' . $str_neighbor . '</p>';
?>
