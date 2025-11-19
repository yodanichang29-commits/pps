<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class CustomVerifyEmail extends BaseVerifyEmail
{
    /**
     * Construye el correo de verificación.
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verificación de Correo Electrónico - PPS UNAH')
            ->greeting('¡Hola, ' . $notifiable->name . '!')
            ->line('Bienvenido/a al **Sistema de Práctica Profesional Supervisada** de la Universidad Nacional Autónoma de Honduras.')
            ->line('Para poder acceder a todas las funcionalidades del sistema, necesitamos verificar tu dirección de correo electrónico institucional.')
            ->line('Por favor, haz clic en el botón a continuación para confirmar tu cuenta:')
            ->action('Verificar Correo Electrónico', $verificationUrl)
            ->line('**Este enlace expirará en 60 minutos.**')
            ->line('Si no creaste una cuenta en nuestro sistema, puedes ignorar este mensaje. No se realizará ninguna acción adicional.')
            ->line('---')
            ->line('Si tienes alguna duda o necesitas asistencia, por favor contacta al administrador del sistema.')
            ->salutation('Atentamente,');
    }

    /**
     * Genera el enlace de verificación firmado.
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
