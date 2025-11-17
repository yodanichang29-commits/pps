@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100 py-4 sm:py-6 lg:py-8 px-3 sm:px-4 lg:px-6">
    <div class="max-w-7xl mx-auto space-y-4 sm:space-y-6">

        {{-- ============================================
             SECCIÓN 1: HEADER Y CONTADORES
             ============================================ --}}
        <div class="flex flex-col gap-3 sm:gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-unahblue">Solicitudes Finalizadas</h1>
                <p class="text-sm sm:text-base text-gray-600 mt-1">Archivo completo de prácticas profesionales completadas</p>
            </div>
            
            {{-- Contadores --}}
            <div class="grid grid-cols-2 sm:flex sm:flex-row gap-3">
                <div class="px-4 py-3 bg-yellow-100 rounded-xl border-2 border-yellow-200">
                    <p class="text-xs text-yellow-600 font-semibold uppercase">Pendientes</p>
                    <p class="text-2xl sm:text-3xl font-bold text-yellow-700">{{ $contadores['pendientes'] }}</p>
                </div>
                <div class="px-4 py-3 bg-green-100 rounded-xl border-2 border-green-200">
                    <p class="text-xs text-green-600 font-semibold uppercase">Aprobadas</p>
                    <p class="text-2xl sm:text-3xl font-bold text-green-700">{{ $contadores['aprobadas'] }}</p>
                </div>
                <div class="px-4 py-3 bg-red-100 rounded-xl border-2 border-red-200 hidden sm:block">
                    <p class="text-xs text-red-600 font-semibold uppercase">Rechazadas</p>
                    <p class="text-2xl sm:text-3xl font-bold text-red-700">{{ $contadores['rechazadas'] }}</p>
                </div>
                <div class="px-4 py-3 bg-blue-100 rounded-xl border-2 border-blue-200">
                    <p class="text-xs text-blue-600 font-semibold uppercase">Finalizadas</p>
                    <p class="text-2xl sm:text-3xl font-bold text-blue-700">{{ $contadores['finalizadas'] }}</p>
                </div>
            </div>
        </div>

        {{-- ============================================
             SECCIÓN 2: ALERTAS
             ============================================ --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-3 sm:p-4 rounded-lg shadow-md animate-fade-in">
                <div class="flex items-start">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-500 mt-0.5 mr-2 sm:mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    <p class="text-green-800 font-medium text-sm sm:text-base">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- ============================================
             SECCIÓN 3: BUSCADOR
             ============================================ --}}
        <div class="bg-white rounded-xl shadow-lg p-3 sm:p-4">
            <form method="GET" action="{{ route('admin.solicitudes.finalizadas') }}" class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <input type="text" name="busqueda" value="{{ request('busqueda') }}" placeholder="Buscar por nombre, correo o cuenta..."
                    class="flex-1 px-3 sm:px-4 py-2 text-sm sm:text-base border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-none px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-sm sm:text-base">
                        Buscar
                    </button>
                    @if(request('busqueda'))
                        <a href="{{ route('admin.solicitudes.finalizadas') }}" class="flex-1 sm:flex-none px-4 sm:px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold text-sm sm:text-base text-center">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- ============================================
             SECCIÓN 4: TABLA DE SOLICITUDES FINALIZADAS
             ============================================ --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            @if($solicitudes->count() > 0)
                
                {{-- DESKTOP: Tabla completa --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">Estudiante</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">Empresa</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase">Supervisor</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase">Supervisiones</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase">Documentos</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase">Fecha Finalización</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($solicitudes as $solicitud)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-gray-900">{{ $solicitud->user->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $solicitud->user->email }}</p>
                                        <p class="text-xs text-gray-500">{{ $solicitud->numero_cuenta }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-gray-900">{{ $solicitud->nombre_empresa }}</p>
                                        <p class="text-xs text-gray-600">{{ $solicitud->tipo_practica === 'normal' ? 'Práctica Normal' : 'Por Trabajo' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($solicitud->supervisor)
                                            <p class="text-sm font-semibold text-gray-900">{{ $solicitud->supervisor->user->name }}</p>
                                            <p class="text-xs text-gray-600">{{ $solicitud->supervisor->user->email }}</p>
                                        @else
                                            <span class="text-gray-400 text-sm italic">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $totalSupervisiones = $solicitud->supervisiones->count();
                                        @endphp
                                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $totalSupervisiones >= 2 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $totalSupervisiones }} supervisión{{ $totalSupervisiones != 1 ? 'es' : '' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span class="text-sm font-semibold text-gray-700">{{ $solicitud->documentos->count() }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <p class="text-sm text-gray-900">{{ $solicitud->updated_at->format('d/m/Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ $solicitud->updated_at->format('h:i A') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="verExpediente({{ $solicitud->id }})" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-sm font-semibold">
                                                Ver Expediente
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE: Cards --}}
                <div class="lg:hidden divide-y divide-gray-200">
                    @foreach($solicitudes as $solicitud)
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-900 truncate">{{ $solicitud->user->name }}</p>
                                    <p class="text-sm text-gray-600 truncate">{{ $solicitud->nombre_empresa }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Finalizada: {{ $solicitud->updated_at->format('d/m/Y') }}
                                    </p>
                                </div>
                                <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 flex-shrink-0">
                                    ✓ Finalizada
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div class="p-2 bg-green-50 rounded-lg text-center">
                                    <p class="text-xs text-green-600 font-semibold">Supervisiones</p>
                                    <p class="text-lg font-bold text-green-800">{{ $solicitud->supervisiones->count() }}</p>
                                </div>
                                <div class="p-2 bg-blue-50 rounded-lg text-center">
                                    <p class="text-xs text-blue-600 font-semibold">Documentos</p>
                                    <p class="text-lg font-bold text-blue-800">{{ $solicitud->documentos->count() }}</p>
                                </div>
                            </div>
                            
                            <button onclick="verExpediente({{ $solicitud->id }})" class="w-full px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-sm font-semibold">
                                Ver Expediente Completo
                            </button>
                        </div>
                    @endforeach
                </div>

                {{-- Paginación --}}
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                    {{ $solicitudes->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-600 font-semibold">No hay solicitudes finalizadas</p>
                    <p class="text-gray-500 text-sm mt-1">Las prácticas completadas aparecerán aquí</p>
                </div>
            @endif
        </div>

    </div>
</div>

{{-- Modales --}}
@include('admin.solicitudes.modalsFinalizadas')

{{-- Scripts --}}
@include('admin.solicitudes.scriptsFinalizadas')

@endsection