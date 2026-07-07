<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Compra;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CompraPorVencerNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Compra $compra,
        public readonly int $diasRestantes,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = "La compra {$this->compra->numero} al proveedor {$this->compra->proveedor?->nombre} "
              ."vence en {$this->diasRestantes} día(s). "
              .'Saldo pendiente: $'.number_format($this->compra->saldo_pendiente, 0, ',', '.');

        return \Filament\Notifications\Notification::make()
            ->title("Compra por vencer: {$this->compra->numero}")
            ->body($body)
            ->icon('heroicon-o-shopping-cart')
            ->warning()
            ->getDatabaseMessage();
    }
}
