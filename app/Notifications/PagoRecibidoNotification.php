<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Empresa;
use App\Models\PagoCliente;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PagoRecibidoNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly PagoCliente $pago,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $empresa = Empresa::actual();
        $venta = $this->pago->venta;
        $cliente = $this->pago->cliente;
        $saldo = $venta?->saldo_pendiente ?? 0;

        return (new MailMessage)
            ->subject('Confirmación de pago recibido — '.($empresa?->nombre_comercial ?? 'SigaWeb'))
            ->greeting("Estimado(a) {$cliente?->nombre},")
            ->line('Hemos recibido su pago por $'.number_format($this->pago->monto, 0, ',', '.')." el {$this->pago->fecha?->format('d/m/Y')}.")
            ->when($venta, fn ($m) => $m->line("Factura: {$venta->numero}"))
            ->line('Saldo pendiente: $'.number_format($saldo, 0, ',', '.'))
            ->when($saldo <= 0,
                fn ($m) => $m->line('Su factura ha sido cancelada en su totalidad. ¡Gracias!'),
                fn ($m) => $m->line('Le recordamos que aún tiene un saldo pendiente.')
            )
            ->salutation('Atentamente, '.($empresa?->razon_social ?? 'SigaWeb'));
    }
}
