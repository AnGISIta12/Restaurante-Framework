@extends('layouts.app')
@section('title', 'Cupo del Restaurante')

@section('content')
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

    <div class="stats-grid" style="margin-bottom:28px;">
        <div class="stat-card">
            <div class="stat-val">{{ $cupoTotal }}</div>
            <div class="stat-label">Cupo total</div>
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

    <div style="margin-bottom:28px;">
        <label style="font-weight:600;">Ocupación actual</label>
        <div style="background:#eee; border-radius:999px; overflow:hidden; height:18px; margin-top:8px;">
            <div style="width:{{ $porcentaje }}%; background:var(--rust); height:18px;"></div>
        </div>
        <p style="font-size:.85rem; color:var(--gray); margin-top:6px;">
            {{ $porcentaje }}% de ocupación de mesas.
        </p>
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
@endsection
