<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Supervision;
use App\Models\SolicitudPPS;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ReportesExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupervisorReporteController extends Controller
{
    /**
     * Mostrar índice de reportes
     */
    public function index(Request $request)
    {
        $supervisor = Auth::user()->supervisor;
        
        // Validar que el usuario tenga perfil de supervisor
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                            ->with('error', 'No tienes perfil de supervisor');
        }

        // Filtros simplificados
        $año = $request->get('año', now()->year);
        $estudiante = $request->get('estudiante');

        // Query base: solicitudes finalizadas del supervisor
        $query = SolicitudPPS::where('supervisor_id', $supervisor->id)
                            ->where('estado_solicitud', 'FINALIZADA')
                            ->whereYear('updated_at', $año)
                            ->with('user', 'supervisiones');

        // Filtrar por estudiante (nombre o email)
        if ($estudiante) {
            $query->whereHas('user', function($q) use ($estudiante) {
                $q->where('name', 'like', "%{$estudiante}%")
                  ->orWhere('email', 'like', "%{$estudiante}%");
            });
        }

        $solicitudes = $query->orderBy('updated_at', 'desc')->paginate(10);

        // Estadísticas
        $estadisticas = [
            'total_finalizadas' => SolicitudPPS::where('supervisor_id', $supervisor->id)
                                              ->where('estado_solicitud', 'FINALIZADA')
                                              ->count(),
            'total_supervisiones' => Supervision::whereIn('solicitud_pps_id',
                                                 SolicitudPPS::where('supervisor_id', $supervisor->id)->pluck('id')
                                               )->count(),
            'promedio_duracion' => $this->calcularPromedioDuracion($supervisor->id),
            'estudiantes_activos' => SolicitudPPS::where('supervisor_id', $supervisor->id)
                                                 ->where('estado_solicitud', 'APROBADA')
                                                 ->count(),
        ];

        return view('supervisor.reportes.index', [
            'solicitudes' => $solicitudes,
            'estadisticas' => $estadisticas,
            'supervisor' => $supervisor,
            'filtros' => [
                'año' => $año,
                'estudiante' => $estudiante,
            ],
        ]);
    }

    /**
     * Exportar a xlsx
     */
    public function exportExcel(Request $request)
    {
        try {
            $supervisor = Auth::user()->supervisor;
            $año = $request->get('año', now()->year);
            $estudiante = $request->get('estudiante');

            $query = SolicitudPPS::where('supervisor_id', $supervisor->id)
                ->where('estado_solicitud', 'FINALIZADA')
                ->whereYear('updated_at', $año)
                ->with('user', 'supervisiones');

            if ($estudiante) {
                $query->whereHas('user', function ($q) use ($estudiante) {
                    $q->where('name', 'like', "%{$estudiante}%")
                      ->orWhere('email', 'like', "%{$estudiante}%");
                });
            }

            $solicitudes = $query->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet()->setTitle('Reportes');

            // NUEVOS ENCABEZADOS (sin Fecha Fin)
            $headings = [
                'Estudiante','Email','Empresa','Puesto',
                'Fecha Inicio','Estado','Supervisiones','Fecha Finalización'
            ];
            $sheet->fromArray($headings, null, 'A1');

            // Datos (omitimos fecha_fin)
            $r = 2;
            foreach ($solicitudes as $s) {
                $sheet->setCellValue("A{$r}", optional($s->user)->name);
                $sheet->setCellValue("B{$r}", optional($s->user)->email);
                $sheet->setCellValue("C{$r}", $s->nombre_empresa);
                $sheet->setCellValue("D{$r}", $s->puesto_trabajo);
                if ($s->fecha_inicio) {
                    $sheet->setCellValue("E{$r}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($s->fecha_inicio));
                }
                $sheet->setCellValue("F{$r}", $s->estado_solicitud);
                $sheet->setCellValue("G{$r}", $s->supervisiones? $s->supervisiones->count():0);
                $sheet->setCellValue("H{$r}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($s->updated_at));
                $r++;
            }
            $last = $r - 1;

            // Formatos fecha (solo Inicio y Finalización)
            foreach (['E','H'] as $col) {
                $sheet->getStyle("{$col}2:{$col}{$last}")
                      ->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            }

            // Estilo encabezado
            $sheet->getStyle('A1:H1')->applyFromArray([
                'font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],
                'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'1e40af']],
                'alignment'=>['horizontal'=>'center','vertical'=>'center'],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(24);

            // Auto ancho
            foreach (range('A','H') as $c) {
                $sheet->getColumnDimension($c)->setAutoSize(true);
            }

            // Bordes
            $sheet->getStyle("A1:H{$last}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('DDDDDD');

            // Zebra
            for ($i=2; $i <= $last; $i++) {
                if ($i % 2 === 0) {
                    $sheet->getStyle("A{$i}:H{$i}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F7FAFC');
                }
            }

            // Resaltar estado (columna F ahora)
            for ($i=2; $i <= $last; $i++) {
                if (strtoupper($sheet->getCell("F{$i}")->getValue()) === 'FINALIZADA') {
                    $sheet->getStyle("F{$i}")->getFont()->setBold(true)->getColor()->setRGB('166534');
                }
            }

            // Filtro y congelar
            $sheet->setAutoFilter("A1:H1");
            $sheet->freezePane('A2');

            $writer = new Xlsx($spreadsheet);
            $file = "reportes_supervisor_{$año}.xlsx";

            return new StreamedResponse(function () use ($writer) {
                $writer->save('php://output');
            }, 200, [
                'Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition'=>"attachment; filename=\"{$file}\"",
                'Cache-Control'=>'max-age=0',
            ]);

        } catch (\Throwable $e) {
            return back()->with('error','Error al exportar: '.$e->getMessage());
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $supervisor = Auth::user()->supervisor;
            
            $año = $request->get('año', now()->year);
            $estudiante = $request->get('estudiante');

            $query = SolicitudPPS::where('supervisor_id', $supervisor->id)
                                ->where('estado_solicitud', 'FINALIZADA')
                                ->whereYear('updated_at', $año)
                                ->with('user', 'supervisiones');

            if ($estudiante) {
                $query->whereHas('user', function($q) use ($estudiante) {
                    $q->where('name', 'like', "%{$estudiante}%")
                      ->orWhere('email', 'like', "%{$estudiante}%");
                });
            }

            $solicitudes = $query->get();
            $estadisticas = [
                'total' => $solicitudes->count(),
                'supervisiones' => $solicitudes->sum(fn($s) => $s->supervisiones->count()),
            ];

            $pdf = Pdf::loadView('supervisor.reportes.pdf', [
                'solicitudes' => $solicitudes,
                'supervisor' => $supervisor,
                'año' => $año,
                'estadisticas' => $estadisticas,
            ]);

            return $pdf->download("reportes_supervisor_{$año}.pdf");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Calcular promedio de duración de prácticas
     */
    private function calcularPromedioDuracion($supervisorId)
    {
        $solicitudes = SolicitudPPS::where('supervisor_id', $supervisorId)
                                   ->where('estado_solicitud', 'FINALIZADA')
                                   ->whereNotNull('fecha_inicio')
                                   ->whereNotNull('fecha_fin')
                                   ->get();

        if ($solicitudes->isEmpty()) {
            return 0;
        }

        $duracionTotal = $solicitudes->sum(function($s) {
            if (!$s->fecha_fin || !$s->fecha_inicio) {
                return 0;
            }
            return $s->fecha_fin->diffInDays($s->fecha_inicio);
        });

        if ($solicitudes->count() === 0) {
            return 0;
        }

        return round($duracionTotal / $solicitudes->count());
    }
}