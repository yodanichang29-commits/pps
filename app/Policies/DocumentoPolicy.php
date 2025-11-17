<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Documento;

class DocumentoPolicy
{
    /**
     * Determina si el usuario puede ver/descargar un documento.
     */
    public function view(User $user, Documento $documento): bool
    {
        // Si la solicitud está cancelada, nadie puede ver documentos
        if ($documento->solicitud->estado_solicitud === \App\Models\SolicitudPPS::EST_CANCELADA) {
            return false;
        }

        // Estudiante dueño de la solicitud
        if ($documento->solicitud->user_id === $user->id) {
            return true;
        }

        // Supervisor asignado a la solicitud
        if ($documento->solicitud->supervisor_id && $user->id === $documento->solicitud->supervisor_id) {
            return true;
        }

        // Admin puede ver todo (vinculación se maneja con rol 'admin' también)
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }
}