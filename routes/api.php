<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MesaController;
use App\Http\Controllers\Api\ReservacionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas de Recursos API para Mesas y Reservaciones
Route::apiResource('mesas', MesaController::class);
Route::apiResource('reservaciones', ReservacionController::class);
