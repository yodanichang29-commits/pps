<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CalculadoraHorasPPS;
use Illuminate\Support\Facades\Log;

class CalculadoraFechaController extends Controller
{
    protected $calculadora;

    public function __construct(CalculadoraHorasPPS $calculadora)
    {
        $this->calculadora = $calculadora;
    }

   /**
 * Calcula la fecha de finalización basada en 800 horas
 */
public function calcular(Request $request)
{
    try {
        // Validar los datos recibidos
        $request->validate([
            'fecha_inicio' => 'required|date',
            'horario' => 'required|string',
        ]);

        $fechaInicio = $request->input('fecha_inicio');
        $horario = $request->input('horario');

        // Calcular usando el servicio
        $resultado = $this->calculadora->calcularFechaFinalizacion($fechaInicio, $horario);

        // Retornar el resultado con información detallada
        return response()->json([
            'success' => true,
            'fecha_fin' => $resultado['fecha_fin'],
            'dias_laborales' => $resultado['dias_laborales'],
            'horas_por_dia' => $resultado['horas_por_dia'],
            'fines_de_semana' => $resultado['fines_de_semana'],
            'feriados' => $resultado['feriados'],
            'feriados_detalle' => $resultado['feriados_detalle'],
        ]);

    } catch (\InvalidArgumentException $e) {
        return response()->json([
            'success' => false,
            'error' => 'Horario inválido. Use formato: "8:00 AM - 5:00 PM"',
        ], 400);

    } catch (\Exception $e) {
        Log::error('Error al calcular fecha de finalización: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => 'Error al calcular la fecha. Por favor, intente nuevamente.',
        ], 500);
    }
}
}