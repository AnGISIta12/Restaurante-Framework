@extends('layouts.app')
@section('title', 'Mesas')

@section('content')

<div class="card-header" style="margin-bottom:24px;">
    <div>
        <h3 style="font-family:'Playfair Display',serif; font-size:1.3rem;">Gestión de Mesas</h3>
        <p style="color:var(--gray); font-size:.85rem; margin-top:4px;">
            {{ $mesas->count() }} mesas registradas · Capacidad total: <strong>{{ $cupoTotal }} sillas</strong>
        </p>
    </div>
    <a href="{{ route('mesas.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Nueva Mesa
    </a>
</div>

{{-- Stats rápidas --}}
<div class="stats-grid" style="margin-bottom:28px;">
    <div class="stat-card gold">
        <div class="stat-val">{{ $mesas->count() }}</div>
        <div class="stat-label">Total de Mesas</div>
    </div>
    <div class="stat-card sage">
        <div class="stat-val">{{ $cupoTotal }}</div>
        <div class="stat-label">Sillas en Total</div>
    </div>
    <div class="stat-card rust">
        <div class="stat-val">{{ $mesas->count() > 0 ? round($cupoTotal / $mesas->count(), 1) : 0 }}</div>
        <div class="stat-label">Promedio por Mesa</div>
    </div>
</div>

<div class="card">
    @if($mesas->isEmpty())
        <div style="text-align:center; padding:60px 20px; color:var(--gray);">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px; display:block; opacity:.4;">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            <p style="font-size:.95rem;">No hay mesas registradas aún.</p>
            <a href="{{ route('mesas.create') }}" class="btn btn-primary" style="margin-top:16px; display:inline-flex;">Crear primera mesa</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID Mesa</th>
                        <th>Capacidad (sillas)</th>
                        <th>Categoría</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mesas as $i => $mesa)
                    <tr>
                        <td style="color:var(--gray); font-size:.8rem;">{{ $i + 1 }}</td>
                        <td>
                            <span style="background:var(--cream); border:1px solid var(--border); border-radius:6px; padding:3px 10px; font-weight:600; font-size:.85rem;">
                                Mesa {{ $mesa->id_mesa }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="display:flex; gap:3px;">
                                    @for($s = 0; $s < min($mesa->sillas, 10); $s++)
                                        <div style="width:8px; height:8px; border-radius:50%; background:var(--gold);"></div>
                                    @endfor
                                    @if($mesa->sillas > 10)
                                        <span style="font-size:.75rem; color:var(--gray);">+{{ $mesa->sillas - 10 }}</span>
                                    @endif
                                </div>
                                <span style="font-weight:600;">{{ $mesa->sillas }}</span>
                                <span style="color:var(--gray); font-size:.8rem;">sillas</span>
                            </div>
                        </td>
                        <td>
                            @if($mesa->sillas <= 2)
                                <span class="badge badge-pending">Íntima</span>
                            @elseif($mesa->sillas <= 4)
                                <span class="badge badge-ok">Pequeña</span>
                            @elseif($mesa->sillas <= 6)
                                <span class="badge badge-assigned">Mediana</span>
                            @else
                                <span class="badge badge-progress">Grande</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <a href="{{ route('mesas.edit', $mesa) }}" class="btn btn-secondary btn-sm">Editar</a>
                                <form method="POST" action="{{ route('mesas.destroy', $mesa) }}"
                                      onsubmit="return confirm('¿Eliminar Mesa {{ $mesa->id_mesa }}? Esta acción no se puede deshacer.')">
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