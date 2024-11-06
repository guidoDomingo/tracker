<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tracking</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCmCOQkQoH7KDvifqpLNcrcLDl4lbhAT1Q&callback=initMap&libraries=geometry" async defer></script>
</head>
<body>
    <div id="map" style="height: 500px; width: 100%;"></div>

    <script>
        let map, marker, path = [];
        const deviceId = "866400058305579"; // ID del dispositivo

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: { lat: -25.363, lng: 131.044 } // Coordenadas iniciales
            });

            // Iniciar actualización de ubicación cada 5 segundos
            setInterval(updateLocation, 5000);
        }

        async function updateLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const { latitude, longitude, accuracy } = position.coords;
                        console.log("Ubicación capturada con precisión:", { latitude, longitude, accuracy });

                        // Enviar ubicación al backend
                        await fetch('/api/tracking/update-location', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Token CSRF para seguridad
                            },
                            body: JSON.stringify({
                                device_id: deviceId,
                                latitude: latitude,
                                longitude: longitude
                            })
                        }).then(response => response.json())
                          .then(data => console.log("Ubicación enviada:", data))
                          .catch(error => console.error('Error al enviar ubicación:', error));

                        // Obtener la ruta actualizada desde el backend
                        const response = await fetch(`/api/tracking/${deviceId}/route`);
                        const data = await response.json();
                        path = data.map(point => new google.maps.LatLng(point.latitude, point.longitude));

                        // Actualizar marcador y ruta en el mapa
                        if (!marker) {
                            marker = new google.maps.Marker({
                                position: path[path.length - 1],
                                map: map
                            });
                        } else {
                            marker.setPosition(path[path.length - 1]);
                        }

                        // Dibujar la ruta
                        new google.maps.Polyline({
                            path: path,
                            geodesic: true,
                            strokeColor: "#FF0000",
                            strokeOpacity: 1.0,
                            strokeWeight: 2,
                            map: map
                        });

                        map.panTo(marker.getPosition());

                        // Verificar si se ha llegado al destino
                        const destino = new google.maps.LatLng(-25.363, 131.044);
                        if (google.maps.geometry.spherical.computeDistanceBetween(marker.getPosition(), destino) < 50) {
                            alert("¡Has llegado al destino!");
                        }
                    },
                    (error) => {
                        console.error('Error al obtener la posición:', error);
                    },
                    {
                        enableHighAccuracy: true, // Precisión alta
                        timeout: 10000,          // Esperar hasta 10 segundos
                        maximumAge: 0            // No usar caché
                    }
                );
            } else {
                console.error("Geolocalización no es soportada por este navegador.");
            }
        }
    </script>
</body>
</html>
