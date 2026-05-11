<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurante — @yield('title', 'Sistema')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --cream: #EAF8FF;
    --dark: #001B44;
    --gold: #00BCD4;
    --gold-lt: #80DEEA;
    --rust: #006064;
    --sage: #00838F;
    --gray: #4A6572;
    --border: rgba(0,188,212,.25);
    --sidebar-w: 240px;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: linear-gradient(135deg, #001B44 0%, #003C8F 55%, #00BCD4 100%);
    color: var(--dark);
    min-height: 100vh;
    display: flex;
}

/* ===== SIDEBAR ===== */

.sidebar {
    width: var(--sidebar-w);
    height: 100vh;
    background: linear-gradient(180deg, #001B44 0%, #002868 100%);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    overflow: hidden;
    border-right: 1px solid rgba(255,255,255,.08);
}

.sidebar-brand {
    padding: 28px 24px 18px;
}

.sidebar-brand h1 {
    color: #80DEEA;
    font-size: 1.8rem;
    font-family: 'Playfair Display', serif;
}

.sidebar-brand small {
    color: rgba(255,255,255,.45);
    font-size: .78rem;
}

.sidebar-user {
    padding: 0 24px 20px;
}

.u-name {
    color: white;
    font-weight: 600;
}

.u-rol {
    color: rgba(255,255,255,.45);
    font-size: .8rem;
}

.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding-bottom: 20px;
}

.nav-section {
    padding: 12px 24px 6px;
    color: rgba(255,255,255,.35);
    font-size: .65rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 24px;
    color: rgba(255,255,255,.72);
    text-decoration: none;
    font-size: .86rem;
    transition: .2s;
    border-left: 3px solid transparent;
}

.nav-link svg {
    width: 16px;
    height: 16px;
}

.nav-link:hover,
.nav-link.active {
    background: rgba(0,188,212,.15);
    color: #80DEEA;
    border-left: 3px solid #00BCD4;
}

.sidebar-footer {
    padding: 18px 24px;
    border-top: 1px solid rgba(255,255,255,.08);
}

.btn-logout {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 8px;
    background: rgba(0,188,212,.15);
    color: #EAF8FF;
    cursor: pointer;
    transition: .2s;
}

.btn-logout:hover {
    background: rgba(0,188,212,.28);
}

/* ===== MAIN ===== */

.main {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: rgba(255,255,255,.88);
    backdrop-filter: blur(8px);
}

.topbar {
    background: rgba(255,255,255,.78);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(0,188,212,.18);
    padding: 14px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
}

.topbar h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem;
}

.content {
    padding: 32px;
    flex: 1;
}

/* ===== ALERTS ===== */

.alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: .9rem;
}

.alert-success {
    background: #E0F7FA;
    border: 1px solid #00BCD4;
    color: #003344;
}

.alert-error {
    background: #FFEBEE;
    border: 1px solid #E57373;
    color: #7F1D1D;
}

/* ===== CARDS ===== */

.card {
    background: rgba(255,255,255,.92);
    border: 1px solid rgba(0,188,212,.18);
    border-radius: 18px;
    padding: 24px;
    box-shadow: 0 10px 35px rgba(0,0,60,.12);
    backdrop-filter: blur(10px);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}

.card-header h3 {
    font-family: 'Playfair Display', serif;
}

/* ===== STATS ===== */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(180px,1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    background: rgba(255,255,255,.94);
    border: 1px solid rgba(0,188,212,.18);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 6px 24px rgba(0,0,80,.08);
}

.stat-card .stat-val {
    font-size: 2rem;
    font-weight: 700;
    font-family: 'Playfair Display', serif;
}

.stat-card .stat-label {
    margin-top: 6px;
    color: var(--gray);
    font-size: .82rem;
}

/* ===== TABLES ===== */

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: .88rem;
}

th {
    background: #E1F5FE;
    color: #003344;
    padding: 12px 14px;
    text-align: left;
    font-size: .74rem;
    text-transform: uppercase;
}

td {
    padding: 12px 14px;
    border-bottom: 1px solid rgba(0,188,212,.12);
}

tr:hover td {
    background: rgba(0,188,212,.06);
}

/* ===== BUTTONS ===== */

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: .85rem;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: .2s;
}

.btn-primary {
    background: linear-gradient(135deg, #00BCD4, #0097A7);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #26C6DA, #00ACC1);
    transform: translateY(-1px);
}

.btn-secondary {
    background: #EAF8FF;
    color: #003344;
    border: 1px solid rgba(0,188,212,.18);
}

.btn-secondary:hover {
    background: #D8F3FA;
}

.btn-danger {
    background: #C62828;
    color: white;
}

.btn-danger:hover {
    background: #B71C1C;
}

/* ===== FORMS ===== */

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    font-size: .84rem;
    font-weight: 600;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid rgba(0,188,212,.22);
    background: rgba(255,255,255,.92);
}

.form-control:focus {
    outline: none;
    border-color: #00BCD4;
    box-shadow: 0 0 0 3px rgba(0,188,212,.12);
}

.form-error {
    color: #C62828;
    font-size: .78rem;
    margin-top: 4px;
}

/* ===== BADGES ===== */

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 600;
}

.badge-pending {
    background: #FFF3CD;
    color: #856404;
}

.badge-ok,
.badge-done {
    background: #D1FAE5;
    color: #065F46;
}

.badge-progress {
    background: #DBEAFE;
    color: #1D4ED8;
}
</style>
   
    @stack('styles')
</head>
<body>

{{-- ========== SIDEBAR ========== --}}
@php $rol = session('rol', ''); @endphp
<aside class="sidebar">
    <div class="sidebar-brand">
        <h1>La Mesa</h1>
        <small>Sistema de Gestión</small>
    </div>
    <div class="sidebar-user">
        <div class="u-name">{{ session('usuario_nombre', 'Usuario') }}</div>
        <div class="u-rol">{{ $rol }}</div>
    </div>
    <nav class="sidebar-nav">

        <div class="nav-section">General</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Dashboard
        </a>

        @if(in_array($rol, ['Administrador','Maitre']))
        <div class="nav-section">Reservaciones</div>
        <a href="{{ route('reservaciones.proximas') }}" class="nav-link {{ request()->routeIs('reservaciones.proximas') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Reservaciones
        </a>
        <a href="{{ route('reservaciones.asignar') }}" class="nav-link {{ request()->routeIs('reservaciones.asignar') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            Asignar Mesa
        </a>
        <a href="{{ route('reservaciones.cupo') }}" class="nav-link {{ request()->routeIs('reservaciones.cupo') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Cupo del Día
        </a>
        @endif

        @if($rol === 'Cliente')
        <div class="nav-section">Mis Reservas</div>
        <a href="{{ route('reservaciones.solicitar') }}" class="nav-link">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nueva Reservación
        </a>
        <a href="{{ route('cliente.reservaciones') }}" class="nav-link {{ request()->routeIs('cliente.reservaciones') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Mis Reservaciones
        </a>
        @endif

        @if(in_array($rol, ['Mesero','Administrador']))
        <div class="nav-section">Pedidos</div>
        <a href="{{ route('pedidos.index') }}" class="nav-link {{ request()->routeIs('pedidos.index') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            Pedidos
        </a>
        <a href="{{ route('pedidos.create') }}" class="nav-link">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Pedido
        </a>
        <a href="{{ route('pedidos.listas') }}" class="nav-link {{ request()->routeIs('pedidos.listas') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Listas para entregar
        </a>
        @endif

        @if(in_array($rol, ['Cocinero','Administrador']))
        <div class="nav-section">Cocina</div>
        <a href="{{ route('pedidos.cocina') }}" class="nav-link {{ request()->routeIs('pedidos.cocina') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
            Órdenes en Cocina
        </a>
        @endif

        @if(in_array($rol, ['Administrador','Maitre','Mesero','Cocinero']))
        <div class="nav-section">Menú</div>
        <a href="{{ route('menu.index') }}" class="nav-link {{ request()->routeIs('menu.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20 10 10 0 000-20z"/><path d="M12 8v4l3 3"/></svg>
            Ver Menú
        </a>
        @endif

        @if($rol === 'Administrador')
        <div class="nav-section">Administración</div>
        <a href="{{ route('mesas.index') }}" class="nav-link {{ request()->routeIs('mesas.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Mesas
        </a>
        <a href="{{ route('empleados.index') }}" class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            Empleados
        </a>
        <a href="{{ route('reportes.index') }}" class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Reportes
        </a>
        <a href="{{ route('configuracion.edit') }}" class="nav-link {{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            Capacidad del Local
        </a>
        @endif

    </nav>
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Cerrar sesión</button>
        </form>
    </div>
</aside>

{{-- ========== MAIN ========== --}}
<div class="main">
    <header class="topbar">
        <h2>@yield('title', 'Dashboard')</h2>
        <span style="font-size:.8rem; color:var(--gray);">{{ now()->format('d M Y') }}</span>
    </header>
    <div class="content">

        {{-- Notificaciones --}}
@if(session('success'))
    <div class="alert alert-success" id="alert-success">
        ✔ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" id="alert-error">
        ✖ {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error" id="alert-validation">
        <strong>Error:</strong>
        @foreach($errors->all() as $e)
            <div>• {{ $e }}</div>
        @endforeach
    </div>
@endif

<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 4000);
</script>

        @yield('content')
    </div>
</div>

@stack('scripts')
</body>
</html>
