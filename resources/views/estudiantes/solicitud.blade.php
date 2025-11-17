@extends('layouts.estudiantes')

@section('content')

@php
    $activa = isset($activa)
        ? $activa
        : (isset($solicitud) && $solicitud && in_array($solicitud->estado_solicitud, ['SOLICITADA','APROBADA']));
@endphp

@if($activa)
    {{-- MENSAJE DE SOLICITUD EN PROCESO --}}
    <div class="min-h-screen bg-gray-100 py-12 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-blue-100">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold text-center text-gray-800 mb-3">¡Solicitud Enviada!</h1>
                <p class="text-center text-gray-600 mb-8">Tu solicitud está en proceso de revisión por el equipo administrativo.</p>
                
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-amber-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                        </svg>
                        <p class="text-sm text-amber-700">Por favor espera la validación de tus documentos. Te notificaremos cuando haya actualizaciones.</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('estudiantes.dashboard') }}" 
                       class="flex-1 text-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-medium hover:from-blue-700 hover:to-indigo-700 transform transition hover:scale-105 shadow-lg">
                        Ver Dashboard
                    </a>
                    @if(!empty($solicitud?->id))
                        <a href="{{ route('estudiantes.solicitudes.documentos', $solicitud->id) }}"
                           class="flex-1 text-center px-6 py-3 bg-white border-2 border-blue-600 text-blue-600 rounded-xl font-medium hover:bg-blue-50 transform transition hover:scale-105">
                            Ver Documentos
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    {{-- FORMULARIO MODERNO --}}
    <div class="min-h-screen bg-gray-100 py-12 px-4">
        <div class="max-w-5xl mx-auto">
            
            {{-- Header --}}
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-unahblue mb-3">Solicitud de Práctica Profesional</h1>
                <p class="text-gray-600">Completa todos los campos para enviar tu solicitud</p>
            </div>

            {{-- Progress Steps --}}
            <div class="mb-12">
                <div class="flex items-center justify-center">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center step-indicator" data-step="1">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full text-white font-bold shadow-lg step-circle">
                                1
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-700 step-text">Tipo de Práctica</span>
                        </div>
                        <div class="w-24 h-1 bg-gray-300 step-line"></div>
                        <div class="flex items-center step-indicator" data-step="2">
                            <div class="flex items-center justify-center w-10 h-10 bg-gray-300 rounded-full text-white font-bold step-circle">
                                2
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-400 step-text">Información</span>
                        </div>
                        <div class="w-24 h-1 bg-gray-300 step-line"></div>
                        <div class="flex items-center step-indicator" data-step="3">
                            <div class="flex items-center justify-center w-10 h-10 bg-gray-300 rounded-full text-white font-bold step-circle">
                                3
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-400 step-text">Documentos</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Error Alert --}}
            <div id="errorAlert" class="hidden max-w-5xl mx-auto mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                    </svg>
                    <div>
                        <p class="font-medium text-red-800">Error en el formulario</p>
                        <p id="errorMessage" class="text-sm text-red-700 mt-1"></p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('estudiantes.solicitud.store') }}" enctype="multipart/form-data" id="ppsForm">
                @csrf

                {{-- STEP 1: Tipo de Práctica --}}
                <div class="step-content" id="step1">
                    <div class="bg-white rounded-2xl shadow-xl p-8 border border-blue-100 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <span class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white mr-3">1</span>
                            Tipo de Práctica
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Opción Normal --}}
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="tipo_practica" value="normal" required class="peer sr-only" onchange="mostrarCampos()">
                                <div class="p-6 border-2 border-gray-200 rounded-2xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 hover:shadow-lg">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div class="w-6 h-6 border-2 border-gray-300 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-2">Práctica Normal</h3>
                                    <p class="text-gray-600 text-sm">Práctica en empresa externa con supervisión académica.</p>
                                </div>
                            </label>

                            {{-- Opción Trabajo --}}
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="tipo_practica" value="trabajo" required class="peer sr-only" onchange="mostrarCampos()">
                                <div class="p-6 border-2 border-gray-200 rounded-2xl transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-purple-300 hover:shadow-lg">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                        <div class="w-6 h-6 border-2 border-gray-300 rounded-full peer-checked:border-purple-500 peer-checked:bg-purple-500 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-2">Por Trabajo</h3>
                                    <p class="text-gray-600 text-sm">Validación de experiencia laboral actual como práctica profesional.</p>
                                </div>
                            </label>
                        </div>

                        {{-- Modalidad (solo normal) --}}
                        <div id="modalidad_fields" class="hidden mt-8">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Modalidad de Trabajo <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="modalidad" value="presencial" class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 rounded-xl text-center transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-600 peer-checked:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span class="font-medium text-gray-700">Presencial</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="modalidad" value="semipresencial" class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 rounded-xl text-center transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="font-medium text-gray-700">Semipresencial</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="modalidad" value="teletrabajo" class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 rounded-xl text-center transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="font-medium text-gray-700">Teletrabajo</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Información --}}
                <div class="step-content hidden" id="step2">
                    <div class="bg-white rounded-2xl shadow-xl p-8 border border-blue-100 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <span class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white mr-3">2</span>
                            Información General
                        </h2>

                        {{-- Información Personal --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Datos del Estudiante
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Número de Cuenta <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                            </svg>
                                        </div>
                                        <input type="text" name="numero_cuenta" required 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                               placeholder="20201234567">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Información Empresa --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Datos de la Empresa
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Empresa <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                        <input type="text" name="nombre_empresa" required 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                               placeholder="Sin abreviaturas: Ej. Tech Solutions S.A.">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Dirección <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                        <input type="text" name="direccion_empresa" required 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                               placeholder="Dirección completa">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Información Jefe --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Datos del Jefe Inmediato
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <input type="text" name="nombre_jefe" required 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                               placeholder="Juan Pérez">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                        </div>
                                        <input type="text" name="numero_jefe" required 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                               placeholder="9999-9999">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <input type="email" name="correo_jefe" required 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                               placeholder="jefe@empresa.com">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Campos específicos TRABAJO --}}
                        <div id="trabajo_fields" class="hidden mb-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Información Laboral
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Puesto de Trabajo <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <input type="text" name="puesto_trabajo" 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                                               placeholder="Desarrollador Full Stack">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Años Trabajando <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <input type="number" name="anios_trabajando" min="0" 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                                               placeholder="2">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Campos específicos NORMAL --}}
                        <div id="normal_fields" class="hidden mb-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Período de Práctica
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Inicio <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <input type="date" name="fecha_inicio" 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Finalización prevista<span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                            </svg>
                                        </div>
                                        <input type="date" name="fecha_fin" 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Horario <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <input type="text" name="horario" 
                                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                               placeholder="8:00 AM - 5:00 PM">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones (Opcional)</label>
                            <textarea name="observaciones" rows="4" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                      placeholder="Agrega cualquier información adicional relevante...">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: Documentos --}}
                <div class="step-content hidden" id="step3">
                    <div class="bg-white rounded-2xl shadow-xl p-8 border border-blue-100 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <span class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white mr-3">3</span>
                            Documentos de Respaldo
                        </h2>

                        <p class="text-gray-600 mb-6"><span class="text-red-500 font-semibold">Importante:</span> Debes subir al menos un documento para continuar.</p>

                        {{-- Docs Normal --}}
                        <div id="docs_normal" class="hidden space-y-6">
                            <div class="file-upload-box" data-name="colegiacion">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Colegiación (PDF) <span class="text-red-500">*</span></label>
                                <div class="file-drop-area border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all hover:border-blue-400 hover:bg-blue-50 cursor-pointer">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-gray-600 mb-1">Arrastra tu archivo aquí o <span class="text-blue-600 font-medium">haz clic para seleccionar</span></p>
                                    <p class="text-sm text-gray-400">PDF - Máximo 5MB</p>
                                    <input type="file" name="colegiacion" accept="application/pdf" class="file-input">
                                </div>
                            </div>

                            <div class="file-upload-box" data-name="documento_ia01">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Formato IA-01 (PDF) <span class="text-red-500">*</span></label>
                                <div class="file-drop-area border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all hover:border-blue-400 hover:bg-blue-50 cursor-pointer">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-gray-600 mb-1">Arrastra tu archivo aquí o <span class="text-blue-600 font-medium">haz clic para seleccionar</span></p>
                                    <p class="text-sm text-gray-400">PDF - Máximo 5MB</p>
                                    <input type="file" name="documento_ia01" accept="application/pdf" class="file-input">
                                </div>
                            </div>

                            <div class="file-upload-box" data-name="carta_aceptacion">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Carta de Aceptación (PDF) <span class="text-red-500">*</span></label>
                                <div class="file-drop-area border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all hover:border-blue-400 hover:bg-blue-50 cursor-pointer">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-gray-600 mb-1">Arrastra tu archivo aquí o <span class="text-blue-600 font-medium">haz clic para seleccionar</span></p>
                                    <p class="text-sm text-gray-400">PDF - Máximo 5MB</p>
                                    <input type="file" name="carta_aceptacion" accept="application/pdf" class="file-input">
                                </div>
                            </div>

                            <div class="file-upload-box" data-name="carta_presentacion">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Carta de Presentación (PDF) <span class="text-red-500">*</span></label>
                                <div class="file-drop-area border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all hover:border-blue-400 hover:bg-blue-50 cursor-pointer">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-gray-600 mb-1">Arrastra tu archivo aquí o <span class="text-blue-600 font-medium">haz clic para seleccionar</span></p>
                                    <p class="text-sm text-gray-400">PDF - Máximo 5MB</p>
                                    <input type="file" name="carta_presentacion" accept="application/pdf" class="file-input">
                                </div>
                            </div>
                        </div>

                        {{-- Docs Trabajo --}}
                        <div id="docs_trabajo" class="hidden space-y-6">
                            <div class="file-upload-box" data-name="colegiacion">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Colegiación (PDF) <span class="text-red-500">*</span></label>
                                <div class="file-drop-area border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all hover:border-purple-400 hover:bg-purple-50 cursor-pointer">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-gray-600 mb-1">Arrastra tu archivo aquí o <span class="text-purple-600 font-medium">haz clic para seleccionar</span></p>
                                    <p class="text-sm text-gray-400">PDF - Máximo 5MB</p>
                                    <input type="file" name="colegiacion" accept="application/pdf" class="file-input">
                                </div>
                            </div>

                            <div class="file-upload-box" data-name="documento_ia02">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Formato IA-02 (PDF) <span class="text-red-500">*</span></label>
                                <div class="file-drop-area border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all hover:border-purple-400 hover:bg-purple-50 cursor-pointer">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-gray-600 mb-1">Arrastra tu archivo aquí o <span class="text-purple-600 font-medium">haz clic para seleccionar</span></p>
                                    <p class="text-sm text-gray-400">PDF - Máximo 5MB</p>
                                    <input type="file" name="documento_ia02" accept="application/pdf" class="file-input">
                                </div>
                            </div>

                            <div class="file-upload-box" data-name="constancia_trabajo">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Constancia de Trabajo (PDF) <span class="text-red-500">*</span></label>
                                <div class="file-drop-area border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all hover:border-purple-400 hover:bg-purple-50 cursor-pointer">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-gray-600 mb-1">Arrastra tu archivo aquí o <span class="text-purple-600 font-medium">haz clic para seleccionar</span></p>
                                    <p class="text-sm text-gray-400">PDF - Máximo 5MB</p>
                                    <input type="file" name="constancia_trabajo" accept="application/pdf" class="file-input">
                                </div>
                            </div>

                            <div class="file-upload-box" data-name="constancia_aprobacion">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Constancia de 100% Clases (PDF) <span class="text-red-500">*</span></label>
                                <div class="file-drop-area border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all hover:border-purple-400 hover:bg-purple-50 cursor-pointer">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-gray-600 mb-1">Arrastra tu archivo aquí o <span class="text-purple-600 font-medium">haz clic para seleccionar</span></p>
                                    <p class="text-sm text-gray-400">PDF - Máximo 5MB</p>
                                    <input type="file" name="constancia_aprobacion" accept="application/pdf" class="file-input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Navigation Buttons --}}
                <div class="flex justify-between items-center max-w-5xl mx-auto mt-8">
                    <button type="button" id="prevBtn" onclick="changeStep(-1)" 
                            class="px-8 py-3 bg-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300 transition-all hidden">
                        ← Anterior
                    </button>
                    <button type="button" id="nextBtn" onclick="changeStep(1)" 
                            class="ml-auto px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-medium hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                        Siguiente →
                    </button>
                    <button type="submit" id="submitBtn" 
                            class="ml-auto px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-medium hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg hidden">
                        ✓ Enviar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<style>
.file-input {
    display: none;
}

.file-drop-area.drag-over {
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
}

.file-drop-area.has-file {
    border-color: #10b981 !important;
    background-color: #d1fae5 !important;
}
</style>


<script src="{{ asset('js/solicitud-form.js') }}"></script>

@endsection