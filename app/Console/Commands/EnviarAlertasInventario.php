<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\StockBodega;
use App\Models\StockBodegaLote;
use App\Models\User;
use App\Notifications\LoteProximoVencerNotification;
use App\Notifications\StockBajoNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EnviarAlertasInventario extends Command
{
    protected $signature = 'alertas:inventario
                            {--lotes : Solo alertas de lotes próximos a vencer}
                            {--stock : Solo alertas de stock bajo}
                            {--dias=30 : Días de anticipación para alertas de lotes}';

    protected $description = 'Envía notificaciones internas de stock bajo y lotes próximos a vencer';

    public function handle(): int
    {
        $soloLotes = $this->option('lotes');
        $soloStock = $this->option('stock');
        $diasLotes = (int) $this->option('dias');

        // Si no se especifica opción, ejecutar ambas
        $ejecutarStock = ! $soloLotes || $soloStock;
        $ejecutarLotes = ! $soloStock || $soloLotes;

        $destinatarios = User::role(['administrador', 'auxiliar'])->get();

        if ($destinatarios->isEmpty()) {
            $this->warn('No hay usuarios con rol administrador o auxiliar para notificar.');

            return self::SUCCESS;
        }

        if ($ejecutarStock) {
            $this->alertasStock($destinatarios);
        }

        if ($ejecutarLotes) {
            $this->alertasLotes($destinatarios, $diasLotes);
        }

        return self::SUCCESS;
    }

    private function alertasStock($destinatarios): void
    {
        $hoy = now()->toDateString();

        // Productos con stock <= stock_minimo (excluye los que no tienen stock_minimo definido)
        $stocksBajos = StockBodega::with(['producto', 'bodega'])
            ->whereHas('producto', fn ($q) => $q->where('activo', true)->whereNotNull('stock_minimo'))
            ->whereColumn('cantidad', '<=', 'productos.stock_minimo')
            ->join('productos', 'stock_bodegas.producto_id', '=', 'productos.id')
            ->select('stock_bodegas.*')
            ->get();

        $enviados = 0;

        foreach ($stocksBajos as $stock) {
            $cacheKey = "alerta_stock_{$stock->producto_id}_{$stock->bodega_id}_{$hoy}";

            // No reenviar si ya se notificó hoy
            if (Cache::has($cacheKey)) {
                continue;
            }

            $nivel = $stock->cantidad <= 0 ? 'agotado' : 'bajo';
            $notif = new StockBajoNotification(
                producto: $stock->producto,
                stockActual: $stock->cantidad,
                bodegaNombre: $stock->bodega->nombre ?? 'Bodega '.$stock->bodega_id,
                nivel: $nivel,
            );

            foreach ($destinatarios as $user) {
                $user->notify($notif);
            }

            // Marcar como enviado por 20 horas (evita duplicados en el día)
            Cache::put($cacheKey, true, now()->addHours(20));
            $enviados++;
        }

        $this->info("Stock: {$enviados} alertas enviadas a {$destinatarios->count()} usuarios.");
    }

    private function alertasLotes($destinatarios, int $dias): void
    {
        $hoy = now()->toDateString();
        $limite = now()->addDays($dias)->toDateString();

        $lotes = StockBodegaLote::with(['stockBodega.producto', 'stockBodega.bodega'])
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->whereDate('fecha_vencimiento', '<=', $limite)
            ->where('cantidad', '>', 0)
            ->get();

        $enviados = 0;

        foreach ($lotes as $lote) {
            $producto = $lote->stockBodega?->producto;
            $bodega = $lote->stockBodega?->bodega;

            if (! $producto) {
                continue;
            }

            $cacheKey = "alerta_lote_{$lote->id}_{$hoy}";

            if (Cache::has($cacheKey)) {
                continue;
            }

            $notif = new LoteProximoVencerNotification(
                producto: $producto,
                lote: $lote->lote ?? 'S/N',
                fechaVencimiento: $lote->fecha_vencimiento,
                cantidad: $lote->cantidad,
                bodegaNombre: $bodega->nombre ?? 'Bodega '.($lote->stockBodega->bodega_id ?? '?'),
            );

            foreach ($destinatarios as $user) {
                $user->notify($notif);
            }

            Cache::put($cacheKey, true, now()->addHours(20));
            $enviados++;
        }

        $this->info("Lotes: {$enviados} alertas de vencimiento enviadas a {$destinatarios->count()} usuarios.");
    }
}
