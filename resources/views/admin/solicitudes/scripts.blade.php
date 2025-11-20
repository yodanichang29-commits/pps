<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let supervisoresData = [];

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
            actualizarSelectSupervisores();
        } else {
            console.error('Error al cargar supervisores:', data.message);
        }
    } catch (error) {
        console.error('Error en la petición:', error);
    }
}

// ============================================
// FUNCIÓN: Actualizar select de supervisores
// ============================================
function actualizarSelectSupervisores() {
    const select = document.getElementById('supervisorSelect');
    select.innerHTML = '<option value="">Selecciona un supervisor</option>';
    
    // Separar supervisores: disponibles y llenos
    const disponibles = supervisoresData.filter(s => !s.lleno);
    const llenos = supervisoresData.filter(s => s.lleno);
    
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
}

// ============================================
// EVENTO: Mostrar info del supervisor
// ============================================
document.addEventListener('change', function(e) {
    if (e.target.id === 'supervisorSelect') {
        const selectedOption = e.target.options[e.target.selectedIndex];
        const infoDiv = document.getElementById('infoSupervisor');
        
        if (selectedOption.value && selectedOption.dataset.supervisor) {
            const supervisor = JSON.parse(selectedOption.dataset.supervisor);
            
            document.getElementById('supervisorEmail').textContent = supervisor.email;
            document.getElementById('supervisorCapacidad').textContent = 
                `${supervisor.asignados} de ${supervisor.max_estudiantes} estudiantes`;
            
            infoDiv.classList.remove('hidden');
        } else {
            infoDiv.classList.add('hidden');
        }
    }
});

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
            <svg class="animate-spin h-8 w-8 sm:h-10 sm:w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
            
            content.innerHTML = `
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

<div>
    <p class="text-sm text-gray-600 font-semibold">Celular</p>
    <p class="text-base text-gray-900 font-bold">${s.celular || 'N/A'}</p>
</div>

                        <div class="sm:col-span-2">
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Correo electrónico</p>
                            <p class="text-sm sm:text-base text-gray-900 break-all">${s.user.email}</p>
                        </div>
                        ${s.telefono_alumno ? `
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Teléfono del estudiante</p>
                            <p class="text-sm sm:text-base text-gray-900">${s.telefono_alumno}</p>
                        </div>
                        ` : ''}
                        <div class="sm:col-span-2">
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Carrera</p>
                            <p class="text-sm sm:text-base text-gray-900">Informática Administrativa</p>
                        </div>
                    </div>
                </div>

                <!-- Información de la Práctica -->
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
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Jefe inmediato</p>
                            <p class="text-sm sm:text-base text-gray-900 break-words">${s.nombre_jefe || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Teléfono del jefe</p>
                            <p class="text-sm sm:text-base text-gray-900">${s.numero_jefe || 'N/A'}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Correo del jefe</p>
                            <p class="text-sm sm:text-base text-gray-900 break-all">${s.correo_jefe || 'N/A'}</p>
                        </div>
                        ${camposEspecificos}
                        <div class="sm:col-span-2">
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Fecha de solicitud</p>
                            <p class="text-sm sm:text-base text-gray-900">${new Date(s.created_at).toLocaleDateString('es-HN', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}</p>
                        </div>
                        ${s.observacion ? `
                        <div class="sm:col-span-2">
                            <p class="text-xs sm:text-sm text-gray-600 font-semibold">Observaciones</p>
                            <p class="text-sm sm:text-base text-gray-700 whitespace-pre-wrap break-words">${s.observacion}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>

                <!-- Documentos -->
                <div class="bg-white rounded-xl p-4 sm:p-6 border-2 border-gray-200">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-3 sm:mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Documentos Adjuntos (${s.documentos.length})
                    </h3>
                    ${s.documentos.length > 0 ? `
                        <div class="space-y-2 sm:space-y-3">
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
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 sm:p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 transition">
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
                                       class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold text-center flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver
                                    </a>
                                </div>
                            `}).join('')}
                        </div>
                    ` : '<p class="text-gray-500 text-center py-8 text-sm">No hay documentos adjuntos</p>'}
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-600 font-semibold text-sm sm:text-base">Error al cargar los detalles</p>
            </div>
        `;
    }
}

// ============================================
// FUNCIÓN: Abrir modal de aprobar
// ============================================
function abrirModalAprobar(solicitudId) {
    const modal = document.getElementById('modalAprobar');
    const form = document.getElementById('formAprobar');
    
    form.action = `{{ url('/admin/solicitudes') }}/${solicitudId}/aprobar`;
    modal.classList.remove('hidden');
}

// ============================================
// FUNCIÓN: Abrir modal de rechazar
// ============================================
function abrirModalRechazar(solicitudId) {
    const modal = document.getElementById('modalRechazar');
    const form = document.getElementById('formRechazar');
    
    form.action = `{{ url('/admin/solicitudes') }}/${solicitudId}/rechazar`;
    modal.classList.remove('hidden');
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

// ============================================
// PREVENIR SCROLL DEL BODY
// ============================================
const modales = document.querySelectorAll('[id^="modal"]');
modales.forEach(modal => {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                if (!modal.classList.contains('hidden')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }
        });
    });
    observer.observe(modal, { attributes: true });
});
</script>

{{-- ============================================
     ESTILOS CSS
     ============================================ --}}
<style>
/* Scrollbar personalizada en modales */
#modalDetalle > div,
#modalAprobar > div,
#modalRechazar > div {
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.4) transparent;
}

#modalDetalle > div::-webkit-scrollbar,
#modalAprobar > div::-webkit-scrollbar,
#modalRechazar > div::-webkit-scrollbar {
    width: 6px;
}

#modalDetalle > div::-webkit-scrollbar-track,
#modalAprobar > div::-webkit-scrollbar-track,
#modalRechazar > div::-webkit-scrollbar-track {
    background: transparent;
}

#modalDetalle > div::-webkit-scrollbar-thumb,
#modalAprobar > div::-webkit-scrollbar-thumb,
#modalRechazar > div::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.4);
    border-radius: 10px;
}

#modalDetalle > div::-webkit-scrollbar-thumb:hover,
#modalAprobar > div::-webkit-scrollbar-thumb:hover,
#modalRechazar > div::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.6);
}

/* Animación fade-in */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>