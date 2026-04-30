@extends('layouts.app')
@section('title', 'Reportes')

@section('content')

<div style="margin-bottom:24px;">
    <h3 style="font-family:'Playfair Display',serif; font-size:1.3rem;">Panel de Reportes</h3>
    <p style="color:var(--gray); font-size:.85rem; margin-top:4px;">Resumen general del restaurante · {{ now()->format('d/m/Y H:i') }}</p>
</div>

{{-- ── KPIs principales ── --}}
<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); margin-bottom:28px;">
    <div class="stat-card gold">
        <div class="stat-val">{{ $totalMesas }}</div>
        <div class="stat-label">Mesas registradas</div>
    </div>
    <div class="stat-card sage">
        <div class="stat-val">{{ $cupoTotal }}</div>
        <div class="stat-label">Capacidad total</div>
    </div>
    <div class="stat-card rust">
        <div class="stat-val">{{ $totalReservaciones }}</div>
        <div class="stat-label">Reservaciones</div>
    </div>
    <div class="stat-card">
        <div class="stat-val">{{ $totalPedidos }}</div>
        <div class="stat-label">Pedidos totales</div>
    </div>
    <div class="stat-card">
        <div class="stat-val">{{ $totalOrdenes }}</div>
        <div class="stat-label">Órdenes totales</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

    {{-- ── Top 5 platos más pedidos ── --}}
    <div class="card">
        <div class="card-header">
            <h3 style="font-size:1rem;">🏆 Platos más pedidos</h3>
        </div>
        @if($platosTop->isEmpty())
            <p style="color:var(--gray); font-size:.85rem; padding:20px 0;">Sin datos disponibles.</p>
        @else
            @php $maxTop = $platosTop->first()->total_pedido ?? 1; @endphp
            <div style="display:flex; flex-direction:column; gap:14px;">
                @foreach($platosTop as $i => $plato)
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-weight:700; color:var(--gold); font-size:.85rem; width:18px;">{{ $i+1 }}</span>
                            <span style="font-size:.85rem; font-weight:500;">{{ $plato->nombre }}</span>
                        </div>
                        <span style="font-size:.8rem; font-weight:700; color:var(--dark);">{{ $plato->total_pedido }}</span>
                    </div>
                    <div style="height:6px; background:var(--border); border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:{{ round($plato->total_pedido / $maxTop * 100) }}%; background:{{ $i===0 ? 'var(--gold)' : 'var(--sage)' }}; border-radius:3px; transition:width .3s;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Ingresos por plato (top 10) ── --}}
    <div class="card">
        <div class="card-header">
            <h3 style="font-size:1rem;">💰 Ingresos por plato</h3>
        </div>
        @if($ingresos->isEmpty())
            <p style="color:var(--gray); font-size:.85rem; padding:20px 0;">Sin datos disponibles.</p>
        @else
            @php $maxIngreso = $ingresos->first()->ingreso ?? 1; @endphp
            <div style="display:flex; flex-direction:column; gap:12px; max-height:260px; overflow-y:auto;">
                @foreach($ingresos as $item)
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                        <span style="font-size:.82rem;">{{ $item->nombre }}</span>
                        <span style="font-size:.82rem; font-weight:700;">${{ number_format($item->ingreso, 2) }}</span>
                    </div>
                    <div style="height:5px; background:var(--border); border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:{{ round($item->ingreso / $maxIngreso * 100) }}%; background:var(--rust); border-radius:3px;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

    {{-- ── Reservaciones por estado ── --}}
    <div class="card">
        <div class="card-header">
            <h3 style="font-size:1rem;">📅 Reservaciones por estado</h3>
            <a href="{{ route('reportes.reservaciones') }}" class="btn btn-secondary btn-sm">Ver todas</a>
        </div>
        @php
            $labelsRes = [0 => 'Pendiente', 1 => 'Confirmada', 2 => 'Asignada'];
            $badgesRes = [0 => 'badge-pending', 1 => 'badge-ok', 2 => 'badge-assigned'];
            $totalRes  = $reservacionesPorEstado->sum('total');
        @endphp
        <div style="display:flex; flex-direction:column; gap:12px;">
            @forelse($reservacionesPorEstado as $row)
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge {{ $badgesRes[$row->estado] ?? 'badge-ok' }}">{{ $labelsRes[$row->estado] ?? "Estado {$row->estado}" }}</span>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:80px; height:6px; background:var(--border); border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:{{ $totalRes > 0 ? round($row->total / $totalRes * 100) : 0 }}%; background:var(--gold); border-radius:3px;"></div>
                    </div>
                    <span style="font-weight:700; font-size:.9rem; min-width:24px; text-align:right;">{{ $row->total }}</span>
                </div>
            </div>
            @empty
                <p style="color:var(--gray); font-size:.85rem;">Sin reservaciones.</p>
            @endforelse
            @if($totalRes > 0)
            <div style="border-top:1px solid var(--border); padding-top:10px; margin-top:4px; display:flex; justify-content:space-between; font-size:.82rem; color:var(--gray);">
                <span>Total</span> <strong style="color:var(--dark);">{{ $totalRes }}</strong>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Órdenes por estado ── --}}
    <div class="card">
        <div class="card-header">
            <h3 style="font-size:1rem;">🍽 Órdenes por estado</h3>
            <a href="{{ route('reportes.pedidos') }}" class="btn btn-secondary btn-sm">Ver todos</a>
        </div>
        @php
            $labelsOrd = [0 => 'Pendiente', 1 => 'En preparación', 2 => 'Lista', 3 => 'Entregada'];
            $badgesOrd = [0 => 'badge-pending', 1 => 'badge-progress', 2 => 'badge-ok', 3 => 'badge-done'];
            $colorsOrd = [0 => '#C9A84C', 1 => '#d97706', 2 => '#5C7A5A', 3 => '#1e40af'];
            $totalOrd  = $ordenesPorEstado->sum('total');
        @endphp
        <div style="display:flex; flex-direction:column; gap:12px;">
            @forelse($ordenesPorEstado as $row)
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span class="badge {{ $badgesOrd[$row->estado] ?? 'badge-ok' }}">{{ $labelsOrd[$row->estado] ?? "Estado {$row->estado}" }}</span>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:80px; height:6px; background:var(--border); border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:{{ $totalOrd > 0 ? round($row->total / $totalOrd * 100) : 0 }}%; background:{{ $colorsOrd[$row->estado] ?? 'var(--sage)' }}; border-radius:3px;"></div>
                    </div>
                    <span style="font-weight:700; font-size:.9rem; min-width:24px; text-align:right;">{{ $row->total }}</span>
                </div>
            </div>
            @empty
                <p style="color:var(--gray); font-size:.85rem;">Sin órdenes.</p>
            @endforelse
            @if($totalOrd > 0)
            <div style="border-top:1px solid var(--border); padding-top:10px; margin-top:4px; display:flex; justify-content:space-between; font-size:.82rem; color:var(--gray);">
                <span>Total</span> <strong style="color:var(--dark);">{{ $totalOrd }}</strong>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Accesos rápidos a reportes detallados ── --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
    <a href="{{ route('reportes.reservaciones') }}" style="text-decoration:none;">
        <div class="card" style="display:flex; align-items:center; gap:16px; transition:all .2s; cursor:pointer;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
            <div style="width:48px; height:48px; background:rgba(201,168,76,.15); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="22" height="22" fill="none" stroke="var(--gold)" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div>
                <div style="font-weight:600; font-size:.9rem;">Reporte de Reservaciones</div>
                <div style="font-size:.78rem; color:var(--gray);">Historial completo con estados y mesas</div>
            </div>
        </div>
    </a>

    <a href="{{ route('reportes.pedidos') }}" style="text-decoration:none;">
        <div class="card" style="display:flex; align-items:center; gap:16px; transition:all .2s; cursor:pointer;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
            <div style="width:48px; height:48px; background:rgba(92,122,90,.15); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="22" height="22" fill="none" stroke="var(--sage)" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <div>
                <div style="font-weight:600; font-size:.9rem;">Reporte de Pedidos</div>
                <div style="font-size:.78rem; color:var(--gray);">Comandas, clientes, meseros y totales</div>
            </div>
        </div>
    </a>
</div>

@push('styles')
<style>
.badge-done { background: #DBEAFE; color: #1E40AF; }
</style>
@endpush

@endsection