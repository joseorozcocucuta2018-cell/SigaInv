<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cotizacions\Widgets;

use App\Enums\CotizacionEstado;
use App\Models\Cotizacion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CotizacionesStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $pendientes = Cotizacion::where('estado', CotizacionEstado::PENDIENTE)->count();

        $montoPendiente = (float) Cotizacion::where('estado', CotizacionEstado::PENDIENTE)->sum('total');

        $delMes = Cotizacion::whereBetween('fecha', [$inicioMes, $finMes])->count();

        $montoMes = (float) Cotizacion::whereBetween('fecha', [$inicioMes, $finMes])->sum('total');

        return [
            Stat::make('Cotizaciones pendientes', $pendientes)
                ->description('Esperando respuesta del cliente')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendientes > 0 ? 'warning' : 'success'),

            Stat::make('Monto pendiente', '$'.number_format($montoPendiente, 0, ',', '.'))
                ->description('En cotizaciones abiertas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Cotizaciones del mes', $delMes)
                ->description('Monto: $'.number_format($montoMes, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-document-text')
                ->chart($this->sparklineMes())
                ->color('gray'),
        ];
    }

    private function sparklineMes(): array
    {
        return collect(range(6, 0))->map(function (int $daysAgo): int {
            $day = Carbon::now()->subDays($daysAgo)->toDateString();

            return Cotizacion::whereDate('fecha', $day)->count();
        })->toArray();
    }
}
