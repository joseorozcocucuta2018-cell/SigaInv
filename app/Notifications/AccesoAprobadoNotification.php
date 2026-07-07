<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccesoAprobadoNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title('Tu acceso ha sido aprobado')
            ->body('Tu cuenta ha sido aprobada por un administrador. Ya puedes iniciar sesión con tus credenciales.')
            ->icon('heroicon-o-check-circle')
            ->success()
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rol = $notifiable->roles->first()?->name ?? 'sin rol';

        return (new MailMessage)
            ->subject('Tu acceso ha sido aprobado - SigaInv')
            ->greeting("Hola, {$notifiable->name}")
            ->line('Tu solicitud de acceso al sistema ha sido aprobada por un administrador.')
            ->line("Rol asignado: {$rol}")
            ->action('Iniciar sesión ahora', url('/admin'))
            ->line('Si tienes alguna duda, contacta al administrador del sistema.')
            ->salutation('Equipo SigaInv');
    }
}
