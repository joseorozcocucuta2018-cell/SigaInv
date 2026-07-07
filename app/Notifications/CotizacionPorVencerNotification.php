<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Cotizacion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CotizacionPorVencerNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Cotizacion $cotizacion,
        public readonly int $diasRestantes,
        public readonly string $tipo = 'por_vencer', // 'por_vencer' | 'vencida'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        if ($this->tipo === 'vencida') {
            $titulo = "Cotización vencida: {$this->cotizacion->numero}";
            $body = "La cotización {$this->cotizacion->numero} del cliente "
                     ."{$this->cotizacion->cliente?->nombre} venció sin respuesta.";
        } else {
            $titulo = "Cotización por vencer: {$this->cotizacion->numero}";
            $body = "La cotización {$this->cotizacion->numero} del cliente "
                     ."{$this->cotizacion->cliente?->nombre} vence en "
                     ."{$this->diasRestantes} día(s) ({$this->cotizacion->fecha_vigencia?->format('d/m/Y')}).";
        }

        return \Filament\Notifications\Notification::make()
            ->title($titulo)
            ->body($body)
            ->icon('heroicon-o-document-check')
            ->warning()
            ->getDatabaseMessage();
    }
}
