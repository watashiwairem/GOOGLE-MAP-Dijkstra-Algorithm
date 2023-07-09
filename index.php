<!DOCTYPE html>
<html>
<head>
    <title>MAP</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://api.mqcdn.com/sdk/mapquest-js/v1.3.2/mapquest.js"></script>
    <link type="text/css" rel="stylesheet" href="https://api.mqcdn.com/sdk/mapquest-js/v1.3.2/mapquest.css"/>
    <script src="https://www.mapquestapi.com/directions/v2/route?key=jOOgGojCabO9NMo6gRTZbLlVU2EsDmXl&"></script>
    <script src="https://www.mapquestapi.com/geocoding/v1/address?key=jOOgGojCabO9NMo6gRTZbLlVU2EsDmXl&"></script>
    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>
	<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <style>

    body {
        background-image: url('background.png');
        background-repeat: no-repeat;
        background-size: cover;
    }

    select {
        display: block;
        margin-bottom: 10px;
    }

    .form-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .form-row {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .form-row label {
        margin-right: 10px;
    }

    .form-row select {
        padding: 5px;
    }

    .my-button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        text-align: center;
        text-decoration: none;
        font-size: 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .my-button:hover {
        background-color: #45a049;
    }

    .my-button:active {
        background-color: #3e8e41;
    }

    .form-row select {
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    .form-row select:focus {
        outline: none;
        border-color: #4CAF50;
    }

    #map {
    height: 400px;
    width: 100%; /* Haritanın genişliği, ekrana yayılacak şekilde ayarlandı */
    position: relative;
    overflow: hidden;
    border: 1px solid #ccc;
    }

    </style>
</head>
<body>
    <h2 style="margin-left:auto; margin-right:auto;width:320px;height:30px; border-top-style:solid;border-bottom-style:solid; border-left-style:double;border-right-style:double;">En Kısa Mesafe Hesaplayıcı</h2>

<div class="form-container">
  <div class="form-row">
    <label for="startCity">Başlangıç:</label>
    <select id="startCity">
      <option value="">Başlangıç Şehri Seçiniz</option>
      <!-- Populate options dynamically using PHP/MySQL -->
      <?php
      require 'config.php';
      $query = "SELECT * FROM sehirler";
      $result = mysqli_query($connection, $query);
      while ($row = mysqli_fetch_assoc($result)) {
          echo '<option value="' . $row['id'] . '">' . $row['cityName'] . '</option>';
      }
      ?>
    </select>
  </div>
  <div class="form-row">
    <label for="endCity">Varış:</label>
    <select id="endCity">
      <option value="">Varış Şehri Seçiniz</option>
      <!-- Populate options dynamically using PHP/MySQL -->
      <?php
      mysqli_data_seek($result, 0); // Reset the result pointer
      while ($row = mysqli_fetch_assoc($result)) {
          echo '<option value="' . $row['id'] . '">' . $row['cityName'] . '</option>';
      }
      ?>
    </select>
    </div>

    <button class = my-button id="calculateBtn">Gönder</button>
    <br>

    <div id="result"></div>
</div>

<div id="map"></div>

<script>
        $(document).ready(function() {
    $('#calculateBtn').click(function() {
        var startCityId = $('#startCity').val();
        var endCityId = $('#endCity').val();

        if (startCityId && endCityId) {
            $.ajax({
                url: 'dijkstra.php',
                type: 'GET',
                data: {
                    startCityId: startCityId,
                    endCityId: endCityId
                },
                dataType: 'html',
                success: function(response) {
                    $('#result').html(response);

                    // Rotayı haritada göstermek için getDirections fonksiyonunu çağırın
                    initMap(startCityId, endCityId);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        } else {
            $('#result').html('Lütfen Şehir Giriniz');
        }
    });
});

</script>
<!--
<script>
L.mapquest.key = 'jOOgGojCabO9NMo6gRTZbLlVU2EsDmXl'; // MapQuest API anahtarınızı buraya ekleyin

// MapQuest haritasını oluşturun ve `map` div öğesine yerleştirin
let map = L.mapquest.map('map', {
    center: [39.00184216342313, 35.64374749444209], // Haritanın orta noktası
    layers: L.mapquest.tileLayer('map'),
    zoom: 6 // Başlangıç yakınlaştırma düzeyi
});

// Yol tarifini almak için bir işlev oluşturun
function getDirections(startCityId, endCityId) {
    // İşlemi başlatmadan önce haritayı temizleyin
    map.removeRoutes();

    // Yol tarifini almak için MapQuest Directions API'sini kullanın
    L.mapquest.directions().route({
        start: startCityId,
        end: endCityId
    }).addTo(map);
}

</script>
-->


<script>
var map = L.map('map').setView([39.00184216342313, 35.64374749444209], 11);
		mapLink = "<a href='http://openstreetmap.org'>OpenStreetMap</a>";
		L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', { attribution: 'Leaflet &copy; ' + mapLink + ', contribution', maxZoom: 18 }).addTo(map);

		var taxiIcon = L.icon({
			iconUrl: 'img/taxi.png',
			iconSize: [70, 70]
		})
        var startCityLatitude =39.00184216342313; // Başlangıç şehrinin enlem değeri
        var startCityLongitude = 35.64374749444209; // Başlangıç şehrinin boylam değeri

		var marker = L.marker([startCityLatitude, startCityLongitude], { icon: taxiIcon }).addTo(map);

		map.on('click', function (e) {
			console.log(e)
			var newMarker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
			L.Routing.control({
				waypoints: [
					L.latLng(38.42939395176317, 27.127768205076755),
					L.latLng(e.latlng.lat, e.latlng.lng)
				]
			}).on('routesfound', function (e) {
				var routes = e.routes;
				console.log(routes);

				e.routes[0].coordinates.forEach(function (coord, index) {
					setTimeout(function () {
						marker.setLatLng([coord.lat, coord.lng]);
					}, 100 * index)
				})

			}).addTo(map);
		});



</script>



</body>
</html>
