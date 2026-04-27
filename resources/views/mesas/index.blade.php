@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800">Estado de Mesas</h1>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-700">
            Total: {{ count($mesas) }}
        </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse ($mesas as $mesa)
            @php
                $estado = strtolower($mesa->estado ?? 'desconocido');

                $estadoClasses = match ($estado) {
                    'disponible' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                    'ocupada' => 'bg-red-50 border-red-200 text-red-700',
                    'reservada' => 'bg-amber-50 border-amber-200 text-amber-700',
                    default => 'bg-gray-50 border-gray-200 text-gray-700',
                };
            @endphp

            <div class="rounded-lg border shadow-sm bg-white overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Mesa #{{ $mesa->id }}</h2>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $estadoClasses }}">
                        {{ ucfirst($estado) }}
                    </span>
                </div>

                <div class="p-5 space-y-2">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium text-gray-800">Capacidad:</span>
                        {{ $mesa->capacidad }} personas
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium text-gray-800">Estado actual:</span>
                        {{ ucfirst($estado) }}
                    </p>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-gray-500">
                    No hay mesas registradas para mostrar.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
