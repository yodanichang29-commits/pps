<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
// Notificaciones personalizadas
use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasProfilePhoto;
    use HasTeams;
    use TwoFactorAuthenticatable;

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'es_supervisor',
        'cod_rol',
        'foto',
        'email_verified_at',
    ];

    /**
     * Atributos ocultos.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Atributos agregados.
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Casts.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'es_supervisor' => 'boolean',
        ];
    }

    /**
     * Notificaciones personalizadas.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

    /**
     * Relaciones PPS
     */
    public function estudiantesAsignados()
    {
        return $this->hasMany(SolicitudPPS::class, 'supervisor_id');
    }

    public function supervisor()
    {
        return $this->hasOne(Supervisor::class);
    }

    public function rolInstitucional()
    {
        return $this->belongsTo(Rol::class, 'cod_rol', 'COD_ROL');
    }

    /**
     * Helpers basados en el campo 'rol'
     */
    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->rol === 'supervisor';
    }

    public function isEstudiante(): bool
    {
        return $this->rol === 'estudiante';
    }

    /**
     * Verifica si el usuario puede supervisar estudiantes
     * (ya sea supervisor de rol O admin con es_supervisor=true)
     */
    public function puedeSuperviar(): bool
    {
        return $this->rol === 'supervisor' || ($this->rol === 'admin' && $this->es_supervisor);
    }

    /**
     * Verifica si tiene estudiantes asignados actualmente
     */
    public function tieneEstudiantesAsignados(): bool
    {
        return $this->supervisor()->exists() &&
               $this->supervisor->solicitudes()->count() > 0;
    }

    /**
     * Helpers usando el rol institucional
     */
    public function esAdminInstitucional(): bool
    {
        return optional($this->rolInstitucional)->NOM_ROL === 'Administrador';
    }

    public function esEstudianteInstitucional(): bool
    {
        return optional($this->rolInstitucional)->NOM_ROL === 'Estudiante';
    }

    public function esSupervisorInstitucional(): bool
    {
        return optional($this->rolInstitucional)->NOM_ROL === 'Supervisor';
    }
}