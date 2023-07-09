<?php
require_once "config.php";

// Set the UTF-8 character encoding
mysqli_set_charset($connection, "utf8");

// Drop the "cities" and "neighbours" tables if they exist
$query = "DROP TABLE IF EXISTS komsular";
mysqli_query($connection, $query);

$query = "DROP TABLE IF EXISTS sehirler";
mysqli_query($connection, $query);

// Create the "cities" table
$query = "CREATE TABLE sehirler (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    cityName VARCHAR(255) NOT NULL,
    plateCode INT(2) NOT NULL,
    latitude FLOAT(12) NOT NULL,
    longitude FLOAT(12) NOT NULL
)";
mysqli_query($connection, $query);

// Import cities from the JSON file
$jsonFile = "sehirler.json"; // Replace with your JSON file name
$jsonData = file_get_contents($jsonFile);
$citiesData = json_decode($jsonData, true);

// Array to store unique city names and plate codes
$uniqueCityNames = [];
$uniquePlateCodes = [];

// Loop through the cities data and collect unique city names and plate codes
foreach ($citiesData as $cityData) {
    $cityName = mysqli_real_escape_string($connection, $cityData['cityName']);
    $plateCode = mysqli_real_escape_string($connection, $cityData['plateCode']);
    $latitude = mysqli_real_escape_string($connection, $cityData['latitude']);
    $longitude = mysqli_real_escape_string($connection, $cityData['longitude']);
    
    $query = "INSERT INTO sehirler (cityName, plateCode, latitude, longitude) VALUES ('$cityName', $plateCode, $latitude, $longitude)";
    mysqli_query($connection, $query);
}



// Create the "neighbours" table
$query = "CREATE TABLE komsular (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    city1_id INT(11) NOT NULL,
    city2_id INT(11) NOT NULL,
    FOREIGN KEY (city1_id) REFERENCES sehirler(id),
    FOREIGN KEY (city2_id) REFERENCES sehirler(id)
)";
mysqli_query($connection, $query);

// Process neighborliness relationships
foreach ($citiesData as $cityData) {
    $cityName = mysqli_real_escape_string($connection, $cityData['cityName']);

    // Get the city ID
    $query = "SELECT id FROM sehirler WHERE cityName = '$cityName'";
    $result = mysqli_query($connection, $query);
    $row = mysqli_fetch_assoc($result);
    $cityId = $row['id'];

    // Insert neighborliness relationships
    foreach ($cityData['komsular'] as $neighbor) {
        $neighbor = mysqli_real_escape_string($connection, $neighbor);

        // Get the neighbor's city ID
        $query = "SELECT id FROM sehirler WHERE cityName = '$neighbor'";
        $result = mysqli_query($connection, $query);
        $row = mysqli_fetch_assoc($result);
        $neighborId = $row['id'];

        $query = "INSERT INTO komsular (city1_id, city2_id) VALUES ('$cityId', '$neighborId')";
        mysqli_query($connection, $query);
    }
}

echo "Initialization script executed successfully.";
?>
