<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccesoRechazadoNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu solicitud de acceso no fue aprobada - SigaInv')
            ->greeting("Hola, {$notifiable->name}")
            ->line('Lamentamos informarte que tu solicitud de acceso al sistema SigaInv no fue aprobada.')
            ->line('Si crees que esto es un error o tienes preguntas, por favor contacta directamente al administrador.')
            ->salutation('Equipo SigaInv');
    }
}
