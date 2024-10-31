<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tracking</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>
</head>
<body>
    <div id="map" style="height: 500px; width: 100%;"></div>

    <script>
        let map, marker, path = [];
        const deviceId = "YOUR_DEVICE_ID"; // Reemplaza con el ID de tu dispositivo

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: { lat: -25.363, lng: 131.044 } // Coordenadas iniciales
            });

            // Inicia la ruta en tiempo real
            setInterval(async () => {
                // Obtener la ubicación del usuario
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(async (position) => {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        console.log("Ubicación capturada:", { latitude, longitude });

                        // Enviar ubicación al servidor
                        await fetch(`/api/tracking/update-location`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                device_id: deviceId,
                                latitude: latitude,
                                longitude: longitude
                            })
                        });

                        // Actualizar la ruta y el mapa
                        const response = await fetch(`/api/tracking/${deviceId}/route`);
                        const data = await response.json();

                        path = data.map(point => new google.maps.LatLng(point.latitude, point.longitude));
                       
                        if (!marker) {
                            marker = new google.maps.Marker({
                                position: path[path.length - 1],
                                map: map
                            });
                        } else {
                            marker.setPosition(path[path.length - 1]);
                        }

                        new google.maps.Polyline({
                            path: path,
                            geodesic: true,
                            strokeColor: "#FF0000",
                            strokeOpacity: 1.0,
                            strokeWeight: 2,
                            map: map
                        });

                        map.panTo(marker.getPosition());

                        // Alerta de llegada al destino
                        const destino = new google.maps.LatLng(-25.363, 131.044); // Define el destino
                        if (google.maps.geometry.spherical.computeDistanceBetween(marker.getPosition(), destino) < 50) {
                            alert("Has llegado al destino!");
                        }
                    });
                } else {
                    alert("La geolocalización no es compatible con este navegador.");
                }
            }, 5000); // Intervalo de 5 segundos
        }
    </script>
</body>
</html>
