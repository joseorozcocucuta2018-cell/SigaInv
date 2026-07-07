<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Widgets;

use App\Enums\CompraEstado;
use App\Models\Compra;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ComprasStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $estados = [CompraEstado::REGISTRADA->value, CompraEstado::PAGADA->value, CompraEstado::PENDIENTE->value];

        $totalMes = (float) Compra::whereIn('estado', $estados)
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('total');

        $comprasPendientes = Compra::where('estado', CompraEstado::PENDIENTE->value)
            ->count();

        $porPagar = (float) Compra::where('estado', CompraEstado::PENDIENTE->value)
            ->sum('saldo_pendiente');

        $promedio = (float) (Compra::whereIn('estado', $estados)
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->avg('total') ?? 0);

        return [
            Stat::make('Compras del mes', '$'.number_format($totalMes, 0, ',', '.'))
                ->description('Registradas y pagadas')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart($this->sparkline($estados))
                ->color('info'),

            Stat::make('Compras por pagar', $comprasPendientes.' órdenes')
                ->description('Saldo: $'.number_format($porPagar, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('warning'),

            Stat::make('Promedio por compra', '$'.number_format($promedio, 0, ',', '.'))
                ->description('Este mes')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('gray'),
        ];
    }

    private function sparkline(array $estados): array
    {
        return collect(range(6, 0))->map(function (int $daysAgo) use ($estados): float {
            $day = Carbon::now()->subDays($daysAgo)->toDateString();

            return (float) Compra::whereIn('estado', $estados)
                ->whereDate('fecha', $day)
                ->sum('total');
        })->toArray();
    }
}
