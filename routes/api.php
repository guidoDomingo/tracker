<?php

use App\Http\Controllers\TrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('tracking')->group(function () {
    Route::post('/update-location', [TrackingController::class, 'updateLocation']);
    Route::get('/{device}/route', [TrackingController::class, 'getRoute']);
    Route::get('all-locations', [TrackingController::class, 'locations']);
});
