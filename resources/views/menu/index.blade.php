@extends('layouts.app')
@section('title', 'Menú')

@section('content')

<div class="card-header" style="margin-bottom:24px;">
    <div>
        <h3 style="font-family:'Playfair Display',serif; font-size:1.3rem;">Gestión del Menú</h3>
        <p style="color:var(--gray); font-size:.85rem; margin-top:4px;">{{ $platos->count() }} platos en carta</p>
    </div>
    @php $rol = session('rol'); @endphp
    @if(in_array($rol, ['Administrador','Maitre']))
    <a href="{{ route('menu.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Nuevo Plato
    </a>
    @endif
</div>

{{-- Filtros por tipo --}}
<div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
    <button class="filter-btn active" data-filter="all">Todos ({{ $platos->count() }})</button>
    @foreach($tipos as $tipo)
        <button class="filter-btn" data-filter="{{ $tipo->id }}">
            {{ ucfirst($tipo->nombre) }} ({{ $tipo->platos->count() }})
        </button>
    @endforeach
</div>

<div class="card">
    @if($platos->isEmpty())
        <div style="text-align:center; padding:60px 20px; color:var(--gray);">
            <p>No hay platos registrados.</p>
            @if(in_array($rol, ['Administrador','Maitre']))
                <a href="{{ route('menu.create') }}" class="btn btn-primary" style="margin-top:16px; display:inline-flex;">Agregar primer plato</a>
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Plato</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Tiempo prep.</th>
                        @if(in_array($rol, ['Administrador','Maitre']))
                        <th style="text-align:right;">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($platos as $plato)
                    <tr class="plato-row" data-tipo="{{ $plato->tipo_id }}">
                        <td>
                            <div>
                                <div style="font-weight:600; font-size:.9rem;">{{ $plato->nombre }}</div>
                                @if($plato->descripcion)
                                    <div style="font-size:.77rem; color:var(--gray); margin-top:2px;">{{ Str::limit($plato->descripcion, 60) }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php
                                $cat = $plato->tipo?->nombre ?? '—';
                                $badgeCat = match($cat) {
                                    'entrada'     => 'badge-ok',
                                    'plato fuerte'=> 'badge-progress',
                                    'bebida'      => 'badge-assigned',
                                    default       => 'badge-pending',
                                };
                            @endphp
                            <span class="badge {{ $badgeCat }}">{{ ucfirst($cat) }}</span>
                        </td>
                        <td>
                            <span style="font-weight:700; color:var(--dark);">${{ number_format($plato->precio, 2) }}</span>
                        </td>
                        <td>
                            @if($plato->tiempo)
                                @php
                                    $partes = explode(':', $plato->tiempo);
                                    $mins = (int)($partes[1] ?? 0);
                                @endphp
                                <span style="font-size:.85rem;">{{ $mins }} min</span>
                            @else
                                <span style="color:var(--gray);">—</span>
                            @endif
                        </td>
                        @if(in_array($rol, ['Administrador','Maitre']))
                        <td style="text-align:right;">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <a href="{{ route('menu.edit', $plato) }}" class="btn btn-secondary btn-sm">Editar</a>
                                <form method="POST" action="{{ route('menu.destroy', $plato) }}"
                                      onsubmit="return confirm('¿Eliminar {{ $plato->nombre }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@push('styles')
<style>
.filter-btn {
    padding: 6px 14px; border-radius: 20px; border: 1.5px solid var(--border);
    background: #fff; color: var(--gray); font-family: inherit; font-size: .8rem;
    cursor: pointer; transition: all .15s; font-weight: 500;
}
.filter-btn.active, .filter-btn:hover {
    background: var(--gold); border-color: var(--gold); color: var(--dark);
}
</style>
@endpush

@push('scripts')
<script>
const btns = document.querySelectorAll('.filter-btn');
const rows = document.querySelectorAll('.plato-row');

btns.forEach(btn => {
    btn.addEventListener('click', () => {
        btns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const filter = btn.dataset.filter;
        rows.forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.tipo === filter) ? '' : 'none';
        });
    });
});
</script>
@endpush

@endsection