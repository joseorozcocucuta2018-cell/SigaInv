<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProveedorResource\Widgets;

use App\Enums\ProveedorEstado;
use App\Models\Compra;
use App\Models\Proveedor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ProveedoresStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalActivos = Proveedor::where('estado', ProveedorEstado::ACTIVO)->count();
        $porPagar = (float) Compra::where('saldo_pendiente', '>', 0)->sum('saldo_pendiente');
        $proveedoresConDeuda = Proveedor::where('estado', ProveedorEstado::ACTIVO)->where('saldo', '>', 0)->count();
        $comprasMes = Compra::whereBetween('fecha', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        ])->count();

        return [
            Stat::make('Proveedores activos', number_format($totalActivos))
                ->description('En el sistema')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('success'),

            Stat::make('Por pagar', '$'.number_format($porPagar, 0, ',', '.'))
                ->description('Saldo en facturas abiertas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Proveedores con deuda', $proveedoresConDeuda)
                ->description($comprasMes.' compras este mes')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
        ];
    }
}
