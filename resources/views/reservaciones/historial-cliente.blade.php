@extends('layouts.app')
@section('title', 'Mis Reservaciones')

@section('content')

{{-- Header --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.4rem; color:var(--dark);">
            📖 Mis Reservaciones
        </h2>
        <p style="font-size:.82rem; color:var(--gray); margin-top:4px;">
            Historial de todas tus reservaciones y su estado actual.
        </p>
    </div>
    <a href="{{ route('reservaciones.solicitar') }}" class="btn btn-primary">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Nueva Reservación
    </a>
</div>

{{-- Stats --}}
@php
    $pendientes  = $reservaciones->where('estado', 0)->count();
    $confirmadas = $reservaciones->whereIn('estado', [1, 2])->count();
    $total       = $reservaciones->count();
@endphp

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr); max-width:460px; margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-val">{{ $total }}</div>
        <div class="stat-label">Total</div>
    </div>
    <div class="stat-card rust">
        <div class="stat-val">{{ $pendientes }}</div>
        <div class="stat-label">Pendientes</div>
    </div>
    <div class="stat-card sage">
        <div class="stat-val">{{ $confirmadas }}</div>
        <div class="stat-label">Confirmadas</div>
    </div>
</div>

{{-- Tabla / Cards --}}
@if($reservaciones->isEmpty())

    <div class="card" style="text-align:center; padding:60px 20px; color:var(--gray);">
        <div style="font-size:3rem; margin-bottom:16px;">📅</div>
        <p style="font-size:.95rem; font-weight:500; color:var(--dark); margin-bottom:6px;">
            Aún no tienes reservaciones
        </p>
        <p style="font-size:.83rem; color:var(--gray); margin-bottom:20px;">
            ¡Haz tu primera reservación y disfruta de la experiencia!
        </p>
        <a href="{{ route('reservaciones.solicitar') }}" class="btn btn-primary" style="display:inline-flex;">
            Solicitar mesa
        </a>
    </div>

@else

    {{-- Vista escritorio: tabla --}}
    <div class="card" style="display:none;" id="tabla-wrap">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Mesa</th>
                        <th>Personas</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservaciones as $reservacion)
                    @php
                        $estado = $reservacion->estado;
                        $badge  = match($estado) {
                            0 => 'badge-pending',
                            1 => 'badge-ok',
                            2 => 'badge-assigned',
                            default => 'badge-pending',
                        };
                        $label  = match($estado) {
                            0 => 'Pendiente',
                            1 => 'Confirmada',
                            2 => 'Asignada',
                            default => 'Desconocido',
                        };
                    @endphp
                    <tr>
                        <td style="color:var(--gray); font-size:.78rem;">
                            #{{ str_pad($reservacion->id_reservacion, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            @if($reservacion->horario)
                                <span style="font-weight:500;">
                                    {{ $reservacion->horario->inicio->format('d/m/Y') }}
                                </span>
                            @else
                                <span style="color:var(--gray);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($reservacion->horario)
                                {{ $reservacion->horario->inicio->format('H:i') }}
                            @else
                                <span style="color:var(--gray);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($reservacion->horario && $reservacion->horario->mesa)
                                Mesa {{ $reservacion->horario->mesa->id_mesa }}
                                <span style="color:var(--gray); font-size:.77rem;">
                                    ({{ $reservacion->horario->mesa->sillas }} sillas)
                                </span>
                            @else
                                <span style="color:var(--gray); font-size:.82rem;">Sin asignar</span>
                            @endif
                        </td>
                        <td style="font-weight:600;">{{ $reservacion->cantidad }}</td>
                        <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Vista mobile / cards --}}
    <div id="cards-wrap" style="display:flex; flex-direction:column; gap:12px;">
        @foreach($reservaciones as $reservacion)
        @php
            $estado = $reservacion->estado;
            $badge  = match($estado) {
                0 => 'badge-pending',
                1 => 'badge-ok',
                2 => 'badge-assigned',
                default => 'badge-pending',
            };
            $label  = match($estado) {
                0 => 'Pendiente',
                1 => 'Confirmada',
                2 => 'Asignada',
                default => 'Desconocido',
            };
        @endphp
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                <div>
                    <span style="font-size:.75rem; color:var(--gray); font-weight:600;">
                        #{{ str_pad($reservacion->id_reservacion, 4, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
                <span class="badge {{ $badge }}">{{ $label }}</span>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <div style="font-size:.7rem; color:var(--gray); text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Fecha</div>
                    <div style="font-size:.88rem; font-weight:600; color:var(--dark);">
                        @if($reservacion->horario)
                            {{ $reservacion->horario->inicio->format('d/m/Y') }}
                        @else
                            <span style="color:var(--gray); font-weight:400;">—</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:.7rem; color:var(--gray); text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Hora</div>
                    <div style="font-size:.88rem; font-weight:600; color:var(--dark);">
                        @if($reservacion->horario)
                            {{ $reservacion->horario->inicio->format('H:i') }}
                        @else
                            <span style="color:var(--gray); font-weight:400;">—</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:.7rem; color:var(--gray); text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Mesa</div>
                    <div style="font-size:.88rem; font-weight:600; color:var(--dark);">
                        @if($reservacion->horario && $reservacion->horario->mesa)
                            Mesa {{ $reservacion->horario->mesa->id_mesa }}
                            <span style="font-size:.75rem; font-weight:400; color:var(--gray);">({{ $reservacion->horario->mesa->sillas }} sillas)</span>
                        @else
                            <span style="color:var(--gray); font-weight:400; font-size:.82rem;">Sin asignar</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:.7rem; color:var(--gray); text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Personas</div>
                    <div style="font-size:.88rem; font-weight:600; color:var(--dark);">
                        {{ $reservacion->cantidad }}
                    </div>
                </div>
            </div>

            {{-- Indicador de estado pendiente --}}
            @if($estado === 0)
            <div style="
                margin-top:14px;
                background:#FEF9C3;
                border:1px solid #FDE047;
                border-radius:7px;
                padding:8px 12px;
                font-size:.77rem;
                color:#854D0E;
                display:flex;
                align-items:center;
                gap:6px;
            ">
                <span>⏳</span>
                <span>El maître revisará tu solicitud y asignará una mesa pronto.</span>
            </div>
            @elseif($estado === 2)
            <div style="
                margin-top:14px;
                background:#DCFCE7;
                border:1px solid #86EFAC;
                border-radius:7px;
                padding:8px 12px;
                font-size:.77rem;
                color:#166534;
                display:flex;
                align-items:center;
                gap:6px;
            ">
                <span>✅</span>
                <span>¡Tu mesa está confirmada y asignada! Te esperamos.</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>

@endif

@push('scripts')
<script>
    // En pantallas anchas, mostrar tabla; en móvil, mostrar cards
    function toggleView() {
        const tablaWrap = document.getElementById('tabla-wrap');
        const cardsWrap = document.getElementById('cards-wrap');
        if (!tablaWrap || !cardsWrap) return;

        if (window.innerWidth >= 768) {
            tablaWrap.style.display = 'block';
            cardsWrap.style.display = 'none';
        } else {
            tablaWrap.style.display = 'none';
            cardsWrap.style.display = 'flex';
        }
    }

    toggleView();
    window.addEventListener('resize', toggleView);
</script>
@endpush

@endsection