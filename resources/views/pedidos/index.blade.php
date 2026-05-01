@extends('layouts.app')

@section('title', 'Historial de Pedidos')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>🧾 Historial de Pedidos</h3>
        <a href="{{ route('pedidos.create') }}" class="btn btn-primary btn-sm">
            + Nuevo Pedido
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Mesero</th>
                    <th>Órdenes</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                    <tr>
                        <td><strong>#{{ str_pad($pedido->id_pedido, 3, '0', STR_PAD_LEFT) }}</strong></td>
                        <td>{{ $pedido->cliente->nombre }}</td>
                        <td>{{ $pedido->mesero->nombre }}</td>
                        <td>
                            <small>
                                {{ $pedido->ordenes->count() }} ítems
                            </small>
                        </td>
                        <td>${{ number_format($pedido->getTotal(), 2) }}</td>
                        <td>
                            <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" class="btn btn-secondary btn-sm">
                                Ver Detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--gray);">
                            No hay pedidos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $pedidos->links() }}
    </div>
</div>
@endsection
