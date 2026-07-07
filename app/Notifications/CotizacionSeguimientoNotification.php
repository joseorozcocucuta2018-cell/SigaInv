<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Cotizacion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CotizacionSeguimientoNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Cotizacion $cotizacion,
        public readonly int $diasPendiente,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = "La cotización {$this->cotizacion->numero} del cliente "
              ."{$this->cotizacion->cliente?->nombre} lleva "
              ."{$this->diasPendiente} días pendiente sin respuesta.";

        return \Filament\Notifications\Notification::make()
            ->title("Cotización sin respuesta: {$this->cotizacion->numero}")
            ->body($body)
            ->icon('heroicon-o-document-check')
            ->info()
            ->getDatabaseMessage();
    }
}
