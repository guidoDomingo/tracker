<?php

namespace App\Http\Controllers;

use App\Models\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Uid\Uuid;

class TrackingController extends Controller
{
    public function updateLocation(Request $request)
    {

        Log::info($request->all());
        $request->validate([
            'device_id' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);


        // Encuentra el último registro o crea uno nuevo si no existe
        Tracking::updateOrCreate(
            ['device_id' => $request->device_id],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'last_tracked_at' => now()
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function getRoute($device)
    {
        // Obtén la última ubicación del dispositivo
        $route = Tracking::where('device_id', $device)->get(['latitude', 'longitude', 'last_tracked_at']);
        return response()->json($route);
    }

    public function locations()
    {
        // Obtén la última ubicación del dispositivo
        $tracking = Tracking::all(['device_id', 'latitude', 'longitude', 'last_tracked_at']);
        return response()->json($tracking);
    }

    public function setDeviceIdentifier(Request $request)
    {
        if (!$request->cookie('device_id')) {
            // Genera un UUID como identificador único
            $deviceId = (string) \Illuminate\Support\Str::uuid();
    
            // Log el deviceId como un array para evitar errores
            Log::info('Se ha asignado un nuevo device_id', ['device_id' => $deviceId]);
    
            // Almacena el UUID en una cookie por un tiempo largo (5 años)
            return response()->json(['device_id' => $deviceId])
                ->cookie('device_id', $deviceId, 60 * 24 * 365 * 5); // Cookie por 5 años
        }
    
        $deviceId = $request->cookie('device_id');
        Log::info('Dispositivo ya tiene un device_id', ['device_id' => $deviceId]);
    
        return response()->json(['device_id' => $deviceId]);
    }
    
}


