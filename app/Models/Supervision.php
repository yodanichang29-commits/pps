<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervision extends Model
{
    use HasFactory;

    protected $table = 'supervisiones';

    protected $fillable = [
        'solicitud_pps_id',      // ✅ Ajustado
        'numero_supervision',     // ✅ Ajustado
        'comentario',
        'archivo',                // ✅ Ajustado
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relación con la solicitud PPS
     */
    public function solicitud()
    {
        return $this->belongsTo(SolicitudPPS::class, 'solicitud_pps_id');
    }

    /**
     * Relación con el supervisor (a través de la solicitud)
     */
    public function supervisor()
    {
        return $this->solicitud->supervisor ?? null;
    }

    /**
     * Obtener la fecha de supervisión (usa created_at)
     */
    public function getFechaSupervisionAttribute()
    {
        return $this->created_at;
    }
}