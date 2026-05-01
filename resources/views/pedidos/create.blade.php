@extends('layouts.app')

@section('title', 'Nuevo Pedido')

@section('content')
<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <h3>📝 Registrar Nueva Comanda</h3>
        <p style="font-size: 0.85rem; color: var(--gray);">Seleccione el cliente y los platos solicitados.</p>
    </div>

    <form action="{{ route('pedidos.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="cliente_id">Cliente</label>
            <select name="cliente_id" id="cliente_id" class="form-control" required>
                <option value="">-- Seleccione un cliente --</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id_usuario }}">{{ $cliente->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-top: 30px;">
            <h4 style="font-family: 'Playfair Display', serif; margin-bottom: 15px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">Menú Disponible</h4>
            
            @php
                $grupos = $platos->groupBy(fn($p) => $p->tipo->nombre ?? 'Otros');
            @endphp

            @foreach($grupos as $tipo => $items)
                <div style="margin-bottom: 25px;">
                    <h5 style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; color: var(--gold); margin-bottom: 12px;">{{ $tipo }}</h5>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Sel.</th>
                                    <th>Plato</th>
                                    <th>Precio</th>
                                    <th style="width: 100px;">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $plato)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="platos[]" value="{{ $plato->id_plato }}" class="plato-checkbox">
                                        </td>
                                        <td>
                                            <strong>{{ $plato->nombre }}</strong><br>
                                            <small style="color: var(--gray);">{{ $plato->descripcion }}</small>
                                        </td>
                                        <td>${{ number_format($plato->precio, 2) }}</td>
                                        <td>
                                            <input type="number" name="cantidades[]" value="1" min="1" class="form-control form-control-sm qty-input" disabled>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Registrar Pedido</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.plato-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            const qtyInput = row.querySelector('.qty-input');
            qtyInput.disabled = !this.checked;
            if (this.checked) {
                qtyInput.focus();
            }
        });
    });

    // Validar que al menos uno esté seleccionado antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.plato-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('Por favor, seleccione al menos un plato.');
        }
    });
</script>
@endpush
@endsection
