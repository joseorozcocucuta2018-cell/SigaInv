<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Compra;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CompraVencidaNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Compra $compra,
        public readonly int $diasVencida,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = $this->diasVencida === 0
            ? "La compra {$this->compra->numero} vence HOY. Saldo: $".number_format($this->compra->saldo_pendiente, 0, ',', '.')
            : "La compra {$this->compra->numero} lleva {$this->diasVencida} día(s) vencida. Saldo: $".number_format($this->compra->saldo_pendiente, 0, ',', '.');

        return \Filament\Notifications\Notification::make()
            ->title("Compra vencida: {$this->compra->numero}")
            ->body($body)
            ->icon('heroicon-o-shopping-cart')
            ->danger()
            ->getDatabaseMessage();
    }
}
