<script>
// Ver expediente completo
async function verExpediente(solicitudId) {
    const modal = document.getElementById('modalExpediente');
    const content = document.getElementById('expedienteContent');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    try {
        const response = await fetch(`{{ url('/admin/solicitudes') }}/${solicitudId}`);
        const data = await response.json();
        
        if (data.success) {
            const s = data.solicitud;
            
            // Tipo de práctica
            const esNormal = s.tipo_practica === 'normal';
            const tipoPracticaLabel = esNormal ? 'Normal' : 'Por Trabajo';
            const tipoPracticaColor = esNormal ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800';
            
            // Campos específicos
            const camposEspecificos = esNormal ? `
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Fecha de inicio</p>
                    <p class="text-base text-gray-900">${s.fecha_inicio ? new Date(s.fecha_inicio).toLocaleDateString('es-HN') : 'N/A'}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Fecha de finalización</p>
                    <p class="text-base text-gray-900">${s.fecha_fin ? new Date(s.fecha_fin).toLocaleDateString('es-HN') : 'N/A'}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Horario</p>
                    <p class="text-base text-gray-900">${s.horario || 'N/A'}</p>
                </div>
            ` : `
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Puesto de trabajo</p>
                    <p class="text-base text-gray-900">${s.puesto_trabajo || 'N/A'}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Años trabajando</p>
                    <p class="text-base text-gray-900">${s.anios_trabajando || 'N/A'}</p>
                </div>
            `;

            // Supervisor
            const supervisorHTML = s.supervisor ? `
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Supervisor Asignado
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 font-semibold">Nombre</p>
                            <p class="text-base text-gray-900 font-bold">${s.supervisor.user.name}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-semibold">Email</p>
                            <p class="text-base text-gray-900">${s.supervisor.user.email}</p>
                        </div>
                    </div>
                </div>
            ` : '<div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg"><p class="text-yellow-800 font-semibold"> No hay supervisor asignado</p></div>';
            
            // Supervisiones
            let supervisionesHTML = '';
            if (s.supervisiones && s.supervisiones.length > 0) {
                supervisionesHTML = `
                    <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Supervisiones Realizadas (${s.supervisiones.length})
                        </h3>
                        <div class="space-y-4">
                             ${s.supervisiones.map((sup) => `
                            <div class="bg-white rounded-lg p-3 sm:p-4 border-l-4 border-green-500">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-2">
                                    <h4 class="font-bold text-gray-900 text-sm sm:text-base">Supervisión #${sup.numero_supervision}</h4>
                                    <span class="text-xs text-gray-500 self-start">${new Date(sup.created_at).toLocaleDateString('es-HN')}</span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-700 mb-3">${sup.comentario || 'Sin comentarios'}</p>
                                ${sup.archivo ? `
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <a href="{{ url('/admin/supervisiones') }}/${sup.id}/ver" target="_blank"
                                        class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Ver
                                        </a>
                                        <a href="{{ url('/admin/supervisiones') }}/${sup.id}/descargar" download
                                        class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Descargar
                                        </a>
                                    </div>
                                ` : '<p class="text-xs text-gray-500 italic">Sin archivo adjunto</p>'}
                            </div>
                        `).join('')}
                        </div>
                    </div>
                `;
            } else {
                supervisionesHTML = `
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                        <p class="text-yellow-800 font-semibold">No hay supervisiones registradas</p>
                    </div>
                `;
            }
            
            // Tipos de documentos
            const tiposDocumentos = {
                'colegiacion': 'Colegiación',
                'documento_ia01': 'Formato IA-01',
                'documento_ia02': 'Formato IA-02',
                'carta_aceptacion': 'Carta de Aceptación',
                'carta_presentacion': 'Carta de Presentación',
                'constancia_trabajo': 'Constancia de Trabajo',
                'constancia_aprobacion': 'Constancia de 100% Clases',
                'carta_finalizacion': 'Carta de Finalización'
            };

            // Agrupar documentos por tipo
            const docsIniciales = s.documentos.filter(d => ['carta_presentacion', 'carta_aceptacion', 'documento_ia01', 'documento_ia02', 'colegiacion', 'constancia_trabajo', 'constancia_aprobacion'].includes(d.tipo));
            const docsFinalizacion = s.documentos.filter(d => d.tipo === 'carta_finalizacion');
            const docsActualizacion = s.documentos.filter(d => d.tipo === 'actualizacion');
            
            content.innerHTML = `
                <!-- Alerta de Finalización -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-blue-800 font-bold text-lg mb-1">✓ Práctica Finalizada</p>
                            <p class="text-blue-700 text-sm">
                                Finalizada el: ${new Date(s.updated_at).toLocaleDateString('es-HN', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric'
                                })}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Información del Estudiante -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Información del Estudiante
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 font-semibold">Nombre completo</p>
                            <p class="text-base text-gray-900 font-bold">${s.user.name}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-semibold">Número de cuenta</p>
                            <p class="text-base text-gray-900 font-bold">${s.numero_cuenta || 'N/A'}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-sm text-gray-600 font-semibold">Correo electrónico</p>
                            <p class="text-base text-gray-900">${s.user.email}</p>
                        </div>
                    </div>
                </div>

                <!-- Supervisor -->
                ${supervisorHTML}

                <!-- Información de la Práctica -->
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Información de la Empresa
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <p class="text-sm text-gray-600 font-semibold">Tipo de práctica</p>
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full ${tipoPracticaColor}">${tipoPracticaLabel}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-sm text-gray-600 font-semibold">Nombre de la empresa</p>
                            <p class="text-base text-gray-900">${s.nombre_empresa || 'N/A'}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-sm text-gray-600 font-semibold">Dirección</p>
                            <p class="text-base text-gray-900">${s.direccion_empresa || 'N/A'}</p>
                        </div>
                        ${camposEspecificos}
                    </div>
                </div>

                <!-- Supervisiones -->
                ${supervisionesHTML}

                <!-- Documentos Iniciales -->
                <div class="bg-white rounded-xl p-6 border-2 border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Documentos Iniciales (${docsIniciales.length})
                    </h3>
                    ${docsIniciales.length > 0 ? `
                        <div class="space-y-3">
                            ${docsIniciales.map(doc => {
                                const nombreDoc = tiposDocumentos[doc.tipo] || doc.tipo.replace(/_/g, ' ');
                                return `
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 transition">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800">${nombreDoc}</p>
                                            <p class="text-xs text-gray-500">${new Date(doc.created_at).toLocaleDateString('es-HN')}</p>
                                        </div>
                                    </div>
                                    <a href="{{ url('/estudiantes/documentos') }}/${doc.id}/ver" target="_blank"
                                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                                        Ver
                                    </a>
                                </div>
                            `}).join('')}
                        </div>
                    ` : '<p class="text-gray-500 text-center py-8">No hay documentos iniciales</p>'}
                </div>

                <!-- Documentos de Actualización -->
                ${docsActualizacion.length > 0 ? `
                <div class="bg-purple-50 rounded-xl p-6 border border-purple-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Documentos de Actualización (${docsActualizacion.length})
                    </h3>
                    <div class="space-y-3">
                        ${docsActualizacion.map((doc, index) => `
                            <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-purple-200">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">Actualización #${index + 1}</p>
                                        <p class="text-xs text-gray-500">${new Date(doc.created_at).toLocaleDateString('es-HN')}</p>
                                    </div>
                                </div>
                                <a href="{{ url('/estudiantes/documentos') }}/${doc.id}/ver" target="_blank"
                                   class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-semibold">
                                    Ver
                                </a>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}

                <!-- Carta de Finalización -->
                ${docsFinalizacion.length > 0 ? `
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border-2 border-green-500">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        Carta de Finalización
                    </h3>
                    <div class="space-y-3">
                        ${docsFinalizacion.map(doc => `
                            <div class="flex items-center justify-between p-4 bg-white rounded-lg border-2 border-green-300">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">Carta de Finalización de Práctica</p>
                                        <p class="text-xs text-gray-600">Subida el ${new Date(doc.created_at).toLocaleDateString('es-HN')}</p>
                                    </div>
                                </div>
                                <a href="{{ url('/estudiantes/documentos') }}/${doc.id}/ver" target="_blank"
                                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-semibold shadow-lg">
                                    Ver Carta
                                </a>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-600 font-semibold">Error al cargar el expediente</p>
            </div>
        `;
    }
}

// Cerrar modal
function cerrarModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Cerrar al hacer click fuera
document.querySelectorAll('[id^="modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModal(this.id);
        }
    });
});
</script>

<style>
/* Line clamp */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Animación fade-in */
@keyframes fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

/* Scrollbar personalizada */
#modalExpediente > div {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.4) transparent;
}

#modalExpediente > div::-webkit-scrollbar {
    width: 6px;
}

#modalExpediente > div::-webkit-scrollbar-track {
    background: transparent;
}

#modalExpediente > div::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.4);
    border-radius: 10px;
}

#modalExpediente > div::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.6);
}
</style>