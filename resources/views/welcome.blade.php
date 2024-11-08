<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tracking</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCmCOQkQoH7KDvifqpLNcrcLDl4lbhAT1Q&callback=initMap&libraries=geometry" async defer></script>
</head>
<body>
    <div id="map" style="height: 500px; width: 100%;"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let map, destinationMarker;
        const markers = {}; // Diccionario para almacenar marcadores por `device_id`
        const polylines = {}; // Diccionario para almacenar polilíneas por `device_id`
        const destination = { lat: -25.2844707, lng: -57.5631504 }; // Coordenadas de Paseo La Galería
        let deviceId; // Cambia esto a un ID único de cada dispositivo
        let marker; // Marcador del dispositivo actual

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: destination
            });

            // Marcar el destino en el mapa
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

            // Actualizar la ubicación del dispositivo y obtener ubicaciones de todos los dispositivos cada 5 segundos
            setInterval(() => {
                updateLocation();
                fetchAllLocations();
            }, 5000);
        }

        async function getDeviceId() {
            try {
                const response = await fetch('/api/tracking/set-device-identifier');
                const data = await response.json();
                deviceId = data.device_id; // Asigna el device_id a la variable global
                console.log("device_id asignado:", deviceId);
            } catch (error) {
                console.error("Error al obtener el device_id:", error);
            }
        }

      // Llama a getDeviceId() al cargar la página para obtener el device_id
        getDeviceId();


        async function updateLocation() {

            if (!deviceId) {
                console.error("No se ha asignado un device_id.");
                return;
            }

            console.log("deviceId recuperado:", deviceId);

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const { latitude, longitude, accuracy } = position.coords;
                        console.log("Ubicación capturada con precisión:", { latitude, longitude, accuracy });

                        // Enviar ubicación al backend
                        await fetch('/api/tracking/update-location', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                device_id: deviceId,
                                latitude: latitude,
                                longitude: longitude
                            })
                        })
                        .then(response => {
                            if (response.redirected) {
                                console.warn('La solicitud fue redirigida a:', response.url);
                            }
                            return response.text(); // Usa text para ver el contenido
                        })
                        .then(text => console.log("Respuesta recibida:", text))
                        .catch(error => console.error('Error al enviar ubicación:', error));



                        // Obtener la ruta completa desde el backend
                        const response = await fetch(`/api/tracking/${deviceId}/route`);
                        const data = await response.json();
                        const path = data.map(point => {
                            if (typeof point.latitude === 'number' && typeof point.longitude === 'number') {
                                return new google.maps.LatLng(point.latitude, point.longitude);
                            } else {
                                console.error("Coordenadas inválidas:", point);
                                return null;
                            }
                        }).filter(point => point !== null);

                        // Verificar si el path tiene coordenadas válidas
                        if (path.length === 0) {
                            console.error("No se encontraron coordenadas válidas en la respuesta.");
                            return;
                        }

                        // Actualizar el marcador del dispositivo en la última posición de la ruta
                        const currentPosition = path[path.length - 1];
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
                                title: `Dispositivo: ${deviceId}`
                            });
                        } else {
                            marker.setPosition(currentPosition);
                        }

                        // Dibujar o actualizar la línea roja de la ruta
                        if (!polylines[deviceId]) {
                            polylines[deviceId] = new google.maps.Polyline({
                                path: path,
                                geodesic: true,
                                strokeColor: "#FF0000",
                                strokeOpacity: 1.0,
                                strokeWeight: 2,
                                map: map
                            });
                        } else {
                            polylines[deviceId].setPath(path); // Actualiza la ruta con el nuevo camino
                        }

                        // Centrar el mapa en la posición actual del marcador
                        map.panTo(marker.getPosition());

                        // Verificar si se ha llegado al destino
                        const destino = new google.maps.LatLng(destination.lat, destination.lng);
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

        async function fetchAllLocations() {
            try {
                const response = await fetch('/api/tracking/all-locations');
                const locations = await response.json();

                locations.forEach(device => {
                    const { device_id, latitude, longitude } = device;
                    const currentPosition = new google.maps.LatLng(latitude, longitude);

                    // Si el marcador del dispositivo ya existe, actualízalo; si no, créalo
                    if (markers[device_id]) {
                        markers[device_id].setPosition(currentPosition);
                    } else {
                        markers[device_id] = new google.maps.Marker({
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
                            title: `Dispositivo: ${device_id}`
                        });
                    }

                    // Si la polilínea del dispositivo ya existe, actualiza el camino; si no, créala
                    if (!polylines[device_id]) {
                        polylines[device_id] = new google.maps.Polyline({
                            path: [currentPosition, destination], // Línea desde el origen al destino
                            geodesic: true,
                            strokeColor: "#FF0000", // Color rojo para la línea al destino
                            strokeOpacity: 1.0,
                            strokeWeight: 2,
                            map: map
                        });
                    } else {
                        // Actualizar la polilínea para reflejar la posición actual al destino
                        polylines[device_id].setPath([currentPosition, destination]);
                    }
                });
            } catch (error) {
                console.error('Error al obtener ubicaciones de dispositivos:', error);
            }
        }
    </script>
</body>
</html>
