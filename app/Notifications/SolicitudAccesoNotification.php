<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitudAccesoNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly User $solicitante) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title("Solicitud de acceso: {$this->solicitante->name}")
            ->body("El usuario {$this->solicitante->name} ({$this->solicitante->email}) solicita acceso. Cargo/Motivo: {$this->solicitante->cargo}")
            ->icon('heroicon-o-user-plus')
            ->warning()
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/admin/users/'.$this->solicitante->id.'/edit');

        return (new MailMessage)
            ->subject("Nueva solicitud de acceso: {$this->solicitante->name}")
            ->greeting("Hola, {$notifiable->name}")
            ->line("El usuario **{$this->solicitante->name}** ha solicitado acceso al sistema.")
            ->line("Correo: {$this->solicitante->email}")
            ->line("Cargo / Motivo: {$this->solicitante->cargo}")
            ->action('Revisar y aprobar', $url)
            ->line('Puedes aprobar o rechazar la solicitud desde el panel de administración.')
            ->salutation('Sistema SigaInv');
    }
}
