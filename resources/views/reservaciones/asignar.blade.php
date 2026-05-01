@extends('layouts.app')

@section('title', 'Asignar Mesa')

@section('content')

<div style="margin-bottom:24px;">
    <h2 style="font-family:'Playfair Display',serif; font-size:1.4rem; color:var(--dark);">
        🗺️ Asignar Mesa a Reservaciones
    </h2>
    <p style="font-size:.82rem; color:var(--gray); margin-top:4px;">
        Reservaciones pendientes de asignación de mesa.
    </p>
</div>

@if($reservaciones->isEmpty())
    <div class="card" style="text-align:center; padding:50px; color:var(--gray);">
        <p style="font-size:2rem; margin-bottom:12px;">✅</p>
        <p><strong>¡Todo asignado!</strong> No hay reservaciones pendientes de mesa.</p>
        <a href="{{ route('reservaciones.proximas') }}" class="btn btn-secondary" style="margin-top:16px;">
            Ver próximas reservaciones
        </a>
    </div>
@else
    <div style="display:flex; flex-direction:column; gap:16px;">
        @foreach($reservaciones as $reservacion)
        @php
            $mesas = $mesasPorReservacion[$reservacion->id_reservacion] ?? collect();
        @endphp
        <div class="card">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap;">

                {{-- Info reservación --}}
                <div style="flex:1; min-width:200px;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                        <span style="
                            background:var(--cream); border:1px solid var(--border);
                            border-radius:8px; padding:4px 12px;
                            font-size:.78rem; font-weight:700; color:var(--gray);
                        ">
                            #{{ str_pad($reservacion->id_reservacion, 4, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="badge badge-pending">
                            {{ $reservacion->getEtiquetaEstado() }}
                        </span>
                    </div>
                    <div style="font-weight:600; color:var(--dark); font-size:.95rem;">
                        👤 {{ $reservacion->cliente->nombre ?? 'Cliente' }}
                    </div>
                    <div style="font-size:.82rem; color:var(--gray); margin-top:4px;">
                        👥 {{ $reservacion->cantidad }} {{ $reservacion->cantidad == 1 ? 'persona' : 'personas' }}
                    </div>
                </div>

                {{-- Formulario de asignación --}}
                <div style="flex:1; min-width:220px;">
                    @if($mesas->isEmpty())
                        <div class="alert alert-error" style="margin:0; font-size:.82rem;">
                            ⚠️ No hay mesas disponibles con capacidad para
                            {{ $reservacion->cantidad }} personas en este momento.
                        </div>
                    @else
                        <form method="POST"
                              action="{{ route('reservaciones.guardarAsignacion') }}"
                              style="display:flex; gap:8px; align-items:flex-start;">
                            @csrf
                            <input type="hidden" name="reservacion_id" value="{{ $reservacion->id_reservacion }}">

                            <div style="flex:1;">
                                <label style="font-size:.78rem; font-weight:600; color:var(--gray); display:block; margin-bottom:4px;">
                                    Mesa disponible
                                </label>
                                <select name="mesa_id" class="form-control" required>
                                    @foreach($mesas as $mesa)
                                        <option value="{{ $mesa->id_mesa }}">
                                            Mesa {{ $mesa->id_mesa }} — {{ $mesa->sillas }} sillas
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-sage"
                                    style="margin-top:22px; white-space:nowrap;">
                                Asignar ✓
                            </button>
                        </form>
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>
@endif

@endsection
