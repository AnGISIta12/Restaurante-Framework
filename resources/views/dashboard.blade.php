@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php 
    $rol = session('rol'); 
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

    {{-- MAITRE / ADMIN --}}
    @if(in_array($rol, ['Administrador','Maitre']))
    <a href="{{ route('reservaciones.proximas') }}" style="text-decoration:none;">
        <div class="card">
            📅
            <h3>Reservaciones</h3>
            <p>Ver próximas reservas</p>
        </div>
    </a>

    <a href="{{ route('reservaciones.asignar') }}" style="text-decoration:none;">
        <div class="card">
            🪑
            <h3>Asignar Mesa</h3>
            <p>Gestionar disponibilidad</p>
        </div>
    </a>
    @endif


    {{-- ADMIN --}}
    @if($rol === 'Administrador')
    <a href="{{ route('mesas.index') }}">
        <div class="card">
            🍽
            <h3>Mesas</h3>
            <p>Administrar mesas</p>
        </div>
    </a>

    <a href="{{ route('empleados.index') }}">
        <div class="card">
            👥
            <h3>Empleados</h3>
            <p>Gestión del personal</p>
        </div>
    </a>

    <a href="{{ route('reportes.index') }}">
        <div class="card">
            📊
            <h3>Reportes</h3>
            <p>Ver estadísticas</p>
        </div>
    </a>
    @endif


    {{-- MESERO --}}
    @if(in_array($rol, ['Mesero','Administrador']))
    <a href="{{ route('pedidos.create') }}">
        <div class="card">
            📝
            <h3>Nuevo Pedido</h3>
            <p>Registrar comanda</p>
        </div>
    </a>

    <a href="{{ route('pedidos.listas') }}">
        <div class="card">
            ✅
            <h3>Entregar</h3>
            <p>Pedidos listos</p>
        </div>
    </a>
    @endif


    {{-- COCINERO --}}
    @if(in_array($rol, ['Cocinero','Administrador']))
    <a href="{{ route('pedidos.cocina') }}">
        <div class="card">
            👨‍🍳
            <h3>Cocina</h3>
            <p>Órdenes en preparación</p>
        </div>
    </a>
    @endif


    {{-- TODOS --}}
    @if(in_array($rol, ['Administrador','Maitre','Mesero','Cocinero']))
    <a href="{{ route('menu.index') }}">
        <div class="card">
            📋
            <h3>Menú</h3>
            <p>Ver platos</p>
        </div>
    </a>
    @endif


    {{-- CLIENTE --}}
    @if($rol === 'Cliente')
    <a href="{{ route('reservaciones.solicitar') }}">
        <div class="card">
            📅
            <h3>Reservar</h3>
            <p>Solicitar mesa</p>
        </div>
    </a>

    <a href="{{ route('cliente.reservaciones') }}">
        <div class="card">
            📖
            <h3>Mis Reservas</h3>
            <p>Historial</p>
        </div>
    </a>
    @endif

</div>


{{-- ESTILO RÁPIDO --}}
<style>
.card {
    text-align:center;
    padding:25px 15px;
    border-radius:12px;
    background:#fff;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    transition:all .2s ease;
    cursor:pointer;
    font-size:1.8rem;
}

.card h3 {
    font-size:1rem;
    margin-top:10px;
    font-weight:600;
}

.card p {
    font-size:.8rem;
    color:#777;
    margin-top:4px;
}

.card:hover {
    transform:translateY(-4px);
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
}
</style>

@endsection
