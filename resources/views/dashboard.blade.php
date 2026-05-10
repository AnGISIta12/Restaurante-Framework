@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $rol    = session('rol');
    $nombre = session('usuario_nombre');

    $config = [
        'Administrador' => [
            'titulo' => 'Panel Administrador',
            'subtitulo' => 'Control total del sistema',
            'icono' => '⚙️',
            'color1' => '#C0392B',
            'color2' => '#922B21',
        ],
        'Maitre' => [
            'titulo' => 'Panel Maitre',
            'subtitulo' => 'Gestión de reservaciones y mesas',
            'icono' => '🪑',
            'color1' => '#3F51B5',
            'color2' => '#1A237E',
        ],
        'Mesero' => [
            'titulo' => 'Panel Mesero',
            'subtitulo' => 'Gestión de pedidos y atención a mesas',
            'icono' => '🍷',
            'color1' => '#1E8449',
            'color2' => '#145A32',
        ],
        'Cocinero' => [
            'titulo' => 'Panel Cocinero',
            'subtitulo' => 'Preparación y gestión de órdenes',
            'icono' => '👨‍🍳',
            'color1' => '#E67E22',
            'color2' => '#BA4A00',
        ],
        'Cliente' => [
            'titulo' => 'Panel Cliente',
            'subtitulo' => 'Reservaciones e historial personal',
            'icono' => '🙋',
            'color1' => '#8E44AD',
            'color2' => '#512E5F',
        ],
    ];

    $rolConfig = $config[$rol] ?? [
        'titulo' => 'Panel Principal',
        'subtitulo' => 'Sistema de gestión',
        'icono' => '🍽️',
        'color1' => '#00BCD4',
        'color2' => '#001B44',
    ];
@endphp

<div class="dashboard-shell">

    <div class="role-banner" style="background:linear-gradient(135deg, {{ $rolConfig['color1'] }}, {{ $rolConfig['color2'] }});">
        <div class="role-icon">{{ $rolConfig['icono'] }}</div>
        <div>
            <h1>{{ $rolConfig['titulo'] }}</h1>
            <p>{{ $rolConfig['subtitulo'] }}</p>
            <small>Bienvenido, {{ $nombre }} · {{ now()->format('d/m/Y') }}</small>
        </div>
        <div class="role-circle"></div>
    </div>

    <div class="dashboard-grid">

        @if($rol === 'Administrador')
            <a href="{{ route('mesas.index') }}" class="dash-card">
                <span>🪑</span>
                <h3>Gestión de Mesas</h3>
                <p>Agregar, modificar y eliminar mesas del restaurante</p>
            </a>

            <a href="{{ route('menu.index') }}" class="dash-card featured">
                <span>🍽️</span>
                <h3>Gestión del Menú</h3>
                <p>Administrar platos, precios y categorías</p>
            </a>

            <a href="{{ route('empleados.index') }}" class="dash-card">
                <span>👥</span>
                <h3>Gestión de Empleados</h3>
                <p>Registrar y gestionar el personal del restaurante</p>
            </a>

            <a href="{{ route('reportes.reservaciones') }}" class="dash-card">
                <span>📅</span>
                <h3>Reporte de Reservaciones</h3>
                <p>Visualizar historial y estado de reservaciones</p>
            </a>

            <a href="{{ route('reportes.index') }}" class="dash-card">
                <span>📊</span>
                <h3>Reportes Generales</h3>
                <p>Estadísticas administrativas del restaurante</p>
            </a>
        @endif

        @if($rol === 'Maitre')
            <a href="{{ route('reservaciones.proximas') }}" class="dash-card featured">
                <span>📅</span>
                <h3>Reservaciones</h3>
                <p>Ver próximas reservas y solicitudes pendientes</p>
            </a>

            <a href="{{ route('reservaciones.asignar') }}" class="dash-card">
                <span>🪑</span>
                <h3>Asignar Mesa</h3>
                <p>Asignar mesas disponibles a reservaciones</p>
            </a>

            <a href="{{ route('reservaciones.cupo') }}" class="dash-card">
                <span>📊</span>
                <h3>Cupo del Día</h3>
                <p>Validar capacidad y ocupación del restaurante</p>
            </a>

            <a href="{{ route('menu.index') }}" class="dash-card">
                <span>📋</span>
                <h3>Menú</h3>
                <p>Consultar la carta disponible</p>
            </a>
        @endif

        @if($rol === 'Mesero')
            <a href="{{ route('pedidos.create') }}" class="dash-card featured">
                <span>📝</span>
                <h3>Registrar Pedido</h3>
                <p>Iniciar una nueva comanda para un cliente</p>
            </a>

            <a href="{{ route('pedidos.index') }}" class="dash-card">
                <span>🧾</span>
                <h3>Pedidos</h3>
                <p>Consultar comandas registradas</p>
            </a>

            <a href="{{ route('pedidos.listas') }}" class="dash-card">
                <span>✅</span>
                <h3>Registrar Entrega</h3>
                <p>Confirmar órdenes listas entregadas al cliente</p>
            </a>

            <a href="{{ route('menu.index') }}" class="dash-card">
                <span>📋</span>
                <h3>Menú</h3>
                <p>Consultar platos, precios y tiempos</p>
            </a>
        @endif

        @if($rol === 'Cocinero')
            <a href="{{ route('pedidos.cocina') }}" class="dash-card featured">
                <span>👨‍🍳</span>
                <h3>Órdenes en Cocina</h3>
                <p>Ver pedidos pendientes y actualizar su estado</p>
            </a>

            <a href="{{ route('menu.index') }}" class="dash-card">
                <span>📋</span>
                <h3>Menú</h3>
                <p>Consultar platos y tiempos de preparación</p>
            </a>
        @endif

        @if($rol === 'Cliente')
            <a href="{{ route('reservaciones.solicitar') }}" class="dash-card featured">
                <span>📅</span>
                <h3>Nueva Reservación</h3>
                <p>Solicitar una mesa en el restaurante</p>
            </a>

            <a href="{{ route('cliente.reservaciones') }}" class="dash-card">
                <span>📖</span>
                <h3>Mis Reservaciones</h3>
                <p>Consultar historial y estado de tus reservas</p>
            </a>
        @endif

    </div>
</div>

<style>
.dashboard-shell {
    max-width: 980px;
    margin: 0 auto;
}

.role-banner {
    position: relative;
    overflow: hidden;
    min-height: 112px;
    border-radius: 16px;
    padding: 28px 34px;
    display: flex;
    align-items: center;
    gap: 22px;
    color: white;
    box-shadow: 0 14px 35px rgba(0,0,80,.16);
    margin-bottom: 30px;
}

.role-banner h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.9rem;
    line-height: 1;
    margin-bottom: 6px;
}

.role-banner p {
    color: rgba(255,255,255,.82);
    margin-bottom: 4px;
    font-size: .92rem;
}

.role-banner small {
    color: rgba(255,255,255,.65);
    font-size: .78rem;
}

.role-icon {
    font-size: 2.4rem;
    z-index: 2;
}

.role-circle {
    position: absolute;
    right: -18px;
    top: -24px;
    width: 115px;
    height: 115px;
    border-radius: 50%;
    background: rgba(255,255,255,.12);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(235px, 1fr));
    gap: 18px;
}

.dash-card {
    min-height: 142px;
    background: rgba(224,247,250,.72);
    border: 1px solid rgba(0,188,212,.22);
    border-radius: 12px;
    padding: 24px 22px;
    text-decoration: none;
    color: #001B44;
    box-shadow: 0 8px 24px rgba(0,0,80,.08);
    transition: .22s ease;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.dash-card span {
    font-size: 1.9rem;
    margin-bottom: 12px;
}

.dash-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.04rem;
    margin-bottom: 8px;
    color: navy;
}

.dash-card p {
    font-size: .82rem;
    line-height: 1.45;
    color: #005577;
}

.dash-card:hover {
    transform: translateY(-5px);
    border-color: #00BCD4;
    box-shadow: 0 14px 34px rgba(0,0,80,.14);
}

.dash-card.featured {
    border-color: #00BCD4;
}
</style>
@endsection
