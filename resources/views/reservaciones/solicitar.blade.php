@extends('layouts.app')
@section('title', 'Solicitar Reservación')

@section('content')

<div style="max-width:640px; margin:0 auto;">

    {{-- Header --}}
    <div style="margin-bottom:28px;">
        <h2 style="font-family:'Playfair Display',serif; font-size:1.4rem; color:var(--dark);">
            📅 Solicitar Reservación
        </h2>
        <p style="font-size:.82rem; color:var(--gray); margin-top:4px;">
            Elige la fecha, hora y mesa que prefieras. El maître confirmará tu reserva pronto.
        </p>
    </div>

    <div class="card">

        <form action="{{ route('reservaciones.guardarSolicitud') }}" method="POST">
            @csrf

            {{-- Fecha y Hora --}}
            <div style="margin-bottom:24px;">
                <div style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--gold); margin-bottom:14px;">
                    ① Fecha y hora
                </div>
                <div class="form-row">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="fecha">
                            Fecha <span style="color:var(--rust);">*</span>
                        </label>
                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            class="form-control"
                            value="{{ old('fecha') }}"
                            min="{{ now()->toDateString() }}"
                            required
                        >
                        @error('fecha') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label for="hora">
                            Horario <span style="color:var(--rust);">*</span>
                        </label>
                        <select id="hora" name="hora" class="form-control" required>
                            <option value="">— Seleccionar hora —</option>
                            @foreach($horarios as $hora)
                                <option value="{{ $hora }}" {{ old('hora') === $hora ? 'selected' : '' }}>
                                    {{ $hora }}
                                </option>
                            @endforeach
                        </select>
                        @error('hora') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div style="border-top:1px solid var(--border); margin-bottom:24px;"></div>

            {{-- Mesa y Personas --}}
            <div style="margin-bottom:24px;">
                <div style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--gold); margin-bottom:14px;">
                    ② Mesa y personas
                </div>
                <div class="form-row">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="mesa_id">
                            Mesa <span style="color:var(--rust);">*</span>
                        </label>
                        <select id="mesa_id" name="mesa_id" class="form-control" required>
                            <option value="">— Seleccionar mesa —</option>
                            @foreach($mesas as $mesa)
                                <option value="{{ $mesa->id_mesa }}" {{ old('mesa_id') == $mesa->id_mesa ? 'selected' : '' }}>
                                    Mesa {{ $mesa->id_mesa }} · {{ $mesa->sillas }} sillas
                                </option>
                            @endforeach
                        </select>
                        @error('mesa_id') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label for="cantidad">
                            Número de personas <span style="color:var(--rust);">*</span>
                        </label>
                        <input
                            type="number"
                            id="cantidad"
                            name="cantidad"
                            class="form-control"
                            value="{{ old('cantidad', 2) }}"
                            min="1"
                            max="20"
                            required
                            placeholder="Ej. 4"
                        >
                        @error('cantidad') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Resumen visual --}}
            <div id="resumen-box" style="
                background: var(--cream);
                border: 1px solid var(--border);
                border-radius: 10px;
                padding: 16px 20px;
                margin-bottom: 24px;
                display: flex;
                align-items: center;
                gap: 20px;
                flex-wrap: wrap;
            ">
                <div style="text-align:center; flex:1; min-width:100px;">
                    <div style="font-size:1.6rem; margin-bottom:2px;">📅</div>
                    <div id="res-fecha" style="font-size:.78rem; font-weight:600; color:var(--dark);">—</div>
                    <div style="font-size:.7rem; color:var(--gray);">Fecha</div>
                </div>
                <div style="width:1px; height:40px; background:var(--border);"></div>
                <div style="text-align:center; flex:1; min-width:100px;">
                    <div style="font-size:1.6rem; margin-bottom:2px;">🕐</div>
                    <div id="res-hora" style="font-size:.78rem; font-weight:600; color:var(--dark);">—</div>
                    <div style="font-size:.7rem; color:var(--gray);">Hora</div>
                </div>
                <div style="width:1px; height:40px; background:var(--border);"></div>
                <div style="text-align:center; flex:1; min-width:100px;">
                    <div style="font-size:1.6rem; margin-bottom:2px;">🪑</div>
                    <div id="res-mesa" style="font-size:.78rem; font-weight:600; color:var(--dark);">—</div>
                    <div style="font-size:.7rem; color:var(--gray);">Mesa</div>
                </div>
                <div style="width:1px; height:40px; background:var(--border);"></div>
                <div style="text-align:center; flex:1; min-width:100px;">
                    <div style="font-size:1.6rem; margin-bottom:2px;">👥</div>
                    <div id="res-personas" style="font-size:.78rem; font-weight:600; color:var(--dark);">—</div>
                    <div style="font-size:.7rem; color:var(--gray);">Personas</div>
                </div>
            </div>

            {{-- Aviso --}}
            <div style="
                background: #EFF6FF;
                border: 1px solid #93C5FD;
                border-radius: 8px;
                padding: 12px 16px;
                font-size: .8rem;
                color: #1E40AF;
                margin-bottom: 24px;
                display: flex;
                align-items: flex-start;
                gap: 10px;
            ">
                <span style="font-size:1rem; flex-shrink:0;">ℹ️</span>
                <span>Tu solicitud quedará en estado <strong>Pendiente</strong> hasta que el maître confirme y asigne tu mesa. Te notificaremos cuando esté lista.</span>
            </div>

            {{-- Botones --}}
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    Enviar solicitud
                </button>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
    const fechaInput    = document.getElementById('fecha');
    const horaSelect    = document.getElementById('hora');
    const mesaSelect    = document.getElementById('mesa_id');
    const cantidadInput = document.getElementById('cantidad');

    const resFecha    = document.getElementById('res-fecha');
    const resHora     = document.getElementById('res-hora');
    const resMesa     = document.getElementById('res-mesa');
    const resPersonas = document.getElementById('res-personas');

    function updateResumen() {
        // Fecha
        if (fechaInput.value) {
            const [y, m, d] = fechaInput.value.split('-');
            resFecha.textContent = `${d}/${m}/${y}`;
        } else {
            resFecha.textContent = '—';
        }

        // Hora
        resHora.textContent = horaSelect.value || '—';

        // Mesa
        const mesaOpt = mesaSelect.options[mesaSelect.selectedIndex];
        resMesa.textContent = mesaSelect.value ? mesaOpt.text.split('·')[0].trim() : '—';

        // Personas
        resPersonas.textContent = cantidadInput.value ? `${cantidadInput.value} pers.` : '—';
    }

    fechaInput.addEventListener('input', updateResumen);
    horaSelect.addEventListener('change', updateResumen);
    mesaSelect.addEventListener('change', updateResumen);
    cantidadInput.addEventListener('input', updateResumen);

    updateResumen();
</script>
@endpush

@endsection