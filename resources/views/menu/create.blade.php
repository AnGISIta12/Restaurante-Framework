@extends('layouts.app')
@section('title', 'Nuevo Plato')

@section('content')

<div style="max-width:600px; margin:0 auto;">

    <div style="margin-bottom:24px;">
        <a href="{{ route('menu.index') }}" style="color:var(--gray); text-decoration:none; font-size:.85rem; display:inline-flex; align-items:center; gap:6px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Volver al Menú
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Agregar Nuevo Plato</h3>
        </div>

        <form method="POST" action="{{ route('menu.store') }}">
            @csrf

            <div class="form-group">
                <label for="nombre">Nombre del plato <span style="color:var(--rust);">*</span></label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    class="form-control"
                    value="{{ old('nombre') }}"
                    maxlength="100"
                    required
                    autofocus
                    placeholder="Ej. Pasta Carbonara"
                >
                @error('nombre') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_id">Categoría <span style="color:var(--rust);">*</span></label>
                    <select id="tipo_id" name="tipo_id" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo->id }}" {{ old('tipo_id') == $tipo->id ? 'selected' : '' }}>
                                {{ ucfirst($tipo->nombre) }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="precio">Precio (USD) <span style="color:var(--rust);">*</span></label>
                    <input
                        type="number"
                        id="precio"
                        name="precio"
                        class="form-control"
                        value="{{ old('precio') }}"
                        min="0"
                        step="0.01"
                        required
                        placeholder="0.00"
                    >
                    @error('precio') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    class="form-control"
                    rows="2"
                    maxlength="255"
                    placeholder="Descripción breve del plato..."
                >{{ old('descripcion') }}</textarea>
                @error('descripcion') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="tiempo">Tiempo de preparación</label>
                <select id="tiempo" name="tiempo" class="form-control">
                    <option value="">— Sin especificar —</option>
                    @foreach(['00:10:00'=>'10 min','00:15:00'=>'15 min','00:20:00'=>'20 min','00:25:00'=>'25 min',
                              '00:30:00'=>'30 min','00:35:00'=>'35 min','00:40:00'=>'40 min','00:45:00'=>'45 min',
                              '00:50:00'=>'50 min','00:55:00'=>'55 min','01:00:00'=>'60 min'] as $val => $label)
                        <option value="{{ $val }}" {{ old('tiempo') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('tiempo') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <a href="{{ route('menu.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Agregar Plato</button>
            </div>
        </form>
    </div>
</div>

@endsection