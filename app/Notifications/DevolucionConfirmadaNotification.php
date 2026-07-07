<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Devolucion;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DevolucionConfirmadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Devolucion $devolucion,
    ) {}

    public function via(object $notifiable): array
    {
        return $notifiable instanceof User ? ['database'] : ['mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = "Se confirmó la devolución {$this->devolucion->numero} del cliente "
              ."{$this->devolucion->cliente?->nombre}. "
              .'Total: $'.number_format($this->devolucion->total, 0, ',', '.');

        return \Filament\Notifications\Notification::make()
            ->title("Devolución confirmada: {$this->devolucion->numero}")
            ->body($body)
            ->icon('heroicon-o-arrow-uturn-left')
            ->info()
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $empresa = Empresa::actual();
        $dev = $this->devolucion;

        return (new MailMessage)
            ->subject("Devolución {$dev->numero} confirmada — ".($empresa?->nombre_comercial ?? 'SigaWeb'))
            ->greeting("Estimado(a) {$dev->cliente?->nombre},")
            ->line("Le informamos que su devolución **{$dev->numero}** ha sido procesada exitosamente.")
            ->line("Motivo: {$dev->motivo}")
            ->line('Valor procesado: $'.number_format($dev->total, 0, ',', '.'))
            ->line('El crédito ha sido aplicado a su cuenta.')
            ->salutation('Atentamente, '.($empresa?->razon_social ?? 'SigaWeb'));
    }
}
