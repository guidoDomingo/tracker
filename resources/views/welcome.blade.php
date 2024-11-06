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
        let map, marker, polyline, destinationMarker;
        let path = [];
        const deviceId = "866400058305579"; // ID del dispositivo
        const destination = { lat: -25.2844707, lng: -57.5631504 }; // Coordenadas de Paseo La Galería

        function initMap() {
            // Inicializar el mapa centrado en el destino inicialmente
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: destination
            });

            // Marcar el destino en el mapa (Paseo La Galería)
            destinationMarker = new google.maps.Marker({
                position: destination,
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 6,
                    fillColor: "green",
                    fillOpacity: 0.8,
                    strokeColor: "green",
                    strokeWeight: 2
                },
                title: "Destino: Paseo La Galería"
            });

            // Crear una línea de polilínea para la ruta en tiempo real
            polyline = new google.maps.Polyline({
                path: [],
                geodesic: true,
                strokeColor: "#FF0000", // Color rojo para la ruta actual hacia el destino
                strokeOpacity: 1.0,
                strokeWeight: 2,
                map: map
            });

            // Iniciar actualización de ubicación cada 5 segundos
            setInterval(updateLocation, 5000);
        }

        async function updateLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const { latitude, longitude, accuracy } = position.coords;
                        console.log("Ubicación capturada:", { latitude, longitude, accuracy });

                        // Enviar ubicación al backend
                        try {
                            const response = await fetch('/api/tracking/update-location', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    device_id: deviceId,
                                    latitude: latitude,
                                    longitude: longitude
                                })
                            });
                            const responseData = await response.json();
                            console.log("Ubicación enviada:", responseData);
                        } catch (error) {
                            console.error('Error al enviar ubicación:', error);
                        }

                        // Actualizar el marcador de la ubicación actual en el mapa
                        const currentPosition = new google.maps.LatLng(latitude, longitude);

                        if (!marker) {
                            marker = new google.maps.Marker({
                                position: currentPosition,
                                map: map,
                                icon: {
                                    path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                                    scale: 4,
                                    fillColor: "#0000FF",
                                    fillOpacity: 0.8,
                                    strokeColor: "#0000FF",
                                    strokeWeight: 2
                                },
                                title: "Tu ubicación actual"
                            });
                        } else {
                            marker.setPosition(currentPosition);
                        }

                        // Actualizar la polilínea de la ruta actual hacia el destino
                        path = [currentPosition, destination]; // Ruta directa al destino
                        polyline.setPath(path);
                        map.panTo(currentPosition);

                        // Verificar si se ha llegado al destino
                        const distance = google.maps.geometry.spherical.computeDistanceBetween(
                            currentPosition,
                            destinationMarker.getPosition()
                        );
                        if (distance < 50) {
                            alert("¡Has llegado a Paseo La Galería!");
                            clearInterval(updateLocation);
                        }
                    },
                    (error) => {
                        console.error('Error al obtener la posición:', error);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            } else {
                console.error("Geolocalización no es soportada por este navegador.");
            }
        }
    </script>
</body>
</html>
