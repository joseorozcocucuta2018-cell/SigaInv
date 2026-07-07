<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Venta;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VentaConfirmadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Venta $venta,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title("Venta confirmada: {$this->venta->numero}")
            ->body('Cliente: '.($this->venta->cliente?->nombre ?? 'N/A').'. Total: $'.number_format($this->venta->total, 0, ',', '.'))
            ->icon('heroicon-o-shopping-cart')
            ->success()
            ->getDatabaseMessage();
    }
}
