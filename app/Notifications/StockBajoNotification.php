<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Producto;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockBajoNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Producto $producto,
        public readonly float $stockActual,
        public readonly string $bodegaNombre,
        public readonly string $nivel = 'bajo', // 'bajo' | 'agotado'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $titulo = $this->nivel === 'agotado'
            ? "Producto agotado: {$this->producto->nombre}"
            : "Stock bajo: {$this->producto->nombre}";

        $body = "{$this->producto->nombre} — Stock actual: {$this->stockActual} "
              ."(Mín: {$this->producto->stock_minimo}) en bodega {$this->bodegaNombre}";

        return \Filament\Notifications\Notification::make()
            ->title($titulo)
            ->body($body)
            ->icon('heroicon-o-exclamation-triangle')
            ->danger()
            ->getDatabaseMessage();
    }
}
