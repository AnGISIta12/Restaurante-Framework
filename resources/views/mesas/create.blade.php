@extends('layouts.app')
@section('title', 'Nueva Mesa')

@section('content')

<div style="max-width:500px; margin:0 auto;">

    <div style="margin-bottom:24px;">
        <a href="{{ route('mesas.index') }}" style="color:var(--gray); text-decoration:none; font-size:.85rem; display:inline-flex; align-items:center; gap:6px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Volver a Mesas
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Crear Nueva Mesa</h3>
        </div>

        <form method="POST" action="{{ route('mesas.store') }}">
            @csrf

            <div class="form-group">
                <label for="sillas">Número de sillas <span style="color:var(--rust);">*</span></label>
                <input
                    type="number"
                    id="sillas"
                    name="sillas"
                    class="form-control"
                    value="{{ old('sillas', 4) }}"
                    min="1"
                    max="50"
                    required
                    placeholder="Ej. 4"
                >
                @error('sillas')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <p style="font-size:.78rem; color:var(--gray); margin-top:6px;">Entre 1 y 50 sillas por mesa.</p>
            </div>

            {{-- Preview visual --}}
            <div style="background:var(--cream); border-radius:10px; padding:20px; text-align:center; margin-bottom:20px;">
                <p style="font-size:.78rem; color:var(--gray); margin-bottom:12px; text-transform:uppercase; letter-spacing:.5px;">Vista previa</p>
                <div id="preview-dots" style="display:flex; flex-wrap:wrap; justify-content:center; gap:6px; max-width:200px; margin:0 auto 8px;">
                </div>
                <p id="preview-text" style="font-size:.85rem; color:var(--dark); font-weight:600;"></p>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <a href="{{ route('mesas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Mesa</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const input = document.getElementById('sillas');
    const dots = document.getElementById('preview-dots');
    const text = document.getElementById('preview-text');

    function updatePreview() {
        const n = Math.max(1, Math.min(50, parseInt(input.value) || 0));
        dots.innerHTML = '';
        const show = Math.min(n, 20);
        for (let i = 0; i < show; i++) {
            const d = document.createElement('div');
            d.style.cssText = 'width:12px;height:12px;border-radius:50%;background:var(--gold);';
            dots.appendChild(d);
        }
        if (n > 20) {
            const more = document.createElement('span');
            more.style.cssText = 'font-size:.75rem;color:var(--gray);align-self:center;';
            more.textContent = `+${n - 20} más`;
            dots.appendChild(more);
        }
        const cat = n <= 2 ? 'Íntima' : n <= 4 ? 'Pequeña' : n <= 6 ? 'Mediana' : 'Grande';
        text.textContent = `Mesa para ${n} personas · Categoría: ${cat}`;
    }

    input.addEventListener('input', updatePreview);
    updatePreview();
</script>
@endpush

@endsection