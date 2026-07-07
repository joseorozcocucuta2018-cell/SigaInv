<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FacturaVencidaNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Venta $venta,
        public readonly int $diasVencida, // 0 = vence hoy, >0 = días de mora
    ) {}

    public function via(object $notifiable): array
    {
        return $notifiable instanceof User ? ['database'] : ['mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = $this->diasVencida === 0
            ? "La factura {$this->venta->numero} vence HOY. Saldo: $".number_format($this->venta->saldo_pendiente, 0, ',', '.')
            : "La factura {$this->venta->numero} lleva {$this->diasVencida} día(s) vencida. Saldo: $".number_format($this->venta->saldo_pendiente, 0, ',', '.');

        return \Filament\Notifications\Notification::make()
            ->title("Factura vencida: {$this->venta->numero}")
            ->body($body)
            ->icon('heroicon-o-document-text')
            ->danger()
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $empresa = Empresa::actual();
        $asunto = $this->diasVencida === 0
            ? "Factura {$this->venta->numero} vence hoy — Pago requerido"
            : "Factura {$this->venta->numero} vencida ({$this->diasVencida} días de mora)";

        return (new MailMessage)
            ->subject($asunto)
            ->view('emails.factura_recordatorio', [
                'venta' => $this->venta,
                'empresa' => $empresa,
                'diasRestantes' => -$this->diasVencida,
                'tipo' => 'vencida',
            ]);
    }
}
