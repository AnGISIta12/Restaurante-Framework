@extends('layouts.app')
@section('title', 'Nueva Reservación')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h3>Nueva Reservación</h3>
            <p style="color:var(--gray); font-size:.85rem;">
                Registra una reservación para un cliente.
            </p>
        </div>
        <a href="{{ route('reservaciones.proximas') }}" class="btn btn-secondary btn-sm">Volver</a>
    </div>

    <form method="POST" action="{{ route('reservaciones.store') }}">
        @csrf

        <div class="form-group">
            <label>Cliente</label>
            <select name="cliente_id" class="form-control" required>
                <option value="">Seleccionar cliente</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id_usuario }}">{{ $cliente->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Cantidad de personas</label>
            <input type="number" name="cantidad" class="form-control" min="1" max="20" required>
        </div>

        <button type="submit" class="btn btn-primary">
            Registrar reservación
        </button>
    </form>
</div>
@endsection
