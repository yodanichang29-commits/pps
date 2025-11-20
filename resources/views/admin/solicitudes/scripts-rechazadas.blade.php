<script>
// ============================================
// FUNCIÓN: Ver detalle de solicitud
// ============================================
async function verDetalle(solicitudId) {
    const modal = document.getElementById('modalDetalle');
    const content = document.getElementById('detalleContent');
    
    // Mostrar modal con loading
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
            
            // Determinar tipo de práctica
            const esNormal = s.tipo_practica === 'normal';
            const tipoPracticaLabel = esNormal ? 'Normal' : 'Por Trabajo';
            const tipoPracticaColor = esNormal ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800';
            
            // Campos específicos según tipo
            const camposEspecificos = esNormal ? `
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Fecha de inicio</p>
                    <p class="text-base text-gray-900">${s.fecha_inicio ? new Date(s.fecha_inicio).toLocaleDateString('es-HN') : 'N/A'}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Fecha de finalización</p>
                    <p class="text-base text-gray-900">${s.fecha_fin ? new Date(s.fecha_fin).toLocaleDateString('es-HN') : 'N/A'}</p>
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
            
            content.innerHTML = `
                <!-- Alerta de Rechazo -->
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-red-800 font-bold text-lg mb-2">Solicitud Rechazada</p>
                            <p class="text-red-700 font-semibold text-sm mb-1">Motivo del rechazo:</p>
                            <p class="text-red-700 text-sm whitespace-pre-wrap">${s.observaciones || 'No especificado'}</p>
                            <p class="text-red-600 text-xs mt-2">
                                Rechazada el: ${new Date(s.updated_at).toLocaleDateString('es-HN', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
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
                        <div>
    <p class="text-sm text-gray-600 font-semibold">Celular</p>
    <p class="text-base text-gray-900 font-bold">${s.celular || 'N/A'}</p>
</div>
                        <div class="sm:col-span-2">
                            <p class="text-sm text-gray-600 font-semibold">Correo electrónico</p>
                            <p class="text-base text-gray-900">${s.user.email}</p>
                        </div>
                    </div>
                </div>

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
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full ${tipoPracticaColor}">
                                ${tipoPracticaLabel}
                            </span>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-sm text-gray-600 font-semibold">Nombre de la empresa</p>
                            <p class="text-base text-gray-900">${s.nombre_empresa || 'N/A'}</p>
                        </div>
                        ${camposEspecificos}
                    </div>
                </div>

                <!-- Documentos -->
                <div class="bg-white rounded-xl p-6 border-2 border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Documentos Adjuntos (${s.documentos.length})
                    </h3>
                    ${s.documentos.length > 0 ? `
                        <div class="space-y-3">
                            ${s.documentos.map(doc => {
                                const tiposDocumentos = {
                                    'colegiacion': 'Colegiación',
                                    'documento_ia01': 'Formato IA-01',
                                    'documento_ia02': 'Formato IA-02',
                                    'carta_aceptacion': 'Carta de Aceptación',
                                    'carta_presentacion': 'Carta de Presentación',
                                    'constancia_trabajo': 'Constancia de Trabajo',
                                    'constancia_aprobacion': 'Constancia de 100% Clases'
                                };
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
                    ` : '<p class="text-gray-500 text-center py-8">No hay documentos adjuntos</p>'}
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-600 font-semibold">Error al cargar los detalles</p>
            </div>
        `;
    }
}

// ============================================
// FUNCIÓN: Ver motivo completo
// ============================================
async function verMotivo(solicitudId) {
    const modal = document.getElementById('modalMotivo');
    const motivoTexto = document.getElementById('motivoTexto');
    
    try {
        const response = await fetch(`{{ url('/admin/solicitudes') }}/${solicitudId}`);
        const data = await response.json();
        
        if (data.success && data.solicitud.observaciones) {
            motivoTexto.textContent = data.solicitud.observaciones;
            modal.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar el motivo del rechazo');
    }
}

// ============================================
// FUNCIÓN: Cerrar modal
// ============================================
function cerrarModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// ============================================
// EVENTO: Cerrar modal al hacer clic fuera
// ============================================
document.querySelectorAll('[id^="modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModal(this.id);
        }
    });
});
</script>

<style>
/* Scrollbar personalizada */
#modalDetalle > div,
#modalMotivo > div {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.4) transparent;
}

#modalDetalle > div::-webkit-scrollbar,
#modalMotivo > div::-webkit-scrollbar {
    width: 6px;
}

#modalDetalle > div::-webkit-scrollbar-track,
#modalMotivo > div::-webkit-scrollbar-track {
    background: transparent;
}

#modalDetalle > div::-webkit-scrollbar-thumb,
#modalMotivo > div::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.4);
    border-radius: 10px;
}

/* Line clamp para texto largo */
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
</style>