<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudPPS;
use App\Models\Documento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SolicitudPPSController extends Controller
{
    /**
     * Mostrar la pantalla de "Solicitar Práctica".
     */
    public function create()
    {
        $ultima = SolicitudPPS::with('documentos')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->first();

        $activa = $ultima && in_array($ultima->estado_solicitud, ['SOLICITADA', 'APROBADA']);

        $documentos = $ultima
            ? $ultima->documentos()->get(['id', 'tipo'])
            : collect();

        return view('estudiantes.solicitud', [
            'solicitud'  => $ultima,
            'activa'     => $activa,
            'documentos' => $documentos,
        ]);
    }

    /**
     * Guardar una nueva solicitud.
     */
    public function store(Request $request)
    {
        if ($request->filled('observaciones') && !$request->filled('observacion')) {
            $request->merge(['observacion' => $request->input('observaciones')]);
        }

        $request->validate([
            'tipo_practica'     => 'required|in:normal,trabajo',
            'modalidad'         => 'nullable|in:presencial,semipresencial,teletrabajo',
            'numero_cuenta'     => 'required|string',
              'celular'           => 'required|string|min:8|max:15', 
            'nombre_empresa'    => 'required|string',
            'direccion_empresa' => 'required|string',
            'nombre_jefe'       => 'required|string',
            'numero_jefe'       => 'required|string',
            'correo_jefe'       => 'required|email',
            'puesto_trabajo'    => 'nullable|string',
            'anios_trabajando'  => 'nullable|integer|min:0',
            'fecha_inicio'      => 'nullable|date',
            'fecha_fin'         => 'nullable|date|after_or_equal:fecha_inicio',
            'horario'           => 'nullable|string',
            'observacion'       => 'nullable|string|max:1000',

            // Documentos opcionales
            'documento_ia01'        => 'nullable|file|mimes:pdf|max:2048',
            'documento_ia02'        => 'nullable|file|mimes:pdf|max:2048',
            'colegiacion'           => 'nullable|file|mimes:pdf|max:2048',
            'carta_aceptacion'      => 'nullable|file|mimes:pdf|max:2048',
            'carta_presentacion'    => 'nullable|file|mimes:pdf|max:2048',
            'constancia_aprobacion' => 'nullable|file|mimes:pdf|max:2048',
            'constancia_trabajo'    => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $yaExiste = SolicitudPPS::where('user_id', Auth::id())
            ->whereIn('estado_solicitud', ['SOLICITADA', 'APROBADA'])
            ->exists();

        if ($yaExiste) {
            return redirect()
                ->route('estudiantes.solicitud')
                ->with('error', 'Ya tienes una solicitud activa. Espera a que sea revisada o cancélala.');
        }

        DB::transaction(function () use ($request) {
            $solicitud = SolicitudPPS::create([
                'user_id'          => Auth::id(),
                'tipo_practica'    => $request->input('tipo_practica'),
                'modalidad'        => $request->input('modalidad'),
                'numero_cuenta'    => $request->input('numero_cuenta'),
                'nombre_empresa'   => $request->input('nombre_empresa'),
                'direccion_empresa'=> $request->input('direccion_empresa'),
                'nombre_jefe'      => $request->input('nombre_jefe'),
                'numero_jefe'      => $request->input('numero_jefe'),
                'correo_jefe'      => $request->input('correo_jefe'),
                'puesto_trabajo'   => $request->input('puesto_trabajo'),
                'anios_trabajando' => $request->input('anios_trabajando'),
                'fecha_inicio'     => $request->input('fecha_inicio'),
                'fecha_fin'        => $request->input('fecha_fin'),
                'horario'          => $request->input('horario'),
                'observacion'      => $request->input('observacion'),
                'estado_solicitud' => 'SOLICITADA',
            ]);

            $documentos = [
                'documento_ia01'        => 'Formulario_PPS_IA_01',
                'documento_ia02'        => 'Formulario_PPS_IA_02',
                'colegiacion'           => 'colegiacion',
                'carta_aceptacion'      => 'carta_aceptacion',
                'carta_presentacion'    => 'carta_presentacion',
                'constancia_aprobacion' => 'constancia_aprobacion',
                'constancia_trabajo'    => 'constancia_trabajo',
            ];

            foreach ($documentos as $campo => $tipo) {
                if ($request->hasFile($campo)) {
                    $archivo = $request->file($campo);
                    $path = $archivo->store("documentos/{$tipo}/{$solicitud->id}", 'private');

                    Documento::create([
                        'solicitud_pps_id' => $solicitud->id,
                        'tipo'             => $tipo,
                        'ruta'             => $path,
                    ]);
                }
            }
        });

        return redirect()
            ->route('estudiantes.solicitud')
            ->with('status', 'Solicitud enviada correctamente.');
    }

    /**
     * Cancelar una solicitud de práctica.
     * - Marca la solicitud como CANCELADA
     * - Elimina archivos físicos y registros de documentos relacionados
     * - Aplica soft delete a la solicitud
     */
    public function cancelar(Request $request, $id)
    {
        $solicitud = SolicitudPPS::with('documentos')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!in_array($solicitud->estado_solicitud, ['SOLICITADA', 'APROBADA'])) {
            return redirect()->back()->with('error', 'No se puede cancelar esta solicitud.');
        }

        DB::transaction(function () use ($request, $solicitud) {
            // Registrar solicitud de cancelación (queda PENDIENTE para admin)
            DB::table('solicitudes_cancelacion')->insert([
                'user_id'    => Auth::id(),
                'motivo'     => $request->input('motivo', 'Cancelación solicitada por el estudiante'),
                'estado'     => 'PENDIENTE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 🔥 Eliminar documentos asociados
            foreach ($solicitud->documentos as $doc) {
                Storage::disk('private')->delete($doc->ruta);
                $doc->delete();
            }

            // Cambiar estado y aplicar soft delete
            $solicitud->estado_solicitud = 'CANCELADA';
            $solicitud->save();
            $solicitud->delete();
        });

        return redirect()
            ->route('estudiantes.solicitud')
            ->with('status', 'Solicitud cancelada y documentos eliminados correctamente. Ahora puedes enviar una nueva.');
    }

    /**
     * Dashboard del estudiante.
     */
    public function dashboard()
    {
        $userId = Auth::id();

        $solicitud = SolicitudPPS::with('documentos')
            ->where('user_id', $userId)
            ->latest('id')
            ->first();

        return view('estudiantes.dashboard', compact('solicitud'));
    }

    /**
     * 📄 Ver documentos asociados a una solicitud.
     */
    public function verDocumentos($id)
    {
        $solicitud = SolicitudPPS::with('documentos')->findOrFail($id);

        if ($solicitud->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para ver los documentos de esta solicitud.');
        }

        return view('estudiantes.documentos.index', [
            'solicitud'  => $solicitud,
            'documentos' => $solicitud->documentos,
        ]);
    }

    /**
     * 👁️ Ver documento individual.
     */
    public function ver($id)
    {
        $doc = Documento::findOrFail($id);

        if ($doc->solicitud->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        return response()->file(storage_path("app/private/{$doc->ruta}"));
    }

    /**
     * ⬇️ Descargar documento.
     */
    public function descargar($id)
    {
        $doc = Documento::findOrFail($id);

        if ($doc->solicitud->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para descargar este documento.');
        }

        return Storage::disk('private')->download($doc->ruta, $doc->nombre_descarga);
    }

    /**
     * ❌ Eliminar documento.
     */
    public function eliminar($id)
    {
        $doc = Documento::findOrFail($id);

        if ($doc->solicitud->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar este documento.');
        }

        Storage::disk('private')->delete($doc->ruta);
        $doc->delete();

        return back()->with('success', 'Documento eliminado correctamente.');
    }
}