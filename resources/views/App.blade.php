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
            --cream:    #FAF7F2;
            --dark:     #1A1A18;
            --gold:     #C9A84C;
            --gold-lt:  #E8D5A3;
            --rust:     #8B3A2A;
            --sage:     #5C7A5A;
            --gray:     #6B6B68;
            --border:   #E2DDD4;
            --sidebar-w: 240px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--dark); min-height: 100vh; display: flex; }

        /* ---- Sidebar ---- */
        .sidebar {
            width: var(--sidebar-w); min-height: 100vh; background: var(--dark);
            display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 100;
        }
        .sidebar-brand {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .sidebar-brand h1 {
            font-family: 'Playfair Display', serif; color: var(--gold);
            font-size: 1.25rem; font-weight: 700; letter-spacing: .5px;
        }
        .sidebar-brand small { color: rgba(255,255,255,.4); font-size: .7rem; display: block; margin-top: 2px; }
        .sidebar-user {
            padding: 16px 24px; border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-user .u-name { color: #fff; font-size: .85rem; font-weight: 500; }
        .sidebar-user .u-rol  { color: var(--gold-lt); font-size: .72rem; opacity: .8; }
        .sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
        .nav-section { padding: 8px 24px 4px; color: rgba(255,255,255,.3); font-size: .65rem; text-transform: uppercase; letter-spacing: 1px; }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 24px; color: rgba(255,255,255,.65); text-decoration: none;
            font-size: .85rem; transition: all .15s;
        }
        .nav-link:hover, .nav-link.active { background: rgba(201,168,76,.12); color: var(--gold); }
        .nav-link svg { width: 16px; height: 16px; flex-shrink: 0; }
        .sidebar-footer {
            padding: 16px 24px; border-top: 1px solid rgba(255,255,255,.08);
        }
        .btn-logout {
            width: 100%; padding: 9px 16px; background: rgba(139,58,42,.3); color: #fff;
            border: 1px solid rgba(139,58,42,.5); border-radius: 6px; cursor: pointer;
            font-family: inherit; font-size: .82rem; text-align: center; display: block;
            text-decoration: none; transition: background .15s;
        }
        .btn-logout:hover { background: rgba(139,58,42,.6); }

        /* ---- Main ---- */
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar {
            background: #fff; border-bottom: 1px solid var(--border);
            padding: 14px 32px; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar h2 { font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--dark); }
        .content { padding: 32px; flex: 1; }

        /* ---- Alerts ---- */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: .875rem; }
        .alert-success { background: #EDFAF0; border: 1px solid #6DC97A; color: #1A6130; }
        .alert-error   { background: #FEF2F2; border: 1px solid #FCA5A5; color: #7F1D1D; }
        .alert-info    { background: #EFF6FF; border: 1px solid #93C5FD; color: #1E40AF; }

        /* ---- Cards ---- */
        .card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 24px; }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .card-header h3 { font-family: 'Playfair Display', serif; font-size: 1.05rem; }

        /* ---- Stat cards ---- */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card { background: #fff; border: 1px solid var(--border); border-radius: 10px; padding: 20px; }
        .stat-card .stat-val { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: var(--dark); }
        .stat-card .stat-label { font-size: .78rem; color: var(--gray); margin-top: 4px; }
        .stat-card.gold  { border-left: 4px solid var(--gold); }
        .stat-card.sage  { border-left: 4px solid var(--sage); }
        .stat-card.rust  { border-left: 4px solid var(--rust); }

        /* ---- Tables ---- */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        th { background: var(--cream); padding: 10px 14px; text-align: left; font-weight: 600; font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: var(--gray); border-bottom: 2px solid var(--border); }
        td { padding: 10px 14px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(201,168,76,.04); }

        /* ---- Buttons ---- */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 7px; font-family: inherit; font-size: .845rem; font-weight: 500; cursor: pointer; text-decoration: none; border: none; transition: all .15s; }
        .btn-primary   { background: var(--gold); color: var(--dark); }
        .btn-primary:hover { background: #B8942E; }
        .btn-secondary { background: var(--cream); color: var(--dark); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--border); }
        .btn-danger    { background: var(--rust); color: #fff; }
        .btn-danger:hover { background: #6E2D20; }
        .btn-sage      { background: var(--sage); color: #fff; }
        .btn-sage:hover { background: #3E5E3C; }
        .btn-sm { padding: 5px 12px; font-size: .8rem; }

        /* ---- Forms ---- */
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: .825rem; font-weight: 500; margin-bottom: 6px; color: var(--dark); }
        .form-control {
            width: 100%; padding: 9px 12px; border: 1px solid var(--border); border-radius: 7px;
            font-family: inherit; font-size: .875rem; background: #fff; color: var(--dark);
            transition: border-color .15s;
        }
        .form-control:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,.15); }
        .form-error { color: var(--rust); font-size: .78rem; margin-top: 4px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

        /* ---- Badge ---- */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600; }
        .badge-pending  { background: #FEF9C3; color: #854D0E; }
        .badge-ok       { background: #DCFCE7; color: #166534; }
        .badge-assigned { background: #DBEAFE; color: #1E40AF; }
        .badge-progress { background: #FEF3C7; color: #92400E; }
        .badge-done     { background: #D1FAE5; color: #064E3B; }
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

        {{-- Mensajes flash --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </div>
</div>

@stack('scripts')
</body>
</html>