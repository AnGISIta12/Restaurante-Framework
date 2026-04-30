@extends('layouts.app')
@section('title', 'Reporte de Pedidos')

@section('content')

<div class="card-header" style="margin-bottom:24px;">
    <div>
        <h3 style="font-family:'Playfair Display',serif; font-size:1.3rem;">Reporte de Pedidos</h3>
        <p style="color:var(--gray); font-size:.85rem; margin-top:4px;">{{ $pedidos->total() }} pedidos en total</p>
    </div>
    <a href="{{ route('reportes.index') }}" class="btn btn-secondary btn-sm">← Volver a Reportes</a>
</div>

<div class="card">
    @if($pedidos->isEmpty())
        <p style="text-align:center; padding:40px; color:var(--gray);">No hay pedidos registrados.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Mesero</th>
                        <th>Órdenes</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedidos as $pedido)
                    <tr>
                        <td style="font-weight:600; font-size:.875rem;">#{{ $pedido->id_pedido }}</td>
                        <td>{{ $pedido->cliente?->nombre ?? '—' }}</td>
                        <td style="color:var(--gray); font-size:.85rem;">{{ $pedido->mesero?->nombre ?? '—' }}</td>
                        <td>
                            <div style="display:flex; flex-wrap:wrap; gap:4px; max-width:220px;">
                                @foreach($pedido->ordenes->take(4) as $o)
                                    <span style="font-size:.72rem; background:var(--cream); border:1px solid var(--border); border-radius:4px; padding:2px 6px;">
                                        {{ Str::limit($o->plato?->nombre ?? '?', 18) }} ×{{ $o->cantidad }}
                                    </span>
                                @endforeach
                                @if($pedido->ordenes->count() > 4)
                                    <span style="font-size:.72rem; color:var(--gray);">+{{ $pedido->ordenes->count() - 4 }} más</span>
                                @endif
                            </div>
                        </td>
                        <td style="font-weight:700;">
                            ${{ number_format($pedido->getTotal(), 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;">
            {{ $pedidos->links() }}
        </div>
    @endif
</div>

@endsection