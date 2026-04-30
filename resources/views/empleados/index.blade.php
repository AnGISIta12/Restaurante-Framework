@extends('layouts.app')
@section('title', 'Empleados')

@section('content')

<div class="card-header" style="margin-bottom:24px;">
    <div>
        <h3 style="font-family:'Playfair Display',serif; font-size:1.3rem;">Gestión de Empleados</h3>
        <p style="color:var(--gray); font-size:.85rem; margin-top:4px;">{{ $empleados->count() }} empleados registrados en el sistema</p>
    </div>
    <a href="{{ route('empleados.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Nuevo Empleado
    </a>
</div>

{{-- Stats por rol --}}
@php
    $porRol = $empleados->flatMap->roles->countBy('nombre');
@endphp
<div class="stats-grid" style="margin-bottom:28px;">
    @foreach(['Administrador','Maitre','Mesero','Cocinero'] as $r)
    <div class="stat-card {{ $r === 'Administrador' ? 'rust' : ($r === 'Maitre' ? 'gold' : ($r === 'Mesero' ? 'sage' : '')) }}">
        <div class="stat-val">{{ $porRol[$r] ?? 0 }}</div>
        <div class="stat-label">{{ $r }}{{ ($porRol[$r] ?? 0) !== 1 ? 's' : '' }}</div>
    </div>
    @endforeach
</div>

<div class="card">
    @if($empleados->isEmpty())
        <div style="text-align:center; padding:60px 20px; color:var(--gray);">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px; display:block; opacity:.4;">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
            <p style="font-size:.95rem;">No hay empleados registrados.</p>
            <a href="{{ route('empleados.create') }}" class="btn btn-primary" style="margin-top:16px; display:inline-flex;">Crear primer empleado</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Rol(es)</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($empleados as $empleado)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:38px; height:38px; border-radius:50%; background:var(--gold); display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--dark); font-size:.9rem; flex-shrink:0;">
                                    {{ strtoupper(substr($empleado->nombre, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600; font-size:.9rem;">{{ $empleado->nombre }}</div>
                                    <div style="font-size:.75rem; color:var(--gray);">ID: {{ $empleado->id_usuario }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                @foreach($empleado->roles as $rol)
                                    @php
                                        $badge = match($rol->nombre) {
                                            'Administrador' => 'badge-pending',
                                            'Maitre'        => 'badge-progress',
                                            'Mesero'        => 'badge-ok',
                                            'Cocinero'      => 'badge-assigned',
                                            default         => 'badge-ok',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $rol->nombre }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <a href="{{ route('empleados.edit', $empleado) }}" class="btn btn-secondary btn-sm">Editar</a>
                                <form method="POST" action="{{ route('empleados.destroy', $empleado) }}"
                                      onsubmit="return confirm('¿Eliminar a {{ $empleado->nombre }}? Esta acción no se puede deshacer.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection