<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\CotizacionEstado;
use App\Models\Cotizacion;
use App\Models\User;
use App\Notifications\CotizacionPorVencerNotification;
use App\Notifications\CotizacionSeguimientoNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EnviarAlertasCotizaciones extends Command
{
    protected $signature = 'alertas:cotizaciones';

    protected $description = 'Envía alertas internas de cotizaciones que requieren seguimiento o están por vencer';

    public function handle(): int
    {
        $hoy = now()->toDateString();
        $destinatarios = User::role(['vendedor', 'administrador'])->get();

        if ($destinatarios->isEmpty()) {
            $this->warn('No hay usuarios con rol vendedor o administrador para notificar.');

            return self::SUCCESS;
        }

        // Solo cotizaciones pendientes con fecha_vigencia o sin respuesta
        $cotizaciones = Cotizacion::with('cliente')
            ->where('estado', CotizacionEstado::PENDIENTE->value)
            ->get();

        $seguimiento = 0;
        $porVencer = 0;
        $vencidas = 0;

        foreach ($cotizaciones as $cotizacion) {
            $diasPendiente = $cotizacion->created_at->diffInDays(now());

            // --- Seguimiento: pendiente sin respuesta > 5 días ---
            if ($diasPendiente > 5) {
                $cacheKey = "alerta_cotizacion_seguimiento_{$cotizacion->id}_{$hoy}";
                if (! Cache::has($cacheKey)) {
                    $notif = new CotizacionSeguimientoNotification($cotizacion, $diasPendiente);
                    foreach ($destinatarios as $user) {
                        $user->notify($notif);
                    }
                    Cache::put($cacheKey, true, now()->addHours(20));
                    $seguimiento++;
                }
            }

            // --- Vigencia: cotización con fecha_vigencia definida ---
            if (! $cotizacion->fecha_vigencia) {
                continue;
            }

            $diasVigencia = now()->startOfDay()->diffInDays($cotizacion->fecha_vigencia, false);

            if ($diasVigencia < 0) {
                // Vencida (fecha_vigencia ya pasó) — marcar como vencida y notificar
                $cacheKey = "alerta_cotizacion_vencida_{$cotizacion->id}_{$hoy}";
                if (! Cache::has($cacheKey)) {
                    $cotizacion->update(['estado' => CotizacionEstado::VENCIDA->value]);
                    $notif = new CotizacionPorVencerNotification($cotizacion, 0, 'vencida');
                    foreach ($destinatarios as $user) {
                        $user->notify($notif);
                    }
                    Cache::put($cacheKey, true, now()->addHours(20));
                    $vencidas++;
                }
            } elseif ($diasVigencia <= 2) {
                // Por vencer en 0-2 días
                $cacheKey = "alerta_cotizacion_por_vencer_{$cotizacion->id}_{$hoy}";
                if (! Cache::has($cacheKey)) {
                    $notif = new CotizacionPorVencerNotification($cotizacion, $diasVigencia, 'por_vencer');
                    foreach ($destinatarios as $user) {
                        $user->notify($notif);
                    }
                    Cache::put($cacheKey, true, now()->addHours(20));
                    $porVencer++;
                }
            }
        }

        $this->info("Seguimiento (>5 días):   {$seguimiento} cotizaciones notificadas.");
        $this->info("Por vencer (<=2 días):   {$porVencer} cotizaciones notificadas.");
        $this->info("Vencidas (marcadas):     {$vencidas} cotizaciones notificadas.");

        return self::SUCCESS;
    }
}
