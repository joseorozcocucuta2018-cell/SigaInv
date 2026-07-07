<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions\Widgets;

use App\Enums\RemisionEstado;
use App\Models\Remision;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class RemisionesStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $porFacturar = Remision::where('estado', RemisionEstado::CONFIRMADA->value)->count();

        $montoPorFacturar = (float) Remision::where('estado', RemisionEstado::CONFIRMADA->value)
            ->sum('total');

        $delMes = Remision::whereBetween('fecha', [$inicioMes, $finMes])->count();

        $montoMes = (float) Remision::whereBetween('fecha', [$inicioMes, $finMes])->sum('total');

        return [
            Stat::make('Por facturar', $porFacturar)
                ->description('Remisiones confirmadas pendientes')
                ->descriptionIcon('heroicon-m-truck')
                ->color($porFacturar > 0 ? 'warning' : 'success'),

            Stat::make('Monto por facturar', '$'.number_format($montoPorFacturar, 0, ',', '.'))
                ->description('En remisiones confirmadas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Remisiones del mes', $delMes)
                ->description('Monto: $'.number_format($montoMes, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-document-duplicate')
                ->chart($this->sparklineMes())
                ->color('gray'),
        ];
    }

    private function sparklineMes(): array
    {
        return collect(range(6, 0))->map(function (int $daysAgo): int {
            $day = Carbon::now()->subDays($daysAgo)->toDateString();

            return Remision::whereDate('fecha', $day)->count();
        })->toArray();
    }
}
