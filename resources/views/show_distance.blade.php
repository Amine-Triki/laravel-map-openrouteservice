<!DOCTYPE html>
<html>

<head>
    <title>Show distance on map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 850px;
            width: 100%;
        }
    </style>
</head>

<body>
    <h1>Show distance on map</h1>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        /// set view
        var map = L.map('map').setView([{{ $distance->current_y }}, {{ $distance->current_x }}], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);


        /// add Starting point
        var markerStart = L.marker([{{ $distance->current_x }}, {{ $distance->current_y }}]).addTo(map)
            .bindPopup('Starting point').openPopup();

        /// add End point
        var markerEnd = L.marker([{{ $distance->target_x }}, {{ $distance->target_y }}]).addTo(map)
            .bindPopup('End point').openPopup();

        @php
            $geometries = json_decode($distance->geometry);
            $distances = json_decode($distance->distance);
        @endphp
        /// path (geometric shape)
        color = ['red', 'blue', 'purple'];
        @foreach ($geometries as $index => $geometry)
            var coordinates = {!! json_encode($geometry->coordinates) !!};
            var latlngs = coordinates.map(function(coord) {
                return [coord[1], coord[0]];
            });
            var currentColor = color[{{ $index }}];
            var polyline = L.polyline(latlngs, {
                color: currentColor
            }).addTo(map);
            map.fitBounds(polyline.getBounds());

            /// Calculate the middle of the path
            var midpointIndex = Math.floor(latlngs.length / 2);
            var midpoint = latlngs[midpointIndex];

            /// popup
            var distanceInKm = {{ $distances[$index] }};
            var popupContent = '<span style="color:' + currentColor + ';">Distance: ' + distanceInKm + ' km</span>';
            var popup = L.popup()
                .setLatLng(midpoint)
                .setContent(popupContent)
                .addTo(map);

            polyline.bindPopup(popup);
        @endforeach
    </script>
</body>

</html>
