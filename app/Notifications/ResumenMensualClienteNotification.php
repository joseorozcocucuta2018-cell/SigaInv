<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ResumenMensualClienteNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Cliente $cliente,
        public readonly string $mes,        // Ej: "Febrero 2026"
        public readonly Collection $ventas,
        public readonly Collection $pagos,
        public readonly Collection $devoluciones,
        public readonly float $saldoPendiente,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $empresa = Empresa::actual();

        return (new MailMessage)
            ->subject("Estado de cuenta {$this->mes} — ".($empresa?->nombre_comercial ?? 'SigaWeb'))
            ->view('emails.resumen_mensual', [
                'cliente' => $this->cliente,
                'empresa' => $empresa,
                'mes' => $this->mes,
                'ventas' => $this->ventas,
                'pagos' => $this->pagos,
                'devoluciones' => $this->devoluciones,
                'saldoPendiente' => $this->saldoPendiente,
            ]);
    }
}
