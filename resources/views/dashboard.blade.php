@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php $rol = session('rol'); $nombre = session('usuario_nombre'); @endphp

<div style="margin-bottom:28px;">
    <h2 style="font-family:'Playfair Display',serif; font-size:1.5rem;">
        Bienvenido, {{ $nombre }}
    </h2>
    <p style="color:var(--gray); margin-top:4px; font-size:.9rem;">
        Rol actual: <strong>{{ $rol }}</strong> — {{ now()->format('l, d \d\e F \d\e Y') }}
    </p>
</div>

{{-- Tarjetas de acceso rápido según rol --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:16px;">

    @if(in_array($rol, ['Administrador','Maitre']))
    <a href="{{ route('reservaciones.proximas') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">📅</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Reservaciones</div>
            <div style="font-size:.78rem; color:var(--gray);">Ver próximas y gestionar</div>
        </div>
    </a>
    <a href="{{ route('reservaciones.asignar') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">🪑</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Asignar Mesa</div>
            <div style="font-size:.78rem; color:var(--gray);">Asignar mesas a reservas</div>
        </div>
    </a>
    @endif

    @if($rol === 'Administrador')
    <a href="{{ route('mesas.index') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">🍽️</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Mesas</div>
            <div style="font-size:.78rem; color:var(--gray);">Gestionar mesas del local</div>
        </div>
    </a>
    <a href="{{ route('empleados.index') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">👥</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Empleados</div>
            <div style="font-size:.78rem; color:var(--gray);">Gestionar personal</div>
        </div>
    </a>
    <a href="{{ route('reportes.index') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">📊</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Reportes</div>
            <div style="font-size:.78rem; color:var(--gray);">Estadísticas y datos</div>
        </div>
    </a>
    @endif

    @if(in_array($rol, ['Mesero','Administrador']))
    <a href="{{ route('pedidos.create') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">🧾</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Nuevo Pedido</div>
            <div style="font-size:.78rem; color:var(--gray);">Registrar comanda</div>
        </div>
    </a>
    <a href="{{ route('pedidos.listas') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">✅</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Entregar</div>
            <div style="font-size:.78rem; color:var(--gray);">Órdenes listas</div>
        </div>
    </a>
    @endif

    @if(in_array($rol, ['Cocinero','Administrador']))
    <a href="{{ route('pedidos.cocina') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">👨‍🍳</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Cocina</div>
            <div style="font-size:.78rem; color:var(--gray);">Órdenes pendientes</div>
        </div>
    </a>
    @endif

    @if(in_array($rol, ['Administrador','Maitre','Mesero','Cocinero']))
    <a href="{{ route('menu.index') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">📋</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Menú</div>
            <div style="font-size:.78rem; color:var(--gray);">Ver platos y precios</div>
        </div>
    </a>
    @endif

    @if($rol === 'Cliente')
    <a href="{{ route('reservaciones.solicitar') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">📅</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Reservar</div>
            <div style="font-size:.78rem; color:var(--gray);">Solicitar una mesa</div>
        </div>
    </a>
    <a href="{{ route('cliente.reservaciones') }}" style="text-decoration:none;">
        <div class="card" style="text-align:center; padding:28px 20px; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem; margin-bottom:12px;">🗒️</div>
            <div style="font-family:'Playfair Display',serif; font-weight:600; margin-bottom:4px;">Mis Reservas</div>
            <div style="font-size:.78rem; color:var(--gray);">Ver historial</div>
        </div>
    </a>
    @endif
</div>
@endsection