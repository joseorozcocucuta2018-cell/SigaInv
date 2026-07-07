<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Compra;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CompraConfirmadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Compra $compra,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title("Compra confirmada: {$this->compra->numero}")
            ->body('Proveedor: '.($this->compra->proveedor?->nombre ?? 'N/A').'. Total: $'.number_format($this->compra->total, 0, ',', '.'))
            ->icon('heroicon-o-clipboard-document-check')
            ->success()
            ->getDatabaseMessage();
    }
}
