@extends('layouts.app')

@section('title', 'Entregas Pendientes')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>✅ Órdenes Listas</h3>
        <p style="font-size: 0.85rem; color: var(--gray);">Platos que ya salieron de cocina y esperan ser entregados.</p>
    </div>

    <div class="table-wrap" style="margin-top: 20px;">
        <table>
            <thead>
                <tr>
                    <th>Hora Listo</th>
                    <th>Pedido</th>
                    <th>Plato</th>
                    <th>Cant.</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordenes as $orden)
                    <tr>
                        <td>{{ now()->diffForHumans($orden->solicitado) }}</td>
                        <td>
                            <strong>#{{ str_pad($orden->pedido_id, 3, '0', STR_PAD_LEFT) }}</strong><br>
                            <small>{{ $orden->pedido->cliente->nombre }}</small>
                        </td>
                        <td>
                            <span style="font-size: 1rem; font-weight: 600;">{{ $orden->plato->nombre }}</span>
                        </td>
                        <td>
                            <span class="badge badge-done">{{ $orden->cantidad }}</span>
                        </td>
                        <td>
                            <form action="{{ route('ordenes.entregar', $orden->id_orden) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Confirmar Entrega
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 60px;">
                            <div style="font-size: 3rem; margin-bottom: 15px;">🍽️</div>
                            <p style="color: var(--gray);">Todo entregado. ¡Buen trabajo!</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
