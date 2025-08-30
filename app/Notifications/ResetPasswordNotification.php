<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    /**
     * Token de reseteo.
     *
     * @var string
     */
    public string $token;

    /**
     * Crea la notificación con el token.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Canales.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Contenido del correo.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)

            ->subject('Restablecer tu contraseña - Academia Internacional de Globos')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Recibiste este correo porque se solicitó un restablecimiento de contraseña para tu cuenta.')
            ->action('Cambiar Contraseña', url(route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false)))
            
            ->line('Si no solicitaste un restablecimiento de contraseña, no es necesario realizar ninguna acción.')
            ->salutation('Saludos, Academia Internacional de Globos '  );



    }
}
