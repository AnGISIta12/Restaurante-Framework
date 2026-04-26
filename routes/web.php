<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\ReservacionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ReporteController;

/*
|--------------------------------------------------------------------------
| Rutas públicas — Autenticación
|--------------------------------------------------------------------------
*/
Route::get('/',        [AuthController::class, 'showLogin'])->name('login');
Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/registro',  [AuthController::class, 'showRegistro'])->name('registro');
Route::post('/registro', [AuthController::class, 'registro']);

/*
|--------------------------------------------------------------------------
| Dashboard — cualquier usuario autenticado
|--------------------------------------------------------------------------
*/
Route::middleware('auth.session.custom')->group(function () {

    Route::get('/dashboard', function () {
        $rol = session('rol');
        return view('dashboard', compact('rol'));
    })->name('dashboard');

    /*--------------------------------------------------------------
     | Mesas — Administrador
     --------------------------------------------------------------*/
    Route::middleware('role:Administrador')->group(function () {
        Route::resource('mesas', MesaController::class)->except(['show']);
        Route::resource('empleados', EmpleadoController::class)->except(['show']);
        Route::get('/reportes',              [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/reservaciones',[ReporteController::class, 'reservaciones'])->name('reportes.reservaciones');
        Route::get('/reportes/pedidos',      [ReporteController::class, 'pedidos'])->name('reportes.pedidos');
    });

    /*--------------------------------------------------------------
     | Reservaciones — Maitre y Administrador
     --------------------------------------------------------------*/
    Route::middleware('role:Maitre,Administrador')->group(function () {
        Route::get('/reservaciones/asignar',         [ReservacionController::class, 'asignar'])->name('reservaciones.asignar');
        Route::post('/reservaciones/asignar',        [ReservacionController::class, 'guardarAsignacion'])->name('reservaciones.guardarAsignacion');
        Route::get('/reservaciones/verificar',       [ReservacionController::class, 'verificar'])->name('reservaciones.verificar');
        Route::get('/reservaciones/cupo',            [ReservacionController::class, 'cupo'])->name('reservaciones.cupo');
        Route::get('/reservaciones/proximas',        [ReservacionController::class, 'proximas'])->name('reservaciones.proximas');
        Route::get('/reservaciones/crear',           [ReservacionController::class, 'create'])->name('reservaciones.create');
        Route::post('/reservaciones',                [ReservacionController::class, 'store'])->name('reservaciones.store');
    });

    /*--------------------------------------------------------------
     | Menú — Administrador y Maitre pueden editar; Cocinero ve
     --------------------------------------------------------------*/
    Route::middleware('role:Administrador,Maitre')->group(function () {
        Route::resource('menu', MenuController::class)->except(['show']);
    });
    // Cocinero puede ver el menú
    Route::middleware('role:Cocinero,Administrador,Maitre,Mesero')->group(function () {
        Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    });

    /*--------------------------------------------------------------
     | Pedidos — Mesero crea; Cocinero gestiona cocina
     --------------------------------------------------------------*/
    Route::middleware('role:Mesero,Administrador')->group(function () {
        Route::get('/pedidos',           [PedidoController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/crear',     [PedidoController::class, 'create'])->name('pedidos.create');
        Route::post('/pedidos',          [PedidoController::class, 'store'])->name('pedidos.store');
        Route::get('/pedidos/{pedido}',  [PedidoController::class, 'show'])->name('pedidos.show');
        Route::get('/entregas/listas',   [PedidoController::class, 'listas'])->name('pedidos.listas');
        Route::post('/ordenes/{orden}/entregar', [PedidoController::class, 'entregar'])->name('ordenes.entregar');
    });

    Route::middleware('role:Cocinero,Administrador')->group(function () {
        Route::get('/cocina',  [PedidoController::class, 'cocina'])->name('pedidos.cocina');
        Route::post('/ordenes/{orden}/estado', [PedidoController::class, 'cambiarEstado'])->name('ordenes.estado');
    });

    /*--------------------------------------------------------------
     | Cliente — solicitar reservación e historial
     --------------------------------------------------------------*/
    Route::middleware('role:Cliente')->group(function () {
        Route::get('/solicitar-reservacion',  [ReservacionController::class, 'solicitar'])->name('reservaciones.solicitar');
        Route::post('/solicitar-reservacion', [ReservacionController::class, 'guardarSolicitud'])->name('reservaciones.guardarSolicitud');
        Route::get('/mis-reservaciones',      [ReservacionController::class, 'historialCliente'])->name('cliente.reservaciones');
    });
});