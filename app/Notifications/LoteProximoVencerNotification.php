<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoteProximoVencerNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Producto $producto,
        public readonly string $lote,
        public readonly Carbon $fechaVencimiento,
        public readonly float $cantidad,
        public readonly string $bodegaNombre,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $diasRestantes = now()->diffInDays($this->fechaVencimiento, false);

        $body = "Lote {$this->lote} de {$this->producto->nombre} "
              ."vence el {$this->fechaVencimiento->format('d/m/Y')} "
              ."({$diasRestantes} días). Cantidad: {$this->cantidad} en {$this->bodegaNombre}";

        return \Filament\Notifications\Notification::make()
            ->title("Lote próximo a vencer: {$this->producto->nombre}")
            ->body($body)
            ->icon('heroicon-o-clock')
            ->warning()
            ->getDatabaseMessage();
    }
}
