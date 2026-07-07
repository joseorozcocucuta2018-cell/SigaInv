<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\CompraEstado;
use App\Enums\VentaEstado;
use App\Models\Compra;
use App\Models\User;
use App\Models\Venta;
use App\Notifications\CompraPorVencerNotification;
use App\Notifications\CompraVencidaNotification;
use App\Notifications\FacturaPorVencerNotification;
use App\Notifications\FacturaVencidaNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class EnviarRecordatoriosFacturas extends Command
{
    protected $signature = 'recordatorios:facturas';

    protected $description = 'Envía recordatorios de facturas próximas a vencer o vencidas';

    public function handle(): int
    {
        $hoy = now()->toDateString();

        $destinatariosInternos = User::role(['administrador', 'contador'])->get();

        if ($destinatariosInternos->isEmpty()) {
            $this->warn('No hay usuarios con rol administrador o contador para notificar.');
        }

        // Facturas confirmadas con saldo pendiente y fecha de vencimiento
        $facturas = Venta::with('cliente')
            ->where('estado', VentaEstado::CONFIRMADA->value)
            ->where('saldo_pendiente', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->get();

        $porVencer = 0;
        $vencidas = 0;
        $resumen = 0;

        foreach ($facturas as $venta) {
            $diasDiff = now()->startOfDay()->diffInDays($venta->fecha_vencimiento, false);
            // diasDiff > 0: días hasta vencer  |  diasDiff <= 0: días de mora

            if ($diasDiff === 3) {
                // Vence en exactamente 3 días
                $cacheKey = "recordatorio_por_vencer_{$venta->id}_{$hoy}";
                if (Cache::has($cacheKey)) {
                    continue;
                }

                $notif = new FacturaPorVencerNotification($venta, 3);
                $this->notificarInternos($destinatariosInternos, $notif, $cacheKey);
                $this->notificarCliente($venta, $notif);
                Cache::put($cacheKey, true, now()->addHours(20));
                $porVencer++;

            } elseif ($diasDiff === 0) {
                // Vence hoy
                $cacheKey = "recordatorio_vence_hoy_{$venta->id}_{$hoy}";
                if (Cache::has($cacheKey)) {
                    continue;
                }

                $notif = new FacturaVencidaNotification($venta, 0);
                $this->notificarInternos($destinatariosInternos, $notif, $cacheKey);
                $this->notificarCliente($venta, $notif);
                Cache::put($cacheKey, true, now()->addHours(20));
                $vencidas++;

            } elseif ($diasDiff < 0 && abs($diasDiff) > 7) {
                // Vencida hace más de 7 días — solo notificación interna (resumen)
                $diasMora = abs($diasDiff);
                $cacheKey = "recordatorio_mora_{$venta->id}_{$hoy}";
                if (Cache::has($cacheKey)) {
                    continue;
                }

                $notif = new FacturaVencidaNotification($venta, $diasMora);
                $this->notificarInternos($destinatariosInternos, $notif, $cacheKey);
                Cache::put($cacheKey, true, now()->addHours(20));
                $resumen++;
            }
        }

        $this->info("Por vencer (3 días): {$porVencer} facturas notificadas.");
        $this->info("Vencidas hoy:        {$vencidas} facturas notificadas.");
        $this->info("En mora >7 días:     {$resumen} facturas notificadas internamente.");

        // Compras registradas o pendientes con saldo y fecha de vencimiento
        $compras = Compra::with('proveedor')
            ->whereIn('estado', [CompraEstado::REGISTRADA->value, CompraEstado::PENDIENTE->value])
            ->where('saldo_pendiente', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->get();

        $compraPorVencer = 0;
        $compraVencidas = 0;
        $compraResumen = 0;

        foreach ($compras as $compra) {
            $diasDiff = now()->startOfDay()->diffInDays($compra->fecha_vencimiento, false);

            if ($diasDiff === 3) {
                $cacheKey = "recordatorio_compra_por_vencer_{$compra->id}_{$hoy}";
                if (Cache::has($cacheKey)) {
                    continue;
                }

                $notif = new CompraPorVencerNotification($compra, 3);
                $this->notificarInternos($destinatariosInternos, $notif, $cacheKey);
                Cache::put($cacheKey, true, now()->addHours(20));
                $compraPorVencer++;

            } elseif ($diasDiff === 0) {
                $cacheKey = "recordatorio_compra_vence_hoy_{$compra->id}_{$hoy}";
                if (Cache::has($cacheKey)) {
                    continue;
                }

                $notif = new CompraVencidaNotification($compra, 0);
                $this->notificarInternos($destinatariosInternos, $notif, $cacheKey);
                Cache::put($cacheKey, true, now()->addHours(20));
                $compraVencidas++;

            } elseif ($diasDiff < 0 && abs($diasDiff) > 7) {
                $diasMora = abs($diasDiff);
                $cacheKey = "recordatorio_compra_mora_{$compra->id}_{$hoy}";
                if (Cache::has($cacheKey)) {
                    continue;
                }

                $notif = new CompraVencidaNotification($compra, $diasMora);
                $this->notificarInternos($destinatariosInternos, $notif, $cacheKey);
                Cache::put($cacheKey, true, now()->addHours(20));
                $compraResumen++;
            }
        }

        $this->info('---');
        $this->info("Por vencer (3 días): {$compraPorVencer} compras notificadas.");
        $this->info("Vencidas hoy:        {$compraVencidas} compras notificadas.");
        $this->info("En mora >7 días:     {$compraResumen} compras notificadas internamente.");

        return self::SUCCESS;
    }

    private function notificarInternos($usuarios, $notif, string $cacheKey): void
    {
        foreach ($usuarios as $user) {
            $user->notify($notif);
        }
    }

    private function notificarCliente(Venta $venta, $notif): void
    {
        if (! $venta->cliente?->email) {
            return;
        }

        Notification::route('mail', $venta->cliente->email)
            ->notify($notif);
    }
}
