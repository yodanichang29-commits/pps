<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\SolicitudPPS;
use App\Models\Supervisor;
use App\Models\User;

class SolicitudAdminController extends Controller
{
    /**
     * Mostrar todas las solicitudes pendientes
     */
    public function pendientes(Request $request)
    {
        $busqueda = $request->query('busqueda');

        $query = SolicitudPPS::with(['user', 'documentos'])
            ->where('estado_solicitud', 'SOLICITADA');

        // Búsqueda por nombre o email del estudiante
        if ($busqueda) {
            $query->whereHas('user', function($q) use ($busqueda) {
                $q->where('name', 'LIKE', "%{$busqueda}%")
                  ->orWhere('email', 'LIKE', "%{$busqueda}%");
            })->orWhere('numero_cuenta', 'LIKE', "%{$busqueda}%");
        }

        $solicitudes = $query->latest('id')->paginate(15);

        // Contadores
        $contadores = [
            'pendientes' => SolicitudPPS::where('estado_solicitud', 'SOLICITADA')->count(),
            'aprobadas' => SolicitudPPS::where('estado_solicitud', 'APROBADA')->count(),
            'rechazadas' => SolicitudPPS::where('estado_solicitud', 'RECHAZADA')->count(),
            'finalizadas' => SolicitudPPS::where('estado_solicitud', 'FINALIZADA')->count(),
        ];

        return view('admin.solicitudes.pendientes', compact('solicitudes', 'contadores'));
    }

    /**
     * Ver detalle completo de una solicitud
     */
        public function show($id)
        {
            $solicitud = SolicitudPPS::with(['user', 'documentos', 'supervisor.user', 'supervisiones']) 
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'solicitud' => $solicitud
            ]);
        }

    /**
     * Aprobar solicitud y asignar supervisor
     */
    public function aprobar(Request $request, $id)
    {
        $request->validate([
            'supervisor_id' => 'required|exists:supervisores,id',
        ], [
            'supervisor_id.required' => 'Debes seleccionar un supervisor',
            'supervisor_id.exists' => 'El supervisor seleccionado no existe',
        ]);

        try {
            $solicitud = SolicitudPPS::findOrFail($id);

            // Obtener el supervisor con su usuario relacionado
            $supervisor = Supervisor::with('user')
                ->where('id', $request->supervisor_id)
                ->where('activo', 1)
                ->first();

            if (!$supervisor) {
                return redirect()
                    ->route('admin.solicitudes.pendientes')
                    ->with('error', 'El supervisor seleccionado no está activo o no existe');
            }

            // Verificar capacidad
            if ($supervisor->estaLleno()) {
                return redirect()
                    ->route('admin.solicitudes.pendientes')
                    ->with('error', 'El supervisor seleccionado ya alcanzó su capacidad máxima (' . $supervisor->max_estudiantes . ' estudiantes)');
            }

            // Actualizar solicitud
            $solicitud->estado_solicitud = 'APROBADA';
            $solicitud->supervisor_id = $request->supervisor_id;
            $solicitud->observaciones = null;
            $solicitud->save();

            Log::info('Solicitud #' . $id . ' aprobada y asignada a supervisor #' . $request->supervisor_id . ' (' . $supervisor->user->name . ')');

            return redirect()
                ->route('admin.solicitudes.pendientes')
                ->with('success', 'Solicitud aprobada correctamente. Supervisor asignado: ' . $supervisor->user->name);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error de BD al aprobar solicitud: ' . $e->getMessage());
            
            return redirect()
                ->route('admin.solicitudes.pendientes')
                ->with('error', 'Error de base de datos. Por favor, contacta al administrador del sistema.');
            
        } catch (\Exception $e) {
            Log::error('❌ Error general al aprobar solicitud: ' . $e->getMessage());
            
            return redirect()
                ->route('admin.solicitudes.pendientes')
                ->with('error', 'Ocurrió un error inesperado. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Rechazar solicitud con motivo
     */
    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'observaciones' => 'required|string|max:1000',
        ], [
            'observaciones.required' => 'Debes explicar el motivo del rechazo',
            'observaciones.max' => 'El motivo no puede superar los 1000 caracteres',
        ]);

        try {
            $solicitud = SolicitudPPS::findOrFail($id);

            $solicitud->estado_solicitud = 'RECHAZADA';
            $solicitud->observaciones = $request->observaciones;
            $solicitud->supervisor_id = null; // Quitar supervisor si tenía
            $solicitud->save();

            Log::info('Solicitud #' . $id . ' rechazada. Motivo: ' . $request->observaciones);

            return redirect()
                ->route('admin.solicitudes.pendientes')
                ->with('success', 'Solicitud rechazada correctamente. El estudiante ha sido notificado.');

        } catch (\Exception $e) {
            Log::error('Error al rechazar solicitud: ' . $e->getMessage());
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Obtener supervisores disponibles para asignar
     */
    public function getSupervisoresDisponibles()
    {
        try {
            //  Obtener supervisores activos con sus datos de usuario
            $supervisores = Supervisor::with('user')
                ->where('activo', 1)
                ->get()
                ->map(function($supervisor) {
                    // Calcular estudiantes asignados
                    $asignados = $supervisor->estudiantes_asignados;

                    return [
                        'id' => $supervisor->id,
                        'nombre' => $supervisor->user->name ?? 'Supervisor #' . $supervisor->id,
                        'email' => $supervisor->user->email ?? 'N/A',
                        'asignados' => $asignados,
                        'max_estudiantes' => $supervisor->max_estudiantes,
                        'disponibles' => $supervisor->cupos_disponibles,
                        'lleno' => $supervisor->estaLleno(),
                        'porcentaje_ocupacion' => $supervisor->porcentaje_ocupacion,
                    ];
                })
                ->sortBy(function($supervisor) {
                    // Ordenar: primero los que tienen más cupo disponible
                    return $supervisor['lleno'] ? 999 : $supervisor['asignados'];
                })
                ->values();

            Log::info('Supervisores disponibles obtenidos: ' . $supervisores->count());

            return response()->json([
                'success' => true,
                'supervisores' => $supervisores
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error al obtener supervisores: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar supervisores: ' . $e->getMessage(),
                'supervisores' => []
            ], 500);
        }
    }

    /**
 * Cambiar supervisor asignado a una solicitud
 */
    public function cambiarSupervisor(Request $request, $id)
    {
    $request->validate([
        'supervisor_id' => 'required|exists:supervisores,id',
    ], [
        'supervisor_id.required' => 'Debes seleccionar un supervisor',
        'supervisor_id.exists' => 'El supervisor seleccionado no existe',
    ]);

    try {
        $solicitud = SolicitudPPS::findOrFail($id);

        // Verificar que la solicitud esté aprobada
        if ($solicitud->estado_solicitud !== 'APROBADA') {
            return back()->with('error', 'Solo se puede cambiar el supervisor de solicitudes aprobadas');
        }

        // Obtener el nuevo supervisor
        $nuevoSupervisor = Supervisor::with('user')
            ->where('id', $request->supervisor_id)
            ->where('activo', 1)
            ->first();

        if (!$nuevoSupervisor) {
            return back()->with('error', 'El supervisor seleccionado no está activo o no existe');
        }

        // Verificar capacidad del nuevo supervisor
        if ($nuevoSupervisor->estaLleno()) {
            return back()->with('error', 'El supervisor seleccionado ya alcanzó su capacidad máxima');
        }

        $supervisorAnterior = $solicitud->supervisor ? $solicitud->supervisor->user->name : 'Ninguno';
        
        // Cambiar supervisor
        $solicitud->supervisor_id = $request->supervisor_id;
        $solicitud->save();

        Log::info('Supervisor cambiado en solicitud #' . $id . ' | Anterior: ' . $supervisorAnterior . ' | Nuevo: ' . $nuevoSupervisor->user->name);

        return redirect()
            ->route('admin.solicitudes.aprobadas')
            ->with('success', 'Supervisor cambiado exitosamente de ' . $supervisorAnterior . ' a ' . $nuevoSupervisor->user->name);

    } catch (\Exception $e) {
        Log::error('Error al cambiar supervisor: ' . $e->getMessage());
        
        return back()->with('error', 'Error al cambiar supervisor: ' . $e->getMessage());
    }
}

    /**
     * Mostrar solicitudes aprobadas
     */
    public function aprobadas(Request $request)
    {
    $busqueda = $request->query('busqueda');

    $query = SolicitudPPS::withCount('supervisiones') // ← Esto crea automáticamente supervisiones_count
        ->with(['user', 'supervisor.user', 'documentos'])
        ->where('estado_solicitud', 'APROBADA');

    if ($busqueda) {
        $query->whereHas('user', function($q) use ($busqueda) {
            $q->where('name', 'LIKE', "%{$busqueda}%")
              ->orWhere('email', 'LIKE', "%{$busqueda}%");
        })->orWhere('numero_cuenta', 'LIKE', "%{$busqueda}%");
    }

    $solicitudes = $query->latest('id')->paginate(15);

    $contadores = [
        'pendientes' => SolicitudPPS::where('estado_solicitud', 'SOLICITADA')->count(),
        'aprobadas' => SolicitudPPS::where('estado_solicitud', 'APROBADA')->count(),
        'rechazadas' => SolicitudPPS::where('estado_solicitud', 'RECHAZADA')->count(),
        'finalizadas' => SolicitudPPS::where('estado_solicitud', 'FINALIZADA')->count(),
    ];

    return view('admin.solicitudes.aprobadas', compact('solicitudes', 'contadores'));
    }

    /**
     * Mostrar solicitudes rechazadas
     */
    public function rechazadas(Request $request)
    {
        $busqueda = $request->query('busqueda');

        $query = SolicitudPPS::with(['user', 'documentos'])
            ->where('estado_solicitud', 'RECHAZADA');

        if ($busqueda) {
            $query->whereHas('user', function($q) use ($busqueda) {
                $q->where('name', 'LIKE', "%{$busqueda}%")
                  ->orWhere('email', 'LIKE', "%{$busqueda}%");
            });
        }

        $solicitudes = $query->latest('id')->paginate(15);

        $contadores = [
            'pendientes' => SolicitudPPS::where('estado_solicitud', 'SOLICITADA')->count(),
            'aprobadas' => SolicitudPPS::where('estado_solicitud', 'APROBADA')->count(),
            'rechazadas' => SolicitudPPS::where('estado_solicitud', 'RECHAZADA')->count(),
            'finalizadas' => SolicitudPPS::where('estado_solicitud', 'FINALIZADA')->count(),
        ];

        return view('admin.solicitudes.rechazadas', compact('solicitudes', 'contadores'));
    }

    /**
     * Mostrar solicitudes finalizadas
     */
    public function finalizadas(Request $request)
    {
    $busqueda = $request->get('busqueda');
    
    // Query con eager loading de todas las relaciones necesarias
    $query = SolicitudPPS::with([
        'user',
        'supervisor.user',
        'supervisiones',
        'documentos'
    ])
    ->where('estado_solicitud', 'FINALIZADA')
    ->orderBy('updated_at', 'desc');
    
    // Filtro de búsqueda
    if ($busqueda) {
        $query->where(function($q) use ($busqueda) {
            $q->where('numero_cuenta', 'LIKE', "%{$busqueda}%")
              ->orWhereHas('user', function($q2) use ($busqueda) {
                  $q2->where('name', 'LIKE', "%{$busqueda}%")
                     ->orWhere('email', 'LIKE', "%{$busqueda}%");
              });
        });
    }
    
    $solicitudes = $query->paginate(15)->appends(['busqueda' => $busqueda]);
    
    // Contadores
    $contadores = [
        'pendientes' => SolicitudPPS::where('estado_solicitud', 'SOLICITADA')->count(),
        'aprobadas' => SolicitudPPS::where('estado_solicitud', 'APROBADA')->count(),
        'rechazadas' => SolicitudPPS::where('estado_solicitud', 'RECHAZADA')->count(),
        'finalizadas' => SolicitudPPS::where('estado_solicitud', 'FINALIZADA')->count(),
    ];
    
    return view('admin.solicitudes.finalizadas', compact('solicitudes', 'contadores'));
 }
    /**
     * Mostrar solicitudes canceladas
     */
    public function canceladas(Request $request)
    {
        $busqueda = $request->query('busqueda');

        $query = SolicitudPPS::with(['user', 'documentos'])
            ->where('estado_solicitud', 'CANCELADA');

        if ($busqueda) {
            $query->whereHas('user', function($q) use ($busqueda) {
                $q->where('name', 'LIKE', "%{$busqueda}%")
                  ->orWhere('email', 'LIKE', "%{$busqueda}%");
            });
        }

        $solicitudes = $query->latest('id')->paginate(15);

        $contadores = [
            'pendientes' => SolicitudPPS::where('estado_solicitud', 'SOLICITADA')->count(),
            'aprobadas' => SolicitudPPS::where('estado_solicitud', 'APROBADA')->count(),
            'rechazadas' => SolicitudPPS::where('estado_solicitud', 'RECHAZADA')->count(),
            'finalizadas' => SolicitudPPS::where('estado_solicitud', 'FINALIZADA')->count(),
            'canceladas' => SolicitudPPS::where('estado_solicitud', 'CANCELADA')->count(),
        ];

        return view('admin.solicitudes.canceladas', compact('solicitudes', 'contadores'));
    }

    /**
     * Ver documentos de una solicitud
     */
    public function verDocumentos($id)
    {
        $solicitud = SolicitudPPS::with(['user', 'documentos'])->findOrFail($id);
        
        return view('admin.solicitudes.documentos', compact('solicitud'));
    }

    public function verSupervision($id)
{
    try {
        $supervision = \App\Models\Supervision::findOrFail($id);
        
        // Verificar que el archivo existe
        if (!$supervision->archivo) {
            abort(404, 'Archivo no encontrado');
        }
        
        if (!\Storage::disk('private')->exists($supervision->archivo)) {
            abort(404, 'Archivo no encontrado en el servidor');
        }
        
        $path = \Storage::disk('private')->path($supervision->archivo);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        Log::info('Admin visualizando supervisión #' . $id);
        
        // Mostrar inline si es PDF o imagen
        if ($extension === 'pdf') {
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
            ]);
        }
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            return response()->file($path, [
                'Content-Type' => mime_content_type($path),
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
            ]);
        }
        
        // Si no es PDF ni imagen, forzar descarga
        return \Storage::disk('private')->download($supervision->archivo);
        
    } catch (\Exception $e) {
        Log::error('Error al ver supervisión: ' . $e->getMessage());
        abort(404, 'Archivo no encontrado');
    }
}

    public function descargarSupervision($id)
 {
    try {
        $supervision = \App\Models\Supervision::findOrFail($id);
        
        // Verificar que el archivo existe
        if (!$supervision->archivo) {
            abort(404, 'Archivo no encontrado');
        }
        
        if (!\Storage::disk('private')->exists($supervision->archivo)) {
            abort(404, 'Archivo no encontrado en el servidor');
        }
        
        Log::info('Admin descargando supervisión #' . $id);
        
        return \Storage::disk('private')->download($supervision->archivo);
        
    } catch (\Exception $e) {
        Log::error('Error al descargar supervisión: ' . $e->getMessage());
        abort(404, 'Archivo no encontrado');
    }
 }
    /**
     * Finalizar práctica profesional
     */
public function finalizar(Request $request, $id)
{
    try {
        DB::beginTransaction();

        $solicitud = SolicitudPPS::with(['documentos', 'supervisiones', 'supervisor', 'user'])
            ->findOrFail($id);

        // Validar que la solicitud esté APROBADA
        if ($solicitud->estado_solicitud !== SolicitudPPS::EST_APROBADA) {
            return back()->with('error', 'Solo se pueden finalizar prácticas que están en estado APROBADA.');
        }

        // Validar que tenga 2 supervisiones
        $supervisionesCount = $solicitud->supervisiones()->count();
        if ($supervisionesCount < 2) {
            return back()->with('error', 'La práctica debe tener 2 supervisiones completadas antes de finalizarla. Actualmente tiene ' . $supervisionesCount . '.');
        }

        // Validar que tenga carta de finalización
        $tieneCartaFinalizacion = $solicitud->documentos()
            ->where('tipo', 'carta_finalizacion')
            ->exists();

        if (!$tieneCartaFinalizacion) {
            return back()->with('error', 'El estudiante debe subir su carta de finalización antes de poder finalizar la práctica.');
        }

        // Cambiar estado a FINALIZADA (updated_at se actualiza automáticamente)
        $solicitud->estado_solicitud = SolicitudPPS::EST_FINALIZADA;
        $solicitud->save();

        DB::commit();

        Log::info('Práctica finalizada: Solicitud=' . $solicitud->id . ', Estudiante=' . $solicitud->user->name . ', Admin=' . auth()->user()->name);

        return redirect()
            ->route('admin.solicitudes.aprobadas')
            ->with('success', 'Práctica profesional finalizada exitosamente. El estudiante ' . $solicitud->user->name . ' ha completado su proceso.');

    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Error al finalizar práctica: ' . $e->getMessage());
        
        return back()->with('error', 'Error al finalizar la práctica. Por favor, intenta nuevamente.');
    }
}

}