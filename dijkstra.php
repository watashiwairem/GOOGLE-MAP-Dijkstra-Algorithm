<?php
// Veritabanı bağlantısını sağlama
$connection = mysqli_connect("localhost", "root", "", "komsuluklar");

// Bağlantıyı kontrol et
if (!$connection) {
    die("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
}

// En kısa yolun hesaplanması için Dijkstra algoritmasını kullanarak en kısa yolu bulan fonksiyon
function findShortestPath($startCityId, $endCityId, $connection)
{
    $distances = [];
    $previous = [];
    $unvisited = [];

    // Başlangıçta tüm mesafeleri sonsuza, önceki şehirleri null olarak ayarla
    $query = "SELECT id FROM sehirler";
    $result = mysqli_query($connection, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $cityId = $row['id'];
        $distances[$cityId] = INF; // Başlangıçta mesafeleri sonsuz olarak ayarla
        $previous[$cityId] = null; // Başlangıçta önceki şehirleri null olarak ayarla
        $unvisited[$cityId] = true; // Tüm şehirleri ziyaret edilmemiş olarak işaretle
    }

    $distances[$startCityId] = 0; // Başlangıç şehrinin mesafesini 0 olarak ayarla

    while (!empty($unvisited)) {
        // En küçük mesafeli ziyaret edilmemiş şehri bul
        $closestCityId = null;
        foreach ($unvisited as $cityId => $value) {
            if ($closestCityId === null || $distances[$cityId] < $distances[$closestCityId]) {
                $closestCityId = $cityId;
            }
        }

        // Hedef şehre ulaşıldıysa dur
        if ($closestCityId === $endCityId) {
            break;
        }

        // Ziyaret edilmemiş listesinden en yakın şehri kaldır
        unset($unvisited[$closestCityId]);

        // Komşu şehirler için mesafeleri ve önceki değerleri güncelle
        $neighbors = getNeighbors($closestCityId, $connection);
        foreach ($neighbors as $neighborCityId) {
            $coordinates1 = getCoordinates($closestCityId, $connection);
            $coordinates2 = getCoordinates($neighborCityId, $connection);

            $distance = calculateDistance(
                $coordinates1['latitude'], $coordinates1['longitude'],
                $coordinates2['latitude'], $coordinates2['longitude']
            );

            $altDistance = $distances[$closestCityId] + $distance;
            if ($altDistance < $distances[$neighborCityId]) {
                $distances[$neighborCityId] = $altDistance;
                $previous[$neighborCityId] = $closestCityId;
            }
        }
    }

    // En kısa yolun geri izlenerek bulunması
    $path = [];
    $currentCityId = $endCityId;

    while ($currentCityId !== null) {
        $path[] = $currentCityId;
        $currentCityId = $previous[$currentCityId];
    }

    // Yolu tersine çevirerek doğru sırayı elde et
    $path = array_reverse($path);

    return $path;
}

// Veritabanından şehir komşularını almak için fonksiyon
function getNeighbors($cityId, $connection)
{
    $neighbors = [];

    $query = "SELECT city2_id FROM komsular WHERE city1_id = $cityId";
    $result = mysqli_query($connection, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $neighbors[] = $row['city2_id'];
    }

    return $neighbors;
}

// Şehirlerin koordinatlarını veritabanından almak için fonksiyon
function getCoordinates($cityId, $connection)
{
    $query = "SELECT latitude, longitude FROM sehirler WHERE id = $cityId";
    $result = mysqli_query($connection, $query);

    $row = mysqli_fetch_assoc($result);

    return [
        'latitude' => $row['latitude'],
        'longitude' => $row['longitude']
    ];
}

// İki şehir arasındaki mesafeyi hesaplamak için fonksiyon
function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371; // Dünya yarıçapı (kilometre cinsinden)

    $latDiff = deg2rad($lat2 - $lat1);
    $lonDiff = deg2rad($lon2 - $lon1);

    $a = sin($latDiff / 2) * sin($latDiff / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDiff / 2) * sin($lonDiff / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $earthRadius * $c;
    return round($distance, 2); // İki ondalık basamağa yuvarla
}

$startCityId = $_GET['startCityId'];
$endCityId = $_GET['endCityId'];

$path = findShortestPath($startCityId, $endCityId, $connection);

// En kısa yolu ekrana yazdırma
echo "Rota Belirlendi: " ;
foreach ($path as $index => $cityId) {
    $query = "SELECT cityName FROM sehirler WHERE id = $cityId";
    $result = mysqli_query($connection, $query);
    $row = mysqli_fetch_assoc($result);

    echo $row['cityName'];

    if ($index < count($path) - 1) {
        echo " -> ";
    }
}

// Toplam mesafeyi hesaplama
$totalDistance = 0;
for ($i = 0; $i < count($path) - 1; $i++) {
    $cityId1 = $path[$i];
    $cityId2 = $path[$i + 1];

    $coordinates1 = getCoordinates($cityId1, $connection);
    $coordinates2 = getCoordinates($cityId2, $connection);

    $distance = calculateDistance(
        $coordinates1['latitude'], $coordinates1['longitude'],
        $coordinates2['latitude'], $coordinates2['longitude']
    );

    $totalDistance += $distance;
}

echo "<br>";
echo "<br>";

echo " İki Şehir Arasındaki Mesafe: " . $totalDistance . " km";

// Veritabanı bağlantısını kapatma
mysqli_close($connection);
?>

