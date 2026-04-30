@extends('layouts.app')
@section('title', 'Nuevo Empleado')

@section('content')

<div style="max-width:540px; margin:0 auto;">

    <div style="margin-bottom:24px;">
        <a href="{{ route('empleados.index') }}" style="color:var(--gray); text-decoration:none; font-size:.85rem; display:inline-flex; align-items:center; gap:6px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Volver a Empleados
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Crear Nuevo Empleado</h3>
        </div>

        <form method="POST" action="{{ route('empleados.store') }}">
            @csrf

            <div class="form-group">
                <label for="nombre">Nombre de usuario <span style="color:var(--rust);">*</span></label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    class="form-control"
                    value="{{ old('nombre') }}"
                    maxlength="50"
                    required
                    autofocus
                    placeholder="Ej. Juan García"
                >
                @error('nombre')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Contraseña <span style="color:var(--rust);">*</span></label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        required
                        minlength="6"
                        placeholder="Mín. 6 caracteres"
                    >
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar contraseña <span style="color:var(--rust);">*</span></label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        required
                        placeholder="Repite la contraseña"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="rol_id">Rol <span style="color:var(--rust);">*</span></label>
                <select id="rol_id" name="rol_id" class="form-control" required>
                    <option value="">— Seleccionar rol —</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id_rol }}" {{ old('rol_id') == $rol->id_rol ? 'selected' : '' }}>
                            {{ $rol->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('rol_id')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- Descripción de roles --}}
            <div id="rol-desc" style="background:var(--cream); border-radius:8px; padding:14px; margin-bottom:20px; font-size:.82rem; color:var(--gray); display:none;">
                <strong id="rol-desc-title" style="color:var(--dark); display:block; margin-bottom:4px;"></strong>
                <span id="rol-desc-text"></span>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Empleado</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const rolDesc = {
    'Administrador': 'Acceso total al sistema: mesas, empleados, menú, reportes y todas las funciones.',
    'Maitre':        'Gestiona reservaciones, asigna mesas y supervisa el cupo del restaurante.',
    'Mesero':        'Crea y gestiona pedidos, registra comandas y entrega órdenes a los clientes.',
    'Cocinero':      'Ve las órdenes en cocina y actualiza su estado de preparación.',
};

const select = document.getElementById('rol_id');
const descBox = document.getElementById('rol-desc');
const descTitle = document.getElementById('rol-desc-title');
const descText = document.getElementById('rol-desc-text');

select.addEventListener('change', function() {
    const text = this.options[this.selectedIndex].text;
    if (rolDesc[text]) {
        descTitle.textContent = text;
        descText.textContent = rolDesc[text];
        descBox.style.display = 'block';
    } else {
        descBox.style.display = 'none';
    }
});
</script>
@endpush

@endsection