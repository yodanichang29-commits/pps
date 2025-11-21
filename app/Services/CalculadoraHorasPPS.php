<?php

namespace App\Services;

use Carbon\Carbon;

class CalculadoraHorasPPS
{
    /**
     * Feriados fijos de Honduras (formato: mes-dia)
     * Fuente: Días festivos oficiales de Honduras
     */
    private const FERIADOS_FIJOS = [
        '01-01', // Año Nuevo
        '04-14', // Día de las Américas
        '05-01', // Día del Trabajo
        '09-15', // Día de la Independencia
        '10-03', // Día de Francisco Morazán
        '10-12', // Día de la Raza
        '10-21', // Día de las Fuerzas Armadas
        '12-25', // Navidad
    ];

    /**
     * Feriados móviles (Semana Santa) - se calculan cada año
     * Jueves, Viernes y Sábado Santo
     */
    private function obtenerSemanaSanta(int $anio): array
    {
        // Algoritmo de Gauss para calcular la Pascua
        $a = $anio % 19;
        $b = intdiv($anio, 100);
        $c = $anio % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        $domingoPascua = Carbon::create($anio, $mes, $dia);

        return [
            $domingoPascua->copy()->subDays(3)->format('Y-m-d'), // Jueves Santo
            $domingoPascua->copy()->subDays(2)->format('Y-m-d'), // Viernes Santo
            $domingoPascua->copy()->subDays(1)->format('Y-m-d'), // Sábado Santo
        ];
    }

    /**
     * Verifica si una fecha es feriado en Honduras
     */
    private function esFeriado(Carbon $fecha): bool
    {
        $mesdia = $fecha->format('m-d');
        
        // Verificar feriados fijos
        if (in_array($mesdia, self::FERIADOS_FIJOS)) {
            return true;
        }

        // Verificar Semana Santa
        $semanaSanta = $this->obtenerSemanaSanta($fecha->year);
        if (in_array($fecha->format('Y-m-d'), $semanaSanta)) {
            return true;
        }

        return false;
    }

/**
 * Calcula la fecha de finalización basada en 800 horas
 * 
 * @param string $fechaInicio Fecha de inicio (Y-m-d)
 * @param string $horario Horario en formato "8:00 AM - 5:00 PM"
 * @return array ['fecha_fin' => '2024-12-31', 'dias_laborales' => 100, ...]
 */
public function calcularFechaFinalizacion(string $fechaInicio, string $horario): array
{
    $horasPorDia = $this->extraerHorasPorDia($horario);
    
    if ($horasPorDia <= 0) {
        throw new \InvalidArgumentException('El horario no es válido. Debe tener formato "8:00 AM - 5:00 PM"');
    }

    $horasRestantes = 800;
    $fecha = Carbon::parse($fechaInicio);
    $diasLaborales = 0;
    $finesDeSemana = 0;
    $feriadosEncontrados = [];

    // Calcular días necesarios para completar 800 horas
    while ($horasRestantes > 0) {
        // Si es fin de semana
        if ($fecha->isWeekend()) {
            $finesDeSemana++;
        }
        // Si es feriado en día hábil
        elseif ($fecha->isWeekday() && $this->esFeriado($fecha)) {
            $feriadosEncontrados[] = $fecha->format('Y-m-d');
        }
        // Si es día laboral normal (lunes a viernes, no feriado)
        elseif ($fecha->isWeekday() && !$this->esFeriado($fecha)) {
            $horasRestantes -= $horasPorDia;
            $diasLaborales++;
        }
        
        // Avanzar al siguiente día
        $fecha->addDay();
    }

    // Retroceder un día porque el while avanza uno de más
    $fecha->subDay();

    // IMPORTANTE: Si la fecha final cae en fin de semana o feriado,
    // avanzar hasta el siguiente día hábil
    while ($fecha->isWeekend() || $this->esFeriado($fecha)) {
        if ($fecha->isWeekend()) {
            $finesDeSemana++;
        } elseif ($this->esFeriado($fecha)) {
            if (!in_array($fecha->format('Y-m-d'), $feriadosEncontrados)) {
                $feriadosEncontrados[] = $fecha->format('Y-m-d');
            }
        }
        $fecha->addDay();
    }

    return [
        'fecha_fin' => $fecha->format('Y-m-d'),
        'dias_laborales' => $diasLaborales,
        'horas_por_dia' => $horasPorDia,
        'fines_de_semana' => $finesDeSemana,
        'feriados' => count($feriadosEncontrados),
        'feriados_detalle' => $feriadosEncontrados,
    ];
}


 /**
     * Extrae las horas laborales por día desde el string de horario
 * Ejemplo: "8:00 AM - 5:00 PM" -> 9 horas (considera 1 hora de almuerzo)
 * Ejemplo: "7:00 AM - 3:00 PM" -> 8 horas
 */
private function extraerHorasPorDia(string $horario): float
{
    // Limpiar el string
    $horario = trim($horario);
    
    // Intentar diferentes formatos
    // Formato: "8:00 AM - 5:00 PM"
    if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)\s*-\s*(\d{1,2}):(\d{2})\s*(AM|PM)/i', $horario, $matches)) {
        $horaInicio = $this->convertirA24Horas((int)$matches[1], (int)$matches[2], $matches[3]);
        $horaFin = $this->convertirA24Horas((int)$matches[4], (int)$matches[5], $matches[6]);
        
        $horasTotales = $horaFin - $horaInicio;
        
        // Restar 1 hora de almuerzo si trabaja más de 6 horas
        if ($horasTotales > 6) {
            $horasTotales -= 1;
        }
        
        return $horasTotales;
    }

    // Formato alternativo: "8-5" o "8 a 5"
    if (preg_match('/(\d{1,2})\s*[-a]\s*(\d{1,2})/', $horario, $matches)) {
        $horasTotales = (int)$matches[2] - (int)$matches[1];
        
        // Restar 1 hora de almuerzo si trabaja más de 6 horas
        if ($horasTotales > 6) {
            $horasTotales -= 1;
        }
        
        return $horasTotales;
    }

    // Si no se puede parsear, retornar 8 horas por defecto
    return 8.0;
}

    /**
     * Convierte hora de formato 12 horas a 24 horas
     */
    private function convertirA24Horas(int $hora, int $minutos, string $periodo): float
    {
        $periodo = strtoupper(trim($periodo));
        
        if ($periodo === 'PM' && $hora !== 12) {
            $hora += 12;
        } elseif ($periodo === 'AM' && $hora === 12) {
            $hora = 0;
        }

        return $hora + ($minutos / 60);
    }
}