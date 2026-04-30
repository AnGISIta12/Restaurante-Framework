@extends('layouts.app')
@section('title', 'Editar Empleado')

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
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:44px; height:44px; border-radius:50%; background:var(--gold); display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--dark); font-size:1.1rem;">
                    {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                </div>
                <div>
                    <h3 style="font-size:1rem;">{{ $usuario->nombre }}</h3>
                    <p style="font-size:.78rem; color:var(--gray);">ID: {{ $usuario->id_usuario }}</p>
                </div>
            </div>
        </div>

        <div style="background:var(--cream); border-radius:8px; padding:14px; margin-bottom:20px; font-size:.82rem;">
            <strong style="color:var(--dark);">Rol actual:</strong>
            <span style="margin-left:8px;">
                @foreach($usuario->roles as $r)
                    <span class="badge badge-ok">{{ $r->nombre }}</span>
                @endforeach
            </span>
        </div>

        <form method="POST" action="{{ route('empleados.update', $usuario) }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label for="rol_id">Nuevo Rol <span style="color:var(--rust);">*</span></label>
                <select id="rol_id" name="rol_id" class="form-control" required>
                    <option value="">— Seleccionar rol —</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id_rol }}"
                            {{ (old('rol_id', $rolActual) == $rol->id_rol) ? 'selected' : '' }}>
                            {{ $rol->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('rol_id')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div id="rol-desc" style="background:var(--cream); border-radius:8px; padding:14px; margin-bottom:20px; font-size:.82rem; color:var(--gray);">
                <strong id="rol-desc-title" style="color:var(--dark); display:block; margin-bottom:4px;"></strong>
                <span id="rol-desc-text"></span>
            </div>

            <div style="display:flex; gap:12px; justify-content:space-between; align-items:center;">
                <form method="POST" action="{{ route('empleados.destroy', $usuario) }}"
                      onsubmit="return confirm('¿Eliminar a {{ $usuario->nombre }}? Esta acción no se puede deshacer.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar empleado</button>
                </form>
                <div style="display:flex; gap:12px;">
                    <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar Rol</button>
                </div>
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

function updateDesc() {
    const text = select.options[select.selectedIndex]?.text;
    if (rolDesc[text]) {
        descTitle.textContent = text;
        descText.textContent = rolDesc[text];
        descBox.style.opacity = '1';
    } else {
        descTitle.textContent = '';
        descText.textContent = 'Selecciona un rol para ver su descripción.';
    }
}

select.addEventListener('change', updateDesc);
updateDesc();
</script>
@endpush

@endsection