<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Remision;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RemisionConfirmadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Remision $remision,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title("Remisión confirmada: {$this->remision->numero}")
            ->body('Cliente: '.($this->remision->cliente?->nombre ?? 'N/A').'. Total: $'.number_format($this->remision->total, 0, ',', '.'))
            ->icon('heroicon-o-truck')
            ->success()
            ->getDatabaseMessage();
    }
}
