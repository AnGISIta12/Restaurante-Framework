@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $rol    = session('rol');
    $nombre = session('usuario_nombre');
@endphp

<div style="margin-bottom:30px;">
    <h2 style="font-family:'Playfair Display',serif; font-size:1.6rem;">
        👋 Bienvenido, {{ $nombre }}
    </h2>
    <p style="color:var(--gray); margin-top:6px; font-size:.9rem;">
        Rol: <strong>{{ $rol }}</strong> — {{ now()->format('d/m/Y') }}
    </p>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:18px;">

    {{-- ── ADMINISTRADOR ── --}}
    {{-- Gestiona el local: mesas, personal, menú y reportes --}}
    @if($rol === 'Administrador')

        <a href="{{ route('mesas.index') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                🍽
                <h3>Mesas</h3>
                <p>Administrar mesas del local</p>
            </div>
        </a>

        <a href="{{ route('empleados.index') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                👥
                <h3>Empleados</h3>
                <p>Gestión del personal</p>
            </div>
        </a>

        <a href="{{ route('menu.index') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                📋
                <h3>Menú</h3>
                <p>Gestionar la carta</p>
            </div>
        </a>

        <a href="{{ route('reportes.index') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                📊
                <h3>Reportes</h3>
                <p>Estadísticas del restaurante</p>
            </div>
        </a>

    @endif

    {{-- ── MAITRE ── --}}
    {{-- Gestiona reservaciones y asignación de mesas --}}
    @if($rol === 'Maitre')

        <a href="{{ route('reservaciones.proximas') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                📅
                <h3>Reservaciones</h3>
                <p>Ver próximas reservas</p>
            </div>
        </a>

        <a href="{{ route('reservaciones.asignar') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                🪑
                <h3>Asignar Mesa</h3>
                <p>Gestionar disponibilidad</p>
            </div>
        </a>

        <a href="{{ route('reservaciones.cupo') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                📊
                <h3>Cupo del Día</h3>
                <p>Validar capacidad</p>
            </div>
        </a>

        <a href="{{ route('menu.index') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                📋
                <h3>Menú</h3>
                <p>Ver y editar la carta</p>
            </div>
        </a>

    @endif

    {{-- ── MESERO ── --}}
    {{-- Toma pedidos y entrega órdenes --}}
    @if($rol === 'Mesero')

        <a href="{{ route('pedidos.create') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                📝
                <h3>Nuevo Pedido</h3>
                <p>Registrar comanda</p>
            </div>
        </a>

        <a href="{{ route('pedidos.index') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                🧾
                <h3>Pedidos</h3>
                <p>Ver todas las comandas</p>
            </div>
        </a>

        <a href="{{ route('pedidos.listas') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                ✅
                <h3>Entregar</h3>
                <p>Órdenes listas para entregar</p>
            </div>
        </a>

        <a href="{{ route('menu.index') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                📋
                <h3>Menú</h3>
                <p>Consultar la carta</p>
            </div>
        </a>

    @endif

    {{-- ── COCINERO ── --}}
    {{-- Prepara y gestiona órdenes en cocina --}}
    @if($rol === 'Cocinero')

        <a href="{{ route('pedidos.cocina') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                👨‍🍳
                <h3>Cocina</h3>
                <p>Órdenes en preparación</p>
            </div>
        </a>

        <a href="{{ route('menu.index') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                📋
                <h3>Menú</h3>
                <p>Consultar platos y tiempos</p>
            </div>
        </a>

    @endif

    {{-- ── CLIENTE ── --}}
    @if($rol === 'Cliente')

        <a href="{{ route('reservaciones.solicitar') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                📅
                <h3>Reservar</h3>
                <p>Solicitar una mesa</p>
            </div>
        </a>

        <a href="{{ route('cliente.reservaciones') }}" style="text-decoration:none;">
            <div class="card-dashboard">
                📖
                <h3>Mis Reservas</h3>
                <p>Ver historial</p>
            </div>
        </a>

    @endif

</div>

<style>
.card-dashboard {
    text-align: center;
    padding: 25px 15px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all .2s ease;
    cursor: pointer;
    font-size: 1.8rem;
    border: 1px solid var(--border);
}
.card-dashboard h3 {
    font-size: 1rem;
    margin-top: 10px;
    font-weight: 600;
    color: var(--dark);
}
.card-dashboard p {
    font-size: .8rem;
    color: #777;
    margin-top: 4px;
}
.card-dashboard:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border-color: var(--gold);
}
</style>

@endsection