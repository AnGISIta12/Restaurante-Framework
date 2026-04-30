@extends('layouts.app')
@section('title', 'Reporte de Reservaciones')

@section('content')

<div class="card-header" style="margin-bottom:24px;">
    <div>
        <h3 style="font-family:'Playfair Display',serif; font-size:1.3rem;">Reporte de Reservaciones</h3>
        <p style="color:var(--gray); font-size:.85rem; margin-top:4px;">{{ $reservaciones->count() }} reservaciones en total</p>
    </div>
    <a href="{{ route('reportes.index') }}" class="btn btn-secondary btn-sm">← Volver a Reportes</a>
</div>

<div class="card">
    @if($reservaciones->isEmpty())
        <p style="text-align:center; padding:40px; color:var(--gray);">No hay reservaciones registradas.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Personas</th>
                        <th>Mesa asignada</th>
                        <th>Inicio</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservaciones as $r)
                    @php
                        $badgeE = match($r->estado) {
                            0 => 'badge-pending',
                            1 => 'badge-ok',
                            2 => 'badge-assigned',
                            default => 'badge-ok',
                        };
                        $labelE = match($r->estado) {
                            0 => 'Pendiente',
                            1 => 'Confirmada',
                            2 => 'Asignada',
                            default => "Estado {$r->estado}",
                        };
                    @endphp
                    <tr>
                        <td style="color:var(--gray); font-size:.8rem;">#{{ $r->id_reservacion }}</td>
                        <td>
                            <div style="font-weight:600; font-size:.875rem;">{{ $r->cliente?->nombre ?? '—' }}</div>
                        </td>
                        <td>{{ $r->cantidad }}</td>
                        <td>
                            @if($r->horario?->mesa)
                                <span style="font-weight:600;">Mesa {{ $r->horario->mesa->id_mesa }}</span>
                                <span style="color:var(--gray); font-size:.78rem;">({{ $r->horario->mesa->sillas }} sillas)</span>
                            @else
                                <span style="color:var(--gray);">Sin asignar</span>
                            @endif
                        </td>
                        <td style="font-size:.82rem;">
                            @if($r->horario?->inicio)
                                {{ $r->horario->inicio->format('d/m/Y H:i') }}
                            @else
                                <span style="color:var(--gray);">—</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $badgeE }}">{{ $labelE }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection