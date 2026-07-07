<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ventas\Widgets;

use App\Enums\VentaEstado;
use App\Models\Venta;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class VentasStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $estados = [VentaEstado::CONFIRMADA->value, VentaEstado::PAGADA->value];

        $totalMes = (float) Venta::whereIn('estado', $estados)
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('total');

        $ventasAbiertas = Venta::where('estado', VentaEstado::CONFIRMADA->value)
            ->where('saldo_pendiente', '>', 0)
            ->count();

        $porCobrar = (float) Venta::where('estado', VentaEstado::CONFIRMADA->value)
            ->where('saldo_pendiente', '>', 0)
            ->sum('saldo_pendiente');

        $promedio = (float) (Venta::whereIn('estado', $estados)
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->avg('total') ?? 0);

        return [
            Stat::make('Ventas del mes', '$'.number_format($totalMes, 0, ',', '.'))
                ->description('Confirmadas y pagadas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($this->sparkline($estados))
                ->color('success'),

            Stat::make('Ventas por cobrar', $ventasAbiertas.' facturas')
                ->description('Saldo: $'.number_format($porCobrar, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Ticket promedio', '$'.number_format($promedio, 0, ',', '.'))
                ->description('Este mes')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }

    private function sparkline(array $estados): array
    {
        return collect(range(6, 0))->map(function (int $daysAgo) use ($estados): float {
            $day = Carbon::now()->subDays($daysAgo)->toDateString();

            return (float) Venta::whereIn('estado', $estados)
                ->whereDate('fecha', $day)
                ->sum('total');
        })->toArray();
    }
}
