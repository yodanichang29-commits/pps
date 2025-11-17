<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudPPS extends Model
{
    use HasFactory, SoftDeletes;

    /** Tabla real */
    protected $table = 'solicitud_p_p_s';

    /** Asignación masiva (coincide con columnas reales) */
    protected $fillable = [
        'user_id',
        'tipo_practica',
        'modalidad',
        'numero_cuenta',
        'nombre_empresa',
        'direccion_empresa',
        'nombre_jefe',
        'numero_jefe',
        'correo_jefe',
        'puesto_trabajo',
        'anios_trabajando',
        'fecha_inicio',
        'fecha_fin',
        'horario',
        'estado_solicitud',
        'observacion',
        'observaciones',
        'motivo_cancelacion',
        'supervisor_id',
        'telefono_alumno',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    /** Estados como constantes */
    public const EST_SOLICITADA  = 'SOLICITADA';
    public const EST_APROBADA    = 'APROBADA';
    public const EST_RECHAZADA   = 'RECHAZADA';
    public const EST_CANCELADA   = 'CANCELADA';
    public const EST_FINALIZADA  = 'FINALIZADA';
    public const EST_EN_PROCESO  = 'EN_PROCESO';

    /** ----- Relaciones ----- */

    /**
     * Relación: Una solicitud tiene muchos documentos
     */
    public function documentos()
    {
        return $this->hasMany(Documento::class, 'solicitud_pps_id', 'id')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Relación: Una solicitud pertenece a un usuario (estudiante)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Una solicitud pertenece a un supervisor
     */
    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id');
    }

    /**
     * Relación: Una solicitud tiene muchas supervisiones
     */
    public function supervisiones()
    {
        return $this->hasMany(Supervision::class, 'solicitud_pps_id', 'id')
                    ->orderBy('created_at', 'asc');
    }

    /** ----- Scopes útiles ----- */

    public function scopeDelUsuario(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    public function scopeActivas(Builder $q): Builder
    {
        return $q->whereIn('estado_solicitud', [
            self::EST_SOLICITADA,
            self::EST_APROBADA,
            self::EST_EN_PROCESO,
        ]);
    }

    public function scopeNoFinalizadas(Builder $q): Builder
    {
        return $q->whereNotIn('estado_solicitud', [self::EST_CANCELADA, self::EST_FINALIZADA]);
    }

    /** ----- Helpers de estado ----- */

    public function getEsActivaAttribute(): bool
    {
        return in_array($this->estado_solicitud, [
            self::EST_SOLICITADA,
            self::EST_APROBADA,
            self::EST_EN_PROCESO,
        ], true);
    }

    public function getPuedeCancelarAttribute(): bool
    {
        return in_array($this->estado_solicitud, [
            self::EST_SOLICITADA,
            self::EST_APROBADA,
            self::EST_RECHAZADA,
            self::EST_EN_PROCESO,
        ], true);
    }

    /** ----- Helpers para el dashboard ----- */

    public function progresoDocumentos(array $requeridos): array
    {
        $tiposCargados = $this->documentos()->pluck('tipo')->all();
        $completados   = array_values(array_intersect($requeridos, $tiposCargados));

        return [
            'requeridos'  => array_values($requeridos),
            'completados' => $completados,
            'porcentaje'  => count($requeridos)
                ? round(count($completados) * 100 / count($requeridos))
                : 0,
        ];
    }

    public function getObservacionesAttribute($value)
    {
        if (!is_null($value)) {
            return $value;
        }
        return $this->attributes['observacion'] ?? null;
    }
}