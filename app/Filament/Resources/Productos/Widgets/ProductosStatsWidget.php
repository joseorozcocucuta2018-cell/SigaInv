<?php

declare(strict_types=1);

namespace App\Filament\Resources\Productos\Widgets;

use App\Models\Producto;
use App\Models\StockBodega;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductosStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $totalActivos = Producto::where('activo', true)->count();

        $stockBajo = Producto::where('activo', true)
            ->where('stock_minimo', '>', 0)
            ->withSum('stockBodegas as stock_total', 'cantidad')
            ->havingRaw('COALESCE(stock_total, 0) <= stock_minimo')
            ->count();

        $valorInventario = (float) StockBodega::join('productos', 'productos.id', '=', 'stock_bodegas.producto_id')
            ->where('productos.activo', true)
            ->where('stock_bodegas.cantidad', '>', 0)
            ->sum(DB::raw('stock_bodegas.cantidad * productos.precio_compra'));

        return [
            Stat::make('Productos activos', number_format($totalActivos))
                ->description('En catálogo')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Stock bajo', $stockBajo.' productos')
                ->description('Bajo o igual al mínimo')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stockBajo > 0 ? 'danger' : 'success'),

            Stat::make('Valor del inventario', '$'.number_format($valorInventario, 0, ',', '.'))
                ->description('Al precio de costo')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->chart($this->sparklineEntradas())
                ->color('info'),
        ];
    }

    private function sparklineEntradas(): array
    {
        return collect(range(6, 0))->map(function (int $daysAgo): float {
            $day = Carbon::now()->subDays($daysAgo)->toDateString();

            return (float) StockBodega::join('productos', 'productos.id', '=', 'stock_bodegas.producto_id')
                ->whereDate('stock_bodegas.updated_at', $day)
                ->where('productos.activo', true)
                ->sum('stock_bodegas.cantidad');
        })->toArray();
    }
}
