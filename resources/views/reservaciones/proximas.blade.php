@extends('layouts.app')

@section('title', 'Próximas Reservaciones')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.4rem; color:var(--dark);">
            📅 Próximas Reservaciones
        </h2>
        <p style="font-size:.82rem; color:var(--gray); margin-top:4px;">
            Reservaciones próximas y pendientes de asignar.
        </p>
    </div>
    <a href="{{ route('reservaciones.create') }}" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px;">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Nueva Reservación
    </a>
</div>

{{-- ── Alerta: reservaciones que empiezan pronto ── --}}
@if($porEmpezar->isNotEmpty())
    <div class="alert alert-info" style="margin-bottom:20px;">
        ⏰ <strong>{{ $porEmpezar->count() }} reservación(es)</strong> empiezan en los próximos 30 minutos:
        @foreach($porEmpezar as $h)
            <span style="font-weight:600;">
                Mesa {{ $h->mesa->id_mesa ?? '?' }}
                ({{ $h->reservacion->cliente->nombre ?? '?' }},
                {{ $h->inicio->format('H:i') }})
            </span>{{ !$loop->last ? '·' : '' }}
        @endforeach
    </div>
@endif

{{-- ── Stats ── --}}
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr); max-width:460px; margin-bottom:24px;">
    <div class="stat-card gold">
        <div class="stat-val">{{ $reservaciones->count() }}</div>
        <div class="stat-label">Total</div>
    </div>
    <div class="stat-card rust">
        <div class="stat-val">
            {{ $reservaciones->where('estado', \App\Models\Reservacion::ESTADO_PENDIENTE)->count() }}
        </div>
        <div class="stat-label">Pendientes</div>
    </div>
    <div class="stat-card sage">
        <div class="stat-val">
            {{ $reservaciones->where('estado', \App\Models\Reservacion::ESTADO_ASIGNADA)->count() }}
        </div>
        <div class="stat-label">Asignadas</div>
    </div>
</div>

{{-- ── Tabla ── --}}
@if($reservaciones->isEmpty())
    <div class="card" style="text-align:center; padding:50px; color:var(--gray);">
        <p style="font-size:2rem; margin-bottom:10px;">📅</p>
        <p>No hay reservaciones próximas registradas.</p>
        <a href="{{ route('reservaciones.create') }}" class="btn btn-primary" style="margin-top:16px;">
            Crear primera reservación
        </a>
    </div>
@else
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Personas</th>
                        <th>Mesa</th>
                        <th>Inicio</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservaciones as $reservacion)
                    @php
                        $estadoBadge = match($reservacion->estado) {
                            \App\Models\Reservacion::ESTADO_PENDIENTE  => 'badge-pending',
                            \App\Models\Reservacion::ESTADO_CONFIRMADA => 'badge-progress',
                            \App\Models\Reservacion::ESTADO_ASIGNADA   => 'badge-ok',
                            default => 'badge-pending',
                        };
                    @endphp
                    <tr>
                        <td style="color:var(--gray); font-size:.8rem;">
                            #{{ str_pad($reservacion->id_reservacion, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td style="font-weight:500;">
                            {{ $reservacion->cliente->nombre ?? '—' }}
                        </td>
                        <td>
                            <span style="font-weight:600;">{{ $reservacion->cantidad }}</span>
                            <span style="color:var(--gray); font-size:.8rem;">
                                {{ $reservacion->cantidad == 1 ? 'persona' : 'personas' }}
                            </span>
                        </td>
                        <td>
                            @if($reservacion->horario && $reservacion->horario->mesa)
                                Mesa {{ $reservacion->horario->mesa->id_mesa }}
                                <span style="color:var(--gray); font-size:.78rem;">
                                    ({{ $reservacion->horario->mesa->sillas }} sillas)
                                </span>
                            @else
                                <span style="color:var(--gray);">Sin asignar</span>
                            @endif
                        </td>
                        <td>
                            @if($reservacion->horario)
                                {{ $reservacion->horario->inicio->format('d/m H:i') }}
                            @else
                                <span style="color:var(--gray);">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $estadoBadge }}">
                                {{ $reservacion->getEtiquetaEstado() }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if($reservacion->estado !== \App\Models\Reservacion::ESTADO_ASIGNADA)
                                <a href="{{ route('reservaciones.asignar') }}"
                                   class="btn btn-sage btn-sm">
                                    🗺️ Asignar
                                </a>
                            @else
                                <span style="font-size:.78rem; color:var(--gray);">✓ Listo</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
