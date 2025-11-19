<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Documento;
use App\Models\SolicitudPPS;

class DocumentoController extends Controller
{
    /**
     * LISTADO (Estudiante): /estudiantes/documentos
     * Muestra documentos de la ÚLTIMA solicitud activa del alumno autenticado.
     */
    public function index()
    {
        $userId = Auth::id();

        // Buscar última solicitud activa (no cancelada ni finalizada)
        $solicitud = SolicitudPPS::where('user_id', $userId)
            ->with('documentos')
            ->whereNotIn('estado_solicitud', [
                SolicitudPPS::EST_CANCELADA,
                SolicitudPPS::EST_FINALIZADA,
            ])
            ->orderByDesc('id')
            ->first();

        // Si no hay solicitud activa, buscar la última (aunque esté finalizada/cancelada)
        if (!$solicitud) {
            $solicitud = SolicitudPPS::where('user_id', $userId)
                ->with('documentos')
                ->orderByDesc('id')
                ->first();
        }

        $documentos = $solicitud?->documentos ?? collect();

        Log::info('Documentos cargados para user_id ' . $userId . ': ' . $documentos->count());

        return view('estudiantes.documentos.index', [
            'titulo'     => 'Mis documentos',
            'solicitud'  => $solicitud,
            'documentos' => $documentos,
            'mensaje'    => $solicitud ? null : 'Aún no has enviado ninguna solicitud.',
        ]);
    }

    /**
     * LISTADO por solicitud (Admin/Supervisor/Estudiante).
     */
    public function indexBySolicitud(int $id)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Usuario no autenticado.');
        }

        $solicitud = SolicitudPPS::with('documentos')->findOrFail($id);

        $esAdmin      = $user->isAdmin();
        $esSupervisor = $user->isSupervisor();
        $esEstudiante = $user->isEstudiante();

        if ($esAdmin) {
            // admin ve todo
        } elseif ($esSupervisor) {
            if ((int)$solicitud->supervisor_id !== (int)$user->id) {
                abort(403, 'No autorizado para ver estos documentos.');
            }
        } elseif ($esEstudiante) {
            if ((int)$solicitud->user_id !== (int)$user->id) {
                abort(403, 'No autorizado para ver estos documentos.');
            }
        } else {
            abort(403, 'No autorizado.');
        }

        $documentos = $solicitud->documentos ?? collect();

        return view('estudiantes.documentos.index', [
            'titulo'     => 'Documentos de la solicitud #' . $solicitud->id,
            'solicitud'  => $solicitud,
            'documentos' => $documentos,
        ]);
    }

    /**
     * Subir documento por tipo (Estudiante).
     * Ahora soporta carta_finalizacion con validaciones especiales
     */
    public function store(Request $request)
    {
        // ACTUALIZADO: Agregado 'carta_finalizacion' a los tipos permitidos
        $request->validate([
            'tipo'              => 'required|string|in:ia01,ia02,colegiacion,carta_aceptacion,carta_presentacion,constancia_aprobacion,constancia_trabajo,actualizacion,carta_finalizacion',
            'archivo'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
            'solicitud_id'      => 'nullable|integer|exists:solicitud_p_p_s,id',
            'solicitud_pps_id'  => 'nullable|integer|exists:solicitud_p_p_s,id', // ← Alternativa para compatibilidad
        ], [
            'tipo.required' => 'El tipo de documento es obligatorio',
            'tipo.in' => 'Tipo de documento no válido',
            'archivo.required' => 'El archivo es obligatorio',
            'archivo.mimes' => 'El archivo debe ser PDF, JPG, JPEG o PNG',
            'archivo.max' => 'El archivo no puede superar 10MB',
        ]);

        $user = Auth::user();
        if (!$user) {
            return back()->with('error', 'Usuario no autenticado.');
        }

        // Determinar la solicitud (prioridad: solicitud_pps_id > solicitud_id > última activa)
        $solicitudId = $request->filled('solicitud_pps_id') 
            ? $request->integer('solicitud_pps_id') 
            : $request->integer('solicitud_id');

        if ($solicitudId) {
            $solicitud = SolicitudPPS::where('id', $solicitudId)
                ->where('user_id', $user->id)
                ->first();

            if (!$solicitud) {
                return back()->with('error', 'La solicitud indicada no existe o no te pertenece.');
            }
        } else {
            $solicitud = SolicitudPPS::where('user_id', $user->id)
                ->whereNotIn('estado_solicitud', [SolicitudPPS::EST_CANCELADA, SolicitudPPS::EST_FINALIZADA])
                ->orderByDesc('id')
                ->first();

            if (!$solicitud) {
                return back()->with('error', 'Primero debes enviar tu solicitud activa.');
            }
        }

        // Validaciones especiales para carta de finalización
        if ($request->tipo === 'carta_finalizacion') {
            
            // 1. Verificar que la solicitud esté APROBADA
            if ($solicitud->estado_solicitud !== SolicitudPPS::EST_APROBADA) {
                return back()->with('error', 'Solo puedes subir la carta de finalización si tu práctica está APROBADA.');
            }

            // 2. Verificar que tenga 2 supervisiones completadas
            $supervisionesCount = DB::table('supervisiones')
                ->where('solicitud_pps_id', $solicitud->id)
                ->count();
            
            if ($supervisionesCount < 2) {
                return back()->with('error', 'No puedes subir la carta de finalización hasta que tu supervisor complete las 2 supervisiones requeridas.');
            }

            // 3. Verificar que no haya subido carta antes
            $cartaExistente = Documento::where('solicitud_pps_id', $solicitud->id)
                ->where('tipo', 'carta_finalizacion')
                ->exists();
            
            if ($cartaExistente) {
                return back()->with('error', 'Ya has subido una carta de finalización para esta práctica.');
            }
        }

        // Sustituir documento previo del mismo tipo en esta solicitud (EXCEPTO carta_finalizacion)
        if ($request->tipo !== 'carta_finalizacion') {
            if ($prev = Documento::where('solicitud_pps_id', $solicitud->id)->where('tipo', $request->string('tipo'))->first()) {
                Storage::disk('private')->delete($prev->ruta);
                Storage::disk('public')->delete($prev->ruta);
                $prev->delete();
                Log::info('Documento anterior eliminado: ' . $prev->id);
            }
        }

        try {
            DB::beginTransaction();

            // Guardar archivo en carpeta privada de la solicitud
            $archivo = $request->file('archivo');
            $extension = $archivo->getClientOriginalExtension();
            $nombreArchivo = $request->tipo . '_' . time() . '.' . $extension;
            $ruta = $archivo->storeAs("documentos/{$solicitud->id}", $nombreArchivo, 'private');

            $documento = Documento::create([
                'solicitud_pps_id' => $solicitud->id,
                'tipo'             => $request->string('tipo'),
                'ruta'             => $ruta,
            ]);

            DB::commit();

            Log::info('Documento creado: ID=' . $documento->id . ', Tipo=' . $documento->tipo . ', Usuario=' . $user->id);

            // Mensaje personalizado para carta de finalización
            $mensaje = $request->tipo === 'carta_finalizacion' 
                ? 'Carta de finalización subida exitosamente. Está en revisión por el administrador.'
                : 'Documento subido correctamente.';

            return back()->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al subir documento: ' . $e->getMessage());
            
            return back()->with('error', 'Error al subir el documento. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Ver el documento en el navegador (inline si es PDF/imagen).
     */
    public function ver($id)
    {
        $documento = Documento::with('solicitud')->findOrFail($id);
        $this->authorizeView($documento);

        $pathPrivate = storage_path("app/private/{$documento->ruta}");
        $pathPublic  = storage_path("app/public/{$documento->ruta}");
        $path = file_exists($pathPrivate) ? $pathPrivate : $pathPublic;

        if (!file_exists($path)) {
            Log::error('Archivo no encontrado: ' . $documento->ruta);
            abort(404, 'El archivo no existe en el servidor.');
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $downloadName = basename($path);

        if ($ext === 'pdf') {
            return response()->file($path, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
            ]);
        }

        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            return response()->file($path, [
                'Content-Type'        => mime_content_type($path),
                'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
            ]);
        }

        return response()->download($path, $downloadName);
    }

    /**
     * Descargar el documento.
     */
    public function descargar($id)
    {
        $documento = Documento::with('solicitud')->findOrFail($id);
        $this->authorizeView($documento);

        if (Storage::disk('private')->exists($documento->ruta)) {
            $nombreDescarga = basename($documento->ruta);
            return Storage::disk('private')->download($documento->ruta, $nombreDescarga);
        }

        if (Storage::disk('public')->exists($documento->ruta)) {
            $nombreDescarga = basename($documento->ruta);
            return Storage::disk('public')->download($documento->ruta, $nombreDescarga);
        }

        Log::error('Archivo no encontrado en storage: ' . $documento->ruta);
        abort(404, 'El archivo no existe en el servidor.');
    }

    /**
     * Eliminar un documento.
     * NO permite eliminar carta_finalizacion una vez subida
     */
    public function destroy($id)
    {
        $documento = Documento::with('solicitud')->findOrFail($id);

        $user = Auth::user();
        if (!$user) {
            abort(403, 'Usuario no autenticado.');
        }

        $esAdmin = $user->isAdmin();
        $esDueno = (int)($documento->solicitud?->user_id) === (int)$user->id;

        if (!$esAdmin && !$esDueno) {
            abort(403, 'No autorizado para eliminar este documento.');
        }

        // NO permitir eliminar carta de finalización (solo admin puede forzarlo si es necesario)
        if ($documento->tipo === 'carta_finalizacion' && !$esAdmin) {
            return back()->with('error', 'No puedes eliminar la carta de finalización una vez subida. Contacta al administrador si necesitas cambiarla.');
        }

        try {
            // Borrar en private y también en public por compatibilidad
            Storage::disk('private')->delete($documento->ruta);
            Storage::disk('public')->delete($documento->ruta);

            $documento->delete();

            Log::info('Documento eliminado: ID=' . $id . ' por usuario=' . $user->id);

            return back()->with('success', 'Documento eliminado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar documento: ' . $e->getMessage());
            
            return back()->with('error', 'Error al eliminar el documento.');
        }
    }

    /**
     * Regla de autorización común (admin, supervisor asignado o propietario).
     */
    private function authorizeView(Documento $documento): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Usuario no autenticado.');
        }

        $sol = $documento->solicitud;

        // Admin
        if ($user->isAdmin()) {
            return;
        }

        // Supervisor asignado
        if ($user->isSupervisor()) {
            if ((int)($sol->supervisor_id ?? 0) === (int)$user->id) {
                return;
            }
            abort(403, 'No autorizado. Supervisor no asignado.');
        }

        // Estudiante dueño
        if ($user->isEstudiante()) {
            if ((int)$sol->user_id === (int)$user->id) {
                return;
            }
            abort(403, 'No autorizado. Documento de otro estudiante.');
        }

        abort(403, 'No autorizado.');
    }
}