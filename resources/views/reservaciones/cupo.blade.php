{{--
    @brief Vista de validación de cupo del restaurante con alertas de capacidad.

    Muestra el estado de ocupación del restaurante en tiempo real, incluyendo
    el límite configurado por el Administrador y alertas visuales cuando la
    ocupación se acerca al límite establecido.

    @param int   $cupoTotal       Límite efectivo de sillas (manual o automático).
    @param int   $numMesas        Total de mesas registradas.
    @param int   $ocupadas        Mesas ocupadas hoy.
    @param int   $libres          Mesas libres hoy.
    @param int   $porcentaje      Porcentaje de mesas ocupadas.
    @param Collection $reservasHoy Horarios de hoy con reservaciones y clientes.
    @param array $alerta          Datos de alerta (nivel, porcentaje, restantes).
    @param int   $sillasOcupadas  Sillas ocupadas en la ventana actual de 2h.
    @param ConfiguracionRestaurante $config  Configuración actual del restaurante.
--}}

@extends('layouts.app')
@section('title', 'Cupo del Restaurante')

@section('content')

{{-- ========== ALERTA DE PROXIMIDAD AL LÍMITE ========== --}}
{{-- Si la ocupación en sillas supera el umbral configurado por el Administrador,
     mostramos una alerta visual prominente para que el Maitre tome acción --}}
@if($alerta['alerta'])
    <div class="capacity-alert {{ $alerta['nivel'] === 'critico' ? 'capacity-alert--critical' : 'capacity-alert--warning' }}"
         id="cupo-alert-banner">
        <div class="capacity-alert__icon">
            {{ $alerta['nivel'] === 'critico' ? '🚨' : '⚠️' }}
        </div>
        <div class="capacity-alert__content">
            <strong>
                {{ $alerta['nivel'] === 'critico'
                    ? '¡CAPACIDAD MÁXIMA ALCANZADA!'
                    : '¡Atención! Ocupación cercana al límite' }}
            </strong>
            <p>
                Se están utilizando <strong>{{ $sillasOcupadas }}</strong> de
                <strong>{{ $alerta['limite'] }}</strong> sillas permitidas
                ({{ $alerta['porcentaje'] }}%).
                @if($alerta['restantes'] > 0)
                    Quedan <strong>{{ $alerta['restantes'] }}</strong> sillas disponibles.
                @else
                    No quedan sillas disponibles en este momento.
                @endif
            </p>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div>
            <h3>Validación de Cupo</h3>
            <p style="color:var(--gray); font-size:.85rem;">
                Estado de ocupación del restaurante para el día actual.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Volver</a>
    </div>

    {{-- ========== TARJETAS DE ESTADÍSTICAS ========== --}}
    {{-- Mostramos el cupo total (límite configurado), las mesas totales,
         mesas ocupadas/libres y las sillas ocupadas actualmente --}}
    <div class="stats-grid" style="margin-bottom:28px;">
        <div class="stat-card">
            <div class="stat-val">{{ $cupoTotal }}</div>
            <div class="stat-label">🎯 Cupo total (límite)</div>
        </div>

        <div class="stat-card gold">
            <div class="stat-val">{{ $numMesas }}</div>
            <div class="stat-label">Mesas</div>
        </div>

        <div class="stat-card rust">
            <div class="stat-val">{{ $ocupadas }}</div>
            <div class="stat-label">Mesas ocupadas</div>
        </div>

        <div class="stat-card sage">
            <div class="stat-val">{{ $libres }}</div>
            <div class="stat-label">Mesas libres</div>
        </div>
    </div>

    {{-- ========== BARRA DE OCUPACIÓN POR MESAS ========== --}}
    <div style="margin-bottom:20px;">
        <label style="font-weight:600;">Ocupación de mesas</label>
        <div style="background:#eee; border-radius:999px; overflow:hidden; height:18px; margin-top:8px;">
            <div style="width:{{ $porcentaje }}%; background:var(--rust); height:18px;"></div>
        </div>
        <p style="font-size:.85rem; color:var(--gray); margin-top:6px;">
            {{ $porcentaje }}% de ocupación de mesas.
        </p>
    </div>

    {{-- ========== BARRA DE OCUPACIÓN POR SILLAS (LÍMITE CONFIGURADO) ========== --}}
    {{-- Esta barra muestra la ocupación de sillas respecto al límite configurado
         por el Administrador, con colores que cambian según el nivel de alerta --}}
    <div style="margin-bottom:28px;">
        <label style="font-weight:600;">📊 Ocupación de sillas vs Límite configurado</label>
        <div style="background:#e8f4f8; border-radius:999px; overflow:hidden; height:22px; margin-top:8px; position:relative;">
            <div style="
                width:{{ min($alerta['porcentaje'], 100) }}%;
                height:22px;
                border-radius:999px;
                background: {{ $alerta['nivel'] === 'critico' ? 'linear-gradient(90deg, #C62828, #E53935)' : ($alerta['nivel'] === 'advertencia' ? 'linear-gradient(90deg, #E65100, #FF9800)' : 'linear-gradient(90deg, #00897B, #26A69A)') }};
                transition: width 0.6s ease;
            "></div>
            {{-- Línea indicadora del umbral de alerta --}}
            <div style="
                position:absolute;
                left:{{ $config->umbral_alerta }}%;
                top:0;
                height:22px;
                width:2px;
                background:rgba(0,0,0,.35);
            " title="Umbral de alerta ({{ $config->umbral_alerta }}%)"></div>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top:6px;">
            <span style="font-size:.82rem; color:var(--gray);">
                {{ $sillasOcupadas }} / {{ $cupoTotal }} sillas · {{ $alerta['porcentaje'] }}% ocupado
            </span>
            <span style="font-size:.78rem; color:var(--gray);">
                Umbral alerta: {{ $config->umbral_alerta }}% ·
                Configuración:
                @if($config->max_sillas > 0)
                    <strong>Manual ({{ $config->max_sillas }})</strong>
                @else
                    <strong>Automática (por mesas)</strong>
                @endif
            </span>
        </div>
    </div>

    <h4 style="margin-bottom:12px;">Reservaciones de hoy</h4>

    @if($reservasHoy->isEmpty())
        <p style="color:var(--gray);">No hay reservaciones asignadas para hoy.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Cliente</th>
                        <th>Mesa</th>
                        <th>Personas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservasHoy as $horario)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($horario->inicio)->format('H:i') }}</td>
                            <td>{{ $horario->reservacion->cliente->nombre ?? '—' }}</td>
                            <td>Mesa {{ $horario->mesa->id_mesa ?? '—' }}</td>
                            <td>{{ $horario->reservacion->cantidad ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ========== ESTILOS DE ALERTAS ========== --}}
@push('styles')
<style>
/* Alerta visual de proximidad al límite de capacidad del restaurante */
.capacity-alert {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 18px 22px;
    border-radius: 14px;
    margin-bottom: 24px;
    animation: alertPulse 2s ease-in-out infinite;
}

/* Alerta nivel advertencia: fondo ámbar para indicar precaución al Maitre */
.capacity-alert--warning {
    background: linear-gradient(135deg, #FFF3E0, #FFE0B2);
    border: 1px solid #FFB74D;
    color: #E65100;
}

/* Alerta nivel crítico: fondo rojo para indicar urgencia máxima */
.capacity-alert--critical {
    background: linear-gradient(135deg, #FFEBEE, #FFCDD2);
    border: 1px solid #E57373;
    color: #B71C1C;
}

.capacity-alert__icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.capacity-alert__content strong {
    font-size: .95rem;
    display: block;
    margin-bottom: 4px;
}

.capacity-alert__content p {
    font-size: .84rem;
    line-height: 1.5;
    margin: 0;
}

/* Animación suave para captar la atención del Maitre sobre la alerta */
@keyframes alertPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .85; }
}
</style>
@endpush

@endsection
