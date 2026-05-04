@extends('layouts.app')

@section('title', 'Detalle del Pedido')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h3>🧾 Pedido #{{ str_pad($pedido->id_pedido, 3, '0', STR_PAD_LEFT) }}</h3>
            <p style="font-size:.85rem;color:var(--gray);">
                Cliente: <strong>{{ $pedido->cliente->nombre ?? '—' }}</strong> ·
                Mesero: <strong>{{ $pedido->mesero->nombre ?? '—' }}</strong>
            </p>
        </div>

        <a href="{{ route('pedidos.index') }}" class="btn btn-secondary btn-sm">
            ← Volver
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Plato</th>
                    <th>Cantidad</th>
                    <th>Estado</th>
                    <th>Precio unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedido->ordenes as $orden)
                    <tr>
                        <td>
                            <strong>{{ $orden->plato->nombre ?? '—' }}</strong><br>
                            <small style="color:var(--gray);">
                                {{ $orden->plato->tipo->nombre ?? 'Sin categoría' }}
                            </small>
                        </td>
                        <td>{{ $orden->cantidad }}</td>
                        <td>
                            <span class="badge
                                @if($orden->estado == 0) badge-pending
                                @elseif($orden->estado == 1) badge-progress
                                @elseif($orden->estado == 2) badge-ok
                                @else badge-done
                                @endif">
                                {{ $orden->getEtiquetaEstado() }}
                            </span>
                        </td>
                        <td>${{ number_format($orden->plato->precio ?? 0, 2) }}</td>
                        <td><strong>${{ number_format($orden->getSubtotal(), 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:40px;color:var(--gray);">
                            Este pedido no tiene órdenes registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:24px;text-align:right;">
        <span style="font-size:.9rem;color:var(--gray);">Total del pedido</span><br>
        <strong style="font-size:1.5rem;">${{ number_format($pedido->getTotal(), 2) }}</strong>
    </div>
</div>
@endsection
