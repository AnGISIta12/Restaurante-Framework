@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Solicitar Reservación
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Por favor, completa los detalles para tu reservación.
            </p>
        </div>
        
        <div class="px-4 py-5 sm:p-6">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('reservaciones.guardarSolicitud') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    
                    <!-- Fecha de la reserva -->
                    <div>
                        <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha de la Reserva</label>
                        <input type="date" name="fecha" id="fecha" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Horario -->
                    <div>
                        <label for="hora" class="block text-sm font-medium text-gray-700">Horario</label>
                        <select id="hora" name="hora" required
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Selecciona un horario</option>
                            @foreach($horarios as $hora)
                                <option value="{{ $hora }}">{{ $hora }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Mesa -->
                    <div>
                        <label for="mesa_id" class="block text-sm font-medium text-gray-700">Mesa</label>
                        <select id="mesa_id" name="mesa_id" required
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Selecciona una mesa</option>
                            @foreach($mesas as $mesa)
                                <option value="{{ $mesa->id_mesa }}">Mesa {{ $mesa->id_mesa }} (Capacidad: {{ $mesa->sillas }} personas)</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cantidad de personas -->
                    <div>
                        <label for="cantidad" class="block text-sm font-medium text-gray-700">Cantidad de Personas</label>
                        <input type="number" name="cantidad" id="cantidad" min="1" max="20" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Ej. 4">
                    </div>

                </div>

                <div class="mt-8 border-t border-gray-200 pt-5">
                    <div class="flex justify-end">
                        <a href="{{ route('cliente.reservaciones') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Solicitar Reservación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
