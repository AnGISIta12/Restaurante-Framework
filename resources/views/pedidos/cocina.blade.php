@extends('layouts.app')

@section('title', 'Panel de Cocina')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>👨‍🍳 Órdenes en Cocina</h3>
        <p style="font-size: 0.85rem; color: var(--gray);">Gestión de preparaciones en tiempo real.</p>
    </div>

    <div class="table-wrap" style="margin-top: 20px;">
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Pedido</th>
                    <th>Plato</th>
                    <th>Cant.</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordenes as $orden)
                    <tr>
                        <td>{{ $orden->solicitado->format('H:i') }}</td>
                        <td>
                            <strong>#{{ str_pad($orden->pedido_id, 3, '0', STR_PAD_LEFT) }}</strong><br>
                            <small>{{ $orden->pedido->cliente->nombre }}</small>
                        </td>
                        <td>
                            <span style="font-size: 1rem; font-weight: 600;">{{ $orden->plato->nombre }}</span>
                        </td>
                        <td>
                            <span class="badge badge-assigned">{{ $orden->cantidad }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $orden->estado == 1 ? 'badge-progress' : 'badge-pending' }}">
                                {{ $orden->getEtiquetaEstado() }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                @if($orden->estado == 0) {{-- Pendiente --}}
                                    <form action="{{ route('ordenes.estado', $orden->id_orden) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="estado" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            Empezar
                                        </button>
                                    </form>
                                @elseif($orden->estado == 1) {{-- En preparación --}}
                                    <form action="{{ route('ordenes.estado', $orden->id_orden) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="estado" value="2">
                                        <button type="submit" class="btn btn-sage btn-sm">
                                            Listo
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 60px;">
                            <div style="font-size: 3rem; margin-bottom: 15px;">🍳</div>
                            <p style="color: var(--gray);">No hay órdenes pendientes en este momento.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
