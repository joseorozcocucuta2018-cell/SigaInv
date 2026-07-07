<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FacturaPorVencerNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Venta $venta,
        public readonly int $diasRestantes,
    ) {}

    public function via(object $notifiable): array
    {
        return $notifiable instanceof User ? ['database'] : ['mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = "La factura {$this->venta->numero} del cliente {$this->venta->cliente?->nombre} "
              ."vence en {$this->diasRestantes} día(s). "
              .'Saldo pendiente: $'.number_format($this->venta->saldo_pendiente, 0, ',', '.');

        return \Filament\Notifications\Notification::make()
            ->title("Factura por vencer: {$this->venta->numero}")
            ->body($body)
            ->icon('heroicon-o-document-text')
            ->warning()
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $empresa = Empresa::actual();

        return (new MailMessage)
            ->subject("Recordatorio: Factura {$this->venta->numero} vence en {$this->diasRestantes} día(s)")
            ->view('emails.factura_recordatorio', [
                'venta' => $this->venta,
                'empresa' => $empresa,
                'diasRestantes' => $this->diasRestantes,
                'tipo' => 'por_vencer',
            ]);
    }
}
