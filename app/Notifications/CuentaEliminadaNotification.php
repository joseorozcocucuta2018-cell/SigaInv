<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CuentaEliminadaNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu cuenta ha sido eliminada - SigaInv')
            ->greeting("Hola, {$notifiable->name}")
            ->line('Tu cuenta en el sistema SigaInv ha sido eliminada por un administrador.')
            ->line('Si crees que esto es un error, comunícate con el administrador del sistema.')
            ->salutation('Equipo SigaInv');
    }
}
