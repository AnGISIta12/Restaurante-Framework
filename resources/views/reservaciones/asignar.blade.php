@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-semibold text-gray-800">Asignar Mesa a Reservación</h1>
            <p class="mt-1 text-sm text-gray-500">Selecciona una mesa disponible para completar la asignación.</p>
        </div>

        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-4">
                    <p class="text-sm font-medium text-red-700 mb-2">Se encontraron errores:</p>
                    <ul class="list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url()->current() }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="mesa_id" class="block text-sm font-medium text-gray-700 mb-2">Mesa disponible</label>
                    <select
                        name="mesa_id"
                        id="mesa_id"
                        required
                        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="" disabled selected>Selecciona una mesa</option>
                        @foreach ($mesas as $mesa)
                            <option value="{{ $mesa->id }}" {{ old('mesa_id') == $mesa->id ? 'selected' : '' }}>
                                Mesa #{{ $mesa->id }} - Capacidad: {{ $mesa->capacidad }} personas
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-md bg-green-600 text-white text-sm font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    >
                        Asignar mesa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
