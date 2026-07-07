<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistroPendienteNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu solicitud de acceso fue recibida - SigaInv')
            ->greeting("Hola, {$notifiable->name}")
            ->line('Hemos recibido tu solicitud de acceso al sistema SigaInv.')
            ->line('Un administrador revisará tu solicitud y activará tu cuenta en breve.')
            ->line('Recibirás un correo de confirmación cuando tu acceso sea aprobado.')
            ->action('Ir al inicio de sesión', url('/admin/login'))
            ->line('Si no solicitaste este acceso, puedes ignorar este mensaje.')
            ->salutation('Equipo SigaInv');
    }
}
