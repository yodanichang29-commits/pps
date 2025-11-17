// public/js/solicitud-form.js

let currentStep = 1;
const totalSteps = 3;

// Inicializar drag & drop
document.addEventListener('DOMContentLoaded', function() {
    initFileUploads();
});

function initFileUploads() {
    const uploadBoxes = document.querySelectorAll('.file-upload-box');
    
    uploadBoxes.forEach(box => {
        const dropArea = box.querySelector('.file-drop-area');
        const fileInput = box.querySelector('.file-input');
        
        // Click to upload
        dropArea.addEventListener('click', () => fileInput.click());
        
        // File selected
        fileInput.addEventListener('change', function() {
            handleFile(this.files[0], dropArea, fileInput);
        });
        
        // Drag & drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.add('drag-over'), false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.remove('drag-over'), false);
        });
        
        dropArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            handleFile(files[0], dropArea, fileInput);
        });
    });
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function handleFile(file, dropArea, fileInput) {
    if (!file) return;
    
    // Validar tipo
    if (file.type !== 'application/pdf') {
        showError('Solo se permiten archivos PDF');
        fileInput.value = '';
        return;
    }
    
    // Validar tamaño (5MB)
    if (file.size > 5 * 1024 * 1024) {
        showError('El archivo no debe superar los 5MB');
        fileInput.value = '';
        return;
    }
    
    // Actualizar UI - mantener el input original
    const fileName = file.name;
    const fileSize = (file.size / 1024 / 1024).toFixed(2);
    
    dropArea.classList.add('has-file');
    
    // Crear un div para el preview SIN eliminar el input
    let previewDiv = dropArea.querySelector('.file-preview');
    if (!previewDiv) {
        previewDiv = document.createElement('div');
        previewDiv.className = 'file-preview';
        dropArea.insertBefore(previewDiv, dropArea.firstChild);
    }
    
    // Ocultar el contenido original (SVG y textos)
    const originalContent = dropArea.querySelectorAll('svg, p');
    originalContent.forEach(el => el.style.display = 'none');
    
    previewDiv.innerHTML = `
        <div class="flex items-center justify-center">
            <svg class="w-10 h-10 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-left">
                <p class="font-semibold text-gray-800">${fileName}</p>
                <p class="text-sm text-gray-600">${fileSize} MB</p>
            </div>
            <button type="button" class="ml-4 text-red-500 hover:text-red-700" onclick="removeFile(this)">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;
}

function removeFile(button) {
    const box = button.closest('.file-upload-box');
    const dropArea = box.querySelector('.file-drop-area');
    const fileInput = box.querySelector('.file-input');
    
    // Limpiar el input
    fileInput.value = '';
    
    // Remover clase y preview
    dropArea.classList.remove('has-file');
    const previewDiv = dropArea.querySelector('.file-preview');
    if (previewDiv) {
        previewDiv.remove();
    }
    
    // Mostrar contenido original
    const originalContent = dropArea.querySelectorAll('svg, p');
    originalContent.forEach(el => el.style.display = '');
}

function mostrarCampos() {
    const tipo = document.querySelector('input[name="tipo_practica"]:checked')?.value;
    
    // Ocultar todos
    document.getElementById('modalidad_fields').classList.add('hidden');
    document.getElementById('trabajo_fields').classList.add('hidden');
    document.getElementById('normal_fields').classList.add('hidden');
    document.getElementById('docs_normal').classList.add('hidden');
    document.getElementById('docs_trabajo').classList.add('hidden');
    
    // DESHABILITAR inputs de ambas secciones
    disableFileInputs('#docs_normal');
    disableFileInputs('#docs_trabajo');
    
    // Limpiar archivos de la sección que se oculta
    if (tipo === 'normal') {
        clearFiles('#docs_trabajo');
    } else if (tipo === 'trabajo') {
        clearFiles('#docs_normal');
    }
    
    // Mostrar según tipo
    if (tipo === 'normal') {
        document.getElementById('modalidad_fields').classList.remove('hidden');
        document.getElementById('normal_fields').classList.remove('hidden');
        document.getElementById('docs_normal').classList.remove('hidden');
        // HABILITAR inputs de normal
        enableFileInputs('#docs_normal');
    } else if (tipo === 'trabajo') {
        document.getElementById('trabajo_fields').classList.remove('hidden');
        document.getElementById('docs_trabajo').classList.remove('hidden');
        // HABILITAR inputs de trabajo
        enableFileInputs('#docs_trabajo');
    }
}

// Nueva función para deshabilitar inputs
function disableFileInputs(selector) {
    document.querySelectorAll(selector + ' .file-input').forEach(input => {
        input.disabled = true;
    });
}

// Nueva función para habilitar inputs
function enableFileInputs(selector) {
    document.querySelectorAll(selector + ' .file-input').forEach(input => {
        input.disabled = false;
    });
}

function clearFiles(selector) {
    document.querySelectorAll(selector + ' .file-upload-box').forEach(box => {
        const dropArea = box.querySelector('.file-drop-area');
        const fileInput = box.querySelector('.file-input');
        
        if (fileInput) {
            fileInput.value = '';
        }
        
        if (dropArea.classList.contains('has-file')) {
            dropArea.classList.remove('has-file');
            const previewDiv = dropArea.querySelector('.file-preview');
            if (previewDiv) {
                previewDiv.remove();
            }
            
            // Mostrar contenido original
            const originalContent = dropArea.querySelectorAll('svg, p');
            originalContent.forEach(el => el.style.display = '');
        }
    });
}

function changeStep(n) {
    hideError();
    
    // Validar paso actual antes de avanzar
    if (n === 1 && !validateCurrentStep()) {
        return;
    }
    
    const steps = document.querySelectorAll('.step-content');
    
    // Ocultar paso actual
    steps[currentStep - 1].classList.add('hidden');
    
    // Actualizar paso
    currentStep += n;
    
    // Mostrar nuevo paso
    steps[currentStep - 1].classList.remove('hidden');
    
    // Actualizar indicadores visuales
    updateStepIndicators();
    
    // Actualizar botones
    document.getElementById('prevBtn').classList.toggle('hidden', currentStep === 1);
    document.getElementById('nextBtn').classList.toggle('hidden', currentStep === totalSteps);
    document.getElementById('submitBtn').classList.toggle('hidden', currentStep !== totalSteps);
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateStepIndicators() {
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        const stepNum = index + 1;
        const circle = indicator.querySelector('.step-circle');
        const text = indicator.querySelector('.step-text');
        
        if (stepNum < currentStep) {
            // Completado
            circle.classList.remove('bg-gray-300', 'bg-gradient-to-br', 'from-blue-500', 'to-indigo-600');
            circle.classList.add('bg-green-500');
            text.classList.remove('text-gray-400');
            text.classList.add('text-green-600');
        } else if (stepNum === currentStep) {
            // Actual
            circle.classList.remove('bg-gray-300', 'bg-green-500');
            circle.classList.add('bg-gradient-to-br', 'from-blue-500', 'to-indigo-600');
            text.classList.remove('text-gray-400', 'text-green-600');
            text.classList.add('text-gray-700');
        } else {
            // Pendiente
            circle.classList.remove('bg-green-500', 'bg-gradient-to-br', 'from-blue-500', 'to-indigo-600');
            circle.classList.add('bg-gray-300');
            text.classList.remove('text-green-600', 'text-gray-700');
            text.classList.add('text-gray-400');
        }
    });
}

function validateCurrentStep() {
    if (currentStep === 1) {
        // Validar tipo de práctica
        const tipoPractica = document.querySelector('input[name="tipo_practica"]:checked');
        if (!tipoPractica) {
            showError('Por favor selecciona el tipo de práctica');
            return false;
        }
        
        // Si es normal, validar modalidad
        if (tipoPractica.value === 'normal') {
            const modalidad = document.querySelector('input[name="modalidad"]:checked');
            if (!modalidad) {
                showError('Por favor selecciona la modalidad de trabajo');
                return false;
            }
        }
    }
    
    if (currentStep === 2) {
        // Validar campos obligatorios del paso 2
        const requiredFields = [
            { name: 'numero_cuenta', label: 'Número de Cuenta' },
            { name: 'nombre_empresa', label: 'Nombre de la Empresa' },
            { name: 'direccion_empresa', label: 'Dirección de la Empresa' },
            { name: 'nombre_jefe', label: 'Nombre del Jefe' },
            { name: 'numero_jefe', label: 'Teléfono del Jefe' },
            { name: 'correo_jefe', label: 'Correo del Jefe' }
        ];
        
        for (let field of requiredFields) {
            const input = document.querySelector(`[name="${field.name}"]`);
            if (!input.value.trim()) {
                showError(`El campo "${field.label}" es obligatorio`);
                input.focus();
                return false;
            }
        }
        
        // Validar email
        const emailInput = document.querySelector('[name="correo_jefe"]');
        if (!isValidEmail(emailInput.value)) {
            showError('Por favor ingresa un correo electrónico válido');
            emailInput.focus();
            return false;
        }
        
        // Validar campos específicos según tipo
        const tipoPractica = document.querySelector('input[name="tipo_practica"]:checked')?.value;
        
        if (tipoPractica === 'normal') {
            const normalFields = [
                { name: 'fecha_inicio', label: 'Fecha de Inicio' },
                { name: 'fecha_fin', label: 'Fecha de Finalización' },
                { name: 'horario', label: 'Horario' }
            ];
            
            for (let field of normalFields) {
                const input = document.querySelector(`[name="${field.name}"]`);
                if (!input.value.trim()) {
                    showError(`El campo "${field.label}" es obligatorio`);
                    input.focus();
                    return false;
                }
            }
        }
        
        if (tipoPractica === 'trabajo') {
            const trabajoFields = [
                { name: 'puesto_trabajo', label: 'Puesto de Trabajo' },
                { name: 'anios_trabajando', label: 'Años Trabajando' }
            ];
            
            for (let field of trabajoFields) {
                const input = document.querySelector(`[name="${field.name}"]`);
                if (!input.value.trim()) {
                    showError(`El campo "${field.label}" es obligatorio`);
                    input.focus();
                    return false;
                }
            }
        }
    }
    
    return true;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showError(message) {
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');
    errorMessage.textContent = message;
    errorAlert.classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function hideError() {
    document.getElementById('errorAlert').classList.add('hidden');
}

// Validar antes de enviar
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('ppsForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validar que haya al menos un documento
            const tipoPractica = document.querySelector('input[name="tipo_practica"]:checked')?.value;
            const containerSelector = tipoPractica === 'normal' ? '#docs_normal' : '#docs_trabajo';
            const fileInputs = document.querySelectorAll(containerSelector + ' .file-input');
            
            let hasFile = false;
            fileInputs.forEach(input => {
                if (input.files.length > 0) {
                    hasFile = true;
                }
            });
            
            if (!hasFile) {
                e.preventDefault();
                showError('Debes subir al menos un documento para continuar');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return false;
            }
        });
    }
});