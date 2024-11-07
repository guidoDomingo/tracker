<?php

namespace App\Http\Controllers;

use App\Models\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function updateLocation(Request $request)
    {

        Log::info($request->all());
        $request->validate([
            'device_id' => 'required|integer',
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
}


