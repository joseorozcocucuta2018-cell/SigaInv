<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClienteResource\Widgets;

use App\Enums\ClienteEstado;
use App\Enums\VentaEstado;
use App\Models\Cliente;
use App\Models\Venta;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ClientesStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalActivos = Cliente::where('estado', ClienteEstado::ACTIVO)->count();

        $porCobrar = (float) Venta::where('estado', VentaEstado::CONFIRMADA->value)
            ->where('saldo_pendiente', '>', 0)
            ->sum('saldo_pendiente');

        $clientesConDeuda = Cliente::where('estado', ClienteEstado::ACTIVO)
            ->where('saldo', '>', 0)
            ->count();

        $clientesEnMora = Cliente::where('estado', ClienteEstado::ACTIVO)
            ->whereNotNull('limite_credito')
            ->whereColumn('saldo', '>', 'limite_credito')
            ->count();

        return [
            Stat::make('Clientes activos', number_format($totalActivos))
                ->description('En el sistema')
                ->descriptionIcon('heroicon-m-users')
                ->chart($this->sparklineNuevos())
                ->color('success'),

            Stat::make('Por cobrar', '$'.number_format($porCobrar, 0, ',', '.'))
                ->description('Saldo en facturas abiertas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Clientes con deuda', $clientesConDeuda)
                ->description($clientesEnMora.' superan límite de crédito')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($clientesEnMora > 0 ? 'danger' : 'info'),
        ];
    }

    private function sparklineNuevos(): array
    {
        return collect(range(6, 0))->map(function (int $daysAgo): int {
            $day = Carbon::now()->subDays($daysAgo)->toDateString();

            return Cliente::whereDate('created_at', $day)->count();
        })->toArray();
    }
}
