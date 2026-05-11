{{--
    @brief Vista de configuración de capacidad del restaurante.

    Permite al Administrador definir el límite máximo de sillas que el
    restaurante puede soportar y el porcentaje de umbral de alerta.
    Muestra estadísticas en tiempo real y alertas visuales cuando la
    ocupación se acerca al límite configurado.

    @param ConfiguracionRestaurante $config         Configuración actual.
    @param int                      $sillasActuales Suma de sillas de todas las mesas.
    @param int                      $numMesas       Total de mesas registradas.
    @param int                      $ocupados       Asientos ocupados ahora (ventana 2h).
    @param array                    $alerta         Datos de alerta (nivel, porcentaje, etc).
--}}

@extends('layouts.app')
@section('title', 'Capacidad del Local')

@section('content')

{{-- ========== ALERTA DE PROXIMIDAD AL LÍMITE ========== --}}
{{-- Si la ocupación actual supera el umbral configurado, mostramos una alerta
     visual prominente para que el Administrador tome acción inmediata --}}
@if($alerta['alerta'])
    <div class="capacity-alert {{ $alerta['nivel'] === 'critico' ? 'capacity-alert--critical' : 'capacity-alert--warning' }}"
         id="capacity-alert-banner">
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
                Se están utilizando <strong>{{ $ocupados }}</strong> de
                <strong>{{ $alerta['limite'] }}</strong> sillas permitidas
                ({{ $alerta['porcentaje'] }}%).
                @if($alerta['restantes'] > 0)
                    Quedan <strong>{{ $alerta['restantes'] }}</strong> sillas disponibles.
                @else
                    No quedan sillas disponibles.
                @endif
            </p>
        </div>
    </div>
@endif

{{-- ========== ENCABEZADO ========== --}}
<div class="card" style="margin-bottom:24px;">
    <div class="card-header" style="margin-bottom:0;">
        <div>
            <h3>⚙️ Configuración de Capacidad del Local</h3>
            <p style="color:var(--gray); font-size:.85rem; margin-top:4px;">
                Define el límite máximo de sillas y el umbral de alerta para el restaurante.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Volver al Dashboard</a>
    </div>
</div>

{{-- ========== ESTADÍSTICAS ACTUALES ========== --}}
{{-- Mostramos tarjetas informativas con el estado actual del restaurante para
     que el admin pueda tomar decisiones informadas al configurar el límite --}}
<div class="stats-grid" style="margin-bottom:28px;">
    <div class="stat-card">
        <div class="stat-val">{{ $config->limiteEfectivo() }}</div>
        <div class="stat-label">🎯 Límite efectivo</div>
    </div>

    <div class="stat-card gold">
        <div class="stat-val">{{ $sillasActuales }}</div>
        <div class="stat-label">🪑 Sillas por mesas</div>
    </div>

    <div class="stat-card sage">
        <div class="stat-val">{{ $numMesas }}</div>
        <div class="stat-label">🏷️ Mesas registradas</div>
    </div>

    <div class="stat-card {{ $alerta['nivel'] === 'critico' ? 'rust' : ($alerta['nivel'] === 'advertencia' ? 'rust' : 'sage') }}">
        <div class="stat-val">{{ $ocupados }}</div>
        <div class="stat-label">👥 Ocupados ahora</div>
    </div>
</div>

{{-- ========== BARRA DE OCUPACIÓN ========== --}}
{{-- Barra de progreso visual que muestra la ocupación actual respecto al límite
     configurado, cambiando de color según el nivel de alerta para dar feedback
     visual inmediato al administrador --}}
<div class="card" style="margin-bottom:24px;">
    <label style="font-weight:600; font-size:.92rem;">📊 Ocupación actual vs Límite configurado</label>
    <div style="background:#e8f4f8; border-radius:999px; overflow:hidden; height:24px; margin-top:12px; position:relative;">
        <div style="
            width:{{ min($alerta['porcentaje'], 100) }}%;
            height:24px;
            border-radius:999px;
            background: {{ $alerta['nivel'] === 'critico' ? 'linear-gradient(90deg, #C62828, #E53935)' : ($alerta['nivel'] === 'advertencia' ? 'linear-gradient(90deg, #E65100, #FF9800)' : 'linear-gradient(90deg, #00897B, #26A69A)') }};
            transition: width 0.6s ease;
        "></div>
        {{-- Línea indicadora del umbral de alerta en la barra de progreso --}}
        <div style="
            position:absolute;
            left:{{ $config->umbral_alerta }}%;
            top:0;
            height:24px;
            width:2px;
            background:rgba(0,0,0,.4);
        " title="Umbral de alerta ({{ $config->umbral_alerta }}%)"></div>
    </div>
    <div style="display:flex; justify-content:space-between; margin-top:8px;">
        <span style="font-size:.82rem; color:var(--gray);">
            {{ $alerta['porcentaje'] }}% ocupado · {{ $alerta['restantes'] }} sillas disponibles
        </span>
        <span style="font-size:.78rem; color:var(--gray);">
            Umbral: {{ $config->umbral_alerta }}%
        </span>
    </div>
</div>

{{-- ========== FORMULARIO DE CONFIGURACIÓN ========== --}}
{{-- Formulario para que el Administrador pueda modificar el límite máximo de
     sillas y el porcentaje de umbral de alerta del restaurante --}}
<div class="card">
    <h4 style="font-family:'Playfair Display',serif; margin-bottom:18px;">
        🔧 Ajustar Configuración
    </h4>

    <form method="POST" action="{{ route('configuracion.update') }}">
        @csrf
        @method('PUT')

        {{-- Campo: Límite máximo de sillas --}}
        <div class="form-group">
            <label for="max_sillas">Límite máximo de sillas</label>
            <input type="number"
                   id="max_sillas"
                   name="max_sillas"
                   class="form-control"
                   value="{{ old('max_sillas', $config->max_sillas) }}"
                   min="0"
                   placeholder="Ej: 80">
            <p style="font-size:.78rem; color:var(--gray); margin-top:4px;">
                💡 Escribe <strong>0</strong> para usar el cálculo automático
                (suma de sillas de todas las mesas: <strong>{{ $sillasActuales }}</strong>).
                Si defines un número mayor a 0, ese será el límite fijo del restaurante.
            </p>
            @error('max_sillas')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo: Umbral de alerta --}}
        <div class="form-group">
            <label for="umbral_alerta">Umbral de alerta (%)</label>
            <input type="number"
                   id="umbral_alerta"
                   name="umbral_alerta"
                   class="form-control"
                   value="{{ old('umbral_alerta', $config->umbral_alerta) }}"
                   min="1"
                   max="100"
                   placeholder="Ej: 80">
            <p style="font-size:.78rem; color:var(--gray); margin-top:4px;">
                💡 Cuando la ocupación alcance este porcentaje del límite, se mostrará
                una alerta visual al Administrador y al Maitre. Valor recomendado: <strong>80%</strong>.
            </p>
            @error('umbral_alerta')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Resumen antes de guardar --}}
        <div style="
            background: #E0F7FA;
            border: 1px solid rgba(0,188,212,.25);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            font-size: .85rem;
        ">
            <strong>📋 Resumen de la configuración:</strong>
            <ul style="margin:8px 0 0 16px; line-height:1.8;">
                <li>Límite actual: <strong>{{ $config->max_sillas > 0 ? $config->max_sillas . ' sillas (manual)' : 'Automático (' . $sillasActuales . ' sillas por mesas)' }}</strong></li>
                <li>Umbral de alerta: <strong>{{ $config->umbral_alerta }}%</strong>
                    (se alertará al llegar a <strong>{{ round($config->limiteEfectivo() * $config->umbral_alerta / 100) }}</strong> sillas ocupadas)</li>
                <li>Ocupación actual: <strong>{{ $ocupados }} / {{ $config->limiteEfectivo() }}</strong> sillas</li>
            </ul>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn btn-primary">
                💾 Guardar Configuración
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

{{-- ========== ESTILOS DE ALERTAS ========== --}}
@push('styles')
<style>
/* Alerta visual de proximidad al límite de capacidad */
.capacity-alert {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 18px 22px;
    border-radius: 14px;
    margin-bottom: 24px;
    animation: alertPulse 2s ease-in-out infinite;
}

/* Alerta nivel advertencia: fondo ámbar para indicar precaución */
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

/* Animación suave de pulsación para llamar la atención del administrador
   sobre la alerta de proximidad al límite de capacidad */
@keyframes alertPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .85; }
}
</style>
@endpush

@endsection
