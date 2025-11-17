<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formato;
use Illuminate\Support\Facades\Storage;

class FormatoController extends Controller
{
    /**
     * Mostrar listado de formatos visibles (estudiantes)
     */
    public function index()
    {
        $formatos = Formato::where('visible', 1)
                          ->orderBy('nombre', 'asc')
                          ->get();
        
        return view('estudiantes.formatos', compact('formatos'));
    }

    /**
     * Descargar un formato específico
     */
    public function download($id)
    {
        $formato = Formato::findOrFail($id);

        // 🔹 Construir ruta completa
        $rutaCompleta = public_path($formato->ruta);
        
        // 🔹 Verificar si existe
        if (!file_exists($rutaCompleta)) {
            return back()->with('error', 'El archivo no existe en el servidor: ' . $formato->nombre);
        }

        // 🔹 Descargar con nombre legible
        $nombreDescarga = $formato->nombre . '.' . pathinfo($formato->ruta, PATHINFO_EXTENSION);
        
        return response()->download($rutaCompleta, $nombreDescarga);
    }

    /**
     * Ver formato en línea (para PDFs)
     */
    public function view($id)
    {
        $formato = Formato::findOrFail($id);
        
        $rutaCompleta = public_path($formato->ruta);
        
        if (!file_exists($rutaCompleta)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->file($rutaCompleta);
    }
}