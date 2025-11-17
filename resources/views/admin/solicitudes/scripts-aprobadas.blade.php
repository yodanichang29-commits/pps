<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let supervisoresData = [];
let supervisorActualId = null;
let solicitudActual = null;

// ============================================
// CARGAR SUPERVISORES AL INICIAR
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    cargarSupervisores();
});

// ============================================
// FUNCIÓN: Cargar supervisores disponibles
// ============================================
async function cargarSupervisores() {
    try {
        const response = await fetch('{{ route("admin.supervisores.disponibles") }}');
        const data = await response.json();
        
        if (data.success) {
            supervisoresData = data.supervisores;
        } else {
            console.error('Error al cargar supervisores:', data.message);
        }
    } catch (error) {
        console.error('Error en la petición:', error);
    }
}

// ============================================
// FUNCIÓN: Ver detalle de solicitud
// ============================================
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
            
            // Modalidad (solo para normal)
            const modalidadHTML = s.modalidad ? `
                <div>
                    <p class="text-xs sm:text-sm text-gray-600 font-semibold">Modalidad</p>
                    <p class="text-sm sm:text-base text-gray-900 capitalize">${s.modalidad}</p>
                </div>
            ` : '';
            
            // Campos específicos según tipo
            const camposEspecificos = esNormal ? `
                <div>
                    <p class="text-xs sm:text-sm text-gray-600 font-semibold">Fecha de inicio</p>
                    <p class="text-sm sm:text-base text-gray-900">${s.fecha_inicio ? new Date(s.fecha_inicio).toLocaleDateString('es-HN') : 'N/A'}</p>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-600 font-semibold">Fecha de finalización</p>
                    <p class="text-sm sm:text-base text-gray-900">${s.fecha_fin ? new Date(s.fecha_fin).toLocaleDateString('es-HN') : 'N/A'}</p>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-600 font-semibold">Horario</p>
                    <p class="text-sm sm:text-base text-gray-900">${s.horario || 'N/A'}</p>
                </div>
            ` : `
                <div>
                    <p class="text-xs sm:text-sm text-gray-600 font-semibold">Puesto de trabajo</p>
                    <p class="text-sm sm:text-base text-gray-900">${s.puesto_trabajo || 'N/A'}</p>
                </div>
                <div>
                    <p class="text-xs sm:text-sm text-gray-600 font-semibold">Años trabajando</p>
                    <p class="text-sm sm:text-base text-gray-900">${s.anios_trabajando || 'N/A'}</p>
                </div>
            `;

            // Información del supervisor
            const supervisorHTML = s.supervisor ? `
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4 sm:p-6 border border-purple-200">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Supervisor Asignado
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Nombre</p>
                            <p class="text-sm sm:text-base text-gray-900 font-bold">${s.supervisor.user.name}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Email</p>
                            <p class="text-sm sm:text-base text-gray-900 break-all">${s.supervisor.user.email}</p>
                        </div>
                    </div>
                </div>
            ` : `
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                    <p class="text-yellow-800 font-semibold">⚠️ No hay supervisor asignado</p>
                </div>
            `;
            
            // Supervisiones
            let supervisionesHTML = '';
            if (s.supervisiones && s.supervisiones.length > 0) {
                supervisionesHTML = `
                    <div class="bg-green-50 rounded-xl p-4 sm:p-6 border border-green-200">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Supervisiones Realizadas (${s.supervisiones.length}/2)
                        </h3>
                        <div class="space-y-3 sm:space-y-4">
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
                        ${s.supervisiones.length >= 2 ? `
                            <div class="mt-4 p-3 sm:p-4 bg-green-100 border-l-4 border-green-600 rounded-lg">
                                <p class="text-green-800 font-bold text-sm sm:text-base">✓ Supervisiones completadas</p>
                                <p class="text-green-700 text-xs sm:text-sm">El estudiante ya puede subir su carta de finalización.</p>
                            </div>
                        ` : `
                            <div class="mt-4 p-3 sm:p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg">
                                <p class="text-yellow-800 font-semibold text-sm sm:text-base">⏳ Faltan ${2 - s.supervisiones.length} supervisión(es)</p>
                            </div>
                        `}
                    </div>
                `;
            } else {
                supervisionesHTML = `
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                        <p class="text-yellow-800 font-semibold text-sm sm:text-base">⚠ No hay supervisiones registradas</p>
                        <p class="text-yellow-700 text-xs sm:text-sm">El supervisor debe realizar las 2 supervisiones requeridas.</p>
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

            // Filtrar documentos
            const docsIniciales = s.documentos.filter(d => ['carta_presentacion', 'carta_aceptacion', 'documento_ia01', 'documento_ia02', 'colegiacion', 'constancia_trabajo', 'constancia_aprobacion'].includes(d.tipo));
            const docsFinalizacion = s.documentos.filter(d => d.tipo === 'carta_finalizacion');
            
            content.innerHTML = `
                <!-- Alerta de Estado -->
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-yellow-800 font-bold text-lg mb-1">Práctica en Proceso</p>
                            <p class="text-yellow-700 text-sm">Aprobada el: ${new Date(s.created_at).toLocaleDateString('es-HN')}</p>
                        </div>
                    </div>
                </div>

                <!-- Información del Estudiante -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 sm:p-6 border border-blue-200">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Información del Estudiante
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Nombre completo</p>
                            <p class="text-sm sm:text-base text-gray-900 font-bold break-words">${s.user.name}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Número de cuenta</p>
                            <p class="text-sm sm:text-base text-gray-900 font-bold">${s.numero_cuenta || 'N/A'}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Correo electrónico</p>
                            <p class="text-sm sm:text-base text-gray-900 break-all">${s.user.email}</p>
                        </div>
                    </div>
                </div>

                <!-- Supervisor -->
                ${supervisorHTML}

                <!-- Información de la Empresa -->
                <div class="bg-gray-50 rounded-xl p-4 sm:p-6 border border-gray-200">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Información de la Empresa
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div class="sm:col-span-2">
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Tipo de práctica</p>
                            <span class="inline-flex px-3 py-1 text-xs sm:text-sm font-semibold rounded-full ${tipoPracticaColor}">
                                ${tipoPracticaLabel}
                            </span>
                        </div>
                        ${modalidadHTML}
                        <div class="sm:col-span-2">
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Nombre de la empresa</p>
                            <p class="text-sm sm:text-base text-gray-900 break-words">${s.nombre_empresa || 'N/A'}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Dirección</p>
                            <p class="text-sm sm:text-base text-gray-900 break-words">${s.direccion_empresa || 'N/A'}</p>
                        </div>
                        ${camposEspecificos}
                    </div>
                </div>

                <!-- Supervisiones -->
                ${supervisionesHTML}

                <!-- Documentos Iniciales -->
                <div class="bg-white rounded-xl p-4 sm:p-6 border-2 border-gray-200">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Documentos (${docsIniciales.length})
                    </h3>
                    ${docsIniciales.length > 0 ? `
                        <div class="space-y-2 sm:space-y-3">
                            ${docsIniciales.map(doc => {
                                const nombreDoc = tiposDocumentos[doc.tipo] || doc.tipo.replace(/_/g, ' ');
                                return `
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 sm:p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-800 text-sm sm:text-base break-words">${nombreDoc}</p>
                                            <p class="text-xs text-gray-500">${new Date(doc.created_at).toLocaleDateString('es-HN')}</p>
                                        </div>
                                    </div>
                                    <a href="{{ url('/estudiantes/documentos') }}/${doc.id}/ver" target="_blank"
                                       class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold text-center">
                                        Ver
                                    </a>
                                </div>
                            `}).join('')}
                        </div>
                    ` : '<p class="text-gray-500 text-center py-8 text-sm">No hay documentos</p>'}
                </div>

                <!-- Carta de Finalización (si existe) -->
                ${docsFinalizacion.length > 0 ? `
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 sm:p-6 border-2 border-green-200">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        ✓ Carta de Finalización Recibida
                    </h3>
                    <div class="bg-white rounded-lg p-3 sm:p-4 border-2 border-green-300">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm sm:text-base">Carta de Finalización</p>
                                    <p class="text-xs text-gray-600">Subida el ${new Date(docsFinalizacion[0].created_at).toLocaleDateString('es-HN')}</p>
                                </div>
                            </div>
                            <a href="{{ url('/estudiantes/documentos') }}/${docsFinalizacion[0].id}/ver" target="_blank"
                               class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-semibold text-center">
                                Ver Carta
                            </a>
                        </div>
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
                <p class="text-red-600 font-semibold">Error al cargar los detalles</p>
            </div>
        `;
    }
}

// ============================================
// FUNCIÓN: Cambiar supervisor
// ============================================
async function cambiarSupervisor(solicitudId) {
    const modal = document.getElementById('modalCambiarSupervisor');
    const form = document.getElementById('formCambiarSupervisor');
    
    // Obtener datos de la solicitud actual
    try {
        const response = await fetch(`{{ url('/admin/solicitudes') }}/${solicitudId}`);
        const data = await response.json();
        
        if (data.success) {
            solicitudActual = data.solicitud;
            supervisorActualId = solicitudActual.supervisor_id;
            
            // Mostrar supervisor actual
            if (solicitudActual.supervisor) {
                document.getElementById('supervisorActualNombre').textContent = solicitudActual.supervisor.user.name;
                document.getElementById('supervisorActualEmail').textContent = solicitudActual.supervisor.user.email;
            } else {
                document.getElementById('supervisorActualNombre').textContent = 'Sin asignar';
                document.getElementById('supervisorActualEmail').textContent = '-';
            }
            
            // Actualizar select excluyendo el supervisor actual
            actualizarSelectCambioSupervisor();
            
            form.action = `{{ url('/admin/solicitudes') }}/${solicitudId}/cambiar-supervisor`;
            modal.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error al cargar solicitud:', error);
        alert('Error al cargar la información de la solicitud');
    }
}

// ============================================
// FUNCIÓN: Actualizar select de cambio (excluyendo actual)
// ============================================
function actualizarSelectCambioSupervisor() {
    const select = document.getElementById('supervisorSelectCambio');
    if (!select) return;
    
    select.innerHTML = '<option value="">Selecciona un supervisor</option>';
    
    // Filtrar supervisores: disponibles y que NO sea el actual
    const disponibles = supervisoresData.filter(s => !s.lleno && s.id !== supervisorActualId);
    const llenos = supervisoresData.filter(s => s.lleno && s.id !== supervisorActualId);
    
    // Agregar supervisores disponibles
    disponibles.forEach(supervisor => {
        const option = document.createElement('option');
        option.value = supervisor.id;
        option.textContent = `${supervisor.nombre} (${supervisor.disponibles} cupos disponibles)`;
        option.dataset.supervisor = JSON.stringify(supervisor);
        select.appendChild(option);
    });
    
    // Agregar supervisores llenos (deshabilitados)
    if (llenos.length > 0) {
        const optgroup = document.createElement('optgroup');
        optgroup.label = 'Sin cupo disponible';
        
        llenos.forEach(supervisor => {
            const option = document.createElement('option');
            option.value = supervisor.id;
            option.textContent = `${supervisor.nombre} (LLENO)`;
            option.disabled = true;
            optgroup.appendChild(option);
        });
        
        select.appendChild(optgroup);
    }
    
    // Si no hay supervisores disponibles
    if (disponibles.length === 0) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No hay supervisores disponibles para cambio';
        option.disabled = true;
        select.appendChild(option);
    }
}

// ============================================
// EVENTO: Mostrar info del nuevo supervisor seleccionado
// ============================================
document.addEventListener('change', function(e) {
    if (e.target.id === 'supervisorSelectCambio') {
        const selectedOption = e.target.options[e.target.selectedIndex];
        const infoDiv = document.getElementById('nuevoSupervisorInfo');
        
        if (selectedOption.value && selectedOption.dataset.supervisor) {
            const supervisor = JSON.parse(selectedOption.dataset.supervisor);
            
            document.getElementById('nuevoSupervisorEmail').textContent = supervisor.email;
            document.getElementById('nuevoSupervisorCapacidad').textContent = 
                `${supervisor.asignados} de ${supervisor.max_estudiantes} estudiantes`;
            
            infoDiv.classList.remove('hidden');
        } else {
            infoDiv.classList.add('hidden');
        }
    }
});

// ============================================
// FUNCIÓN: Finalizar solicitud
// ============================================
async function finalizarSolicitud(solicitudId) {
    const modal = document.getElementById('modalFinalizar');
    const form = document.getElementById('formFinalizar');
    
    // Obtener datos de la solicitud
    try {
        const response = await fetch(`{{ url('/admin/solicitudes') }}/${solicitudId}`);
        const data = await response.json();
        
        if (data.success) {
            const s = data.solicitud;
            
            // Verificar requisitos
            const supervisionesCount = s.supervisiones ? s.supervisiones.length : 0;
            const tieneCartaFinalizacion = s.documentos.some(d => d.tipo === 'carta_finalizacion');
            
            if (supervisionesCount < 2) {
                alert(' No se puede finalizar: El supervisor debe completar 2 supervisiones.\nActualmente tiene: ' + supervisionesCount);
                return;
            }
            
            if (!tieneCartaFinalizacion) {
                alert(' No se puede finalizar: El estudiante debe subir su carta de finalización.');
                return;
            }
            
            // Todo OK, mostrar modal
            document.getElementById('nombreEstudianteFinalizar').textContent = s.user.name;
            form.action = `{{ url('/admin/solicitudes') }}/${solicitudId}/finalizar`;
            modal.classList.remove('hidden');
            
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al verificar los requisitos de finalización');
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

