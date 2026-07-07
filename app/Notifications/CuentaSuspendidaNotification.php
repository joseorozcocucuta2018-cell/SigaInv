<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CuentaSuspendidaNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu cuenta ha sido suspendida')
            ->greeting("Hola, {$notifiable->name}")
            ->line('Tu cuenta en el sistema SigaInv ha sido suspendida por un administrador.')
            ->line('Ya no podrás acceder al sistema hasta que un administrador reactive tu cuenta.')
            ->line('Si crees que esto es un error, por favor contacta al administrador.')
            ->salutation('Sistema SigaInv');
    }
}
