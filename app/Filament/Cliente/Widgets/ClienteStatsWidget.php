<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Widgets;

use App\Enums\CotizacionEstado;
use App\Enums\RemisionEstado;
use App\Enums\VentaEstado;
use App\Models\Cotizacion;
use App\Models\Remision;
use App\Models\Venta;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

/**
 * Estadísticas del cliente autenticado en el portal /clientes.
 * Reemplaza al antiguo getStats() de ClienteDashboard (deprecado en v5).
 */
class ClienteStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $cliente = Auth::guard('cliente')->user();

        if (! $cliente) {
            return [];
        }

        $clienteId = $cliente->id;

        $facturas = Venta::where('cliente_id', $clienteId)
            ->whereIn('estado', [VentaEstado::CONFIRMADA->value, VentaEstado::PAGADA->value])
            ->count();

        $totalFacturado = Venta::where('cliente_id', $clienteId)
            ->whereIn('estado', [VentaEstado::CONFIRMADA->value, VentaEstado::PAGADA->value])
            ->sum('total');

        $saldoPendiente = Venta::where('cliente_id', $clienteId)
            ->where('estado', VentaEstado::CONFIRMADA->value)
            ->sum('saldo_pendiente');

        $remisiones = Remision::where('cliente_id', $clienteId)
            ->where('estado', RemisionEstado::CONFIRMADA->value)
            ->count();

        $cotizaciones = Cotizacion::where('cliente_id', $clienteId)
            ->where('estado', CotizacionEstado::PENDIENTE->value)
            ->count();

        return [
            Stat::make('Facturas', $facturas)
                ->description('Documentos emitidos')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('Total Facturado', '$'.number_format((float) $totalFacturado, 0, ',', '.'))
                ->description('Monto total')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Saldo Pendiente', '$'.number_format((float) $saldoPendiente, 0, ',', '.'))
                ->description('Por pagar')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($saldoPendiente > 0 ? 'warning' : 'gray'),

            Stat::make('Remisiones', $remisiones)
                ->description('Pendientes de facturar')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            Stat::make('Cotizaciones', $cotizaciones)
                ->description('Pendientes')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray'),
        ];
    }
}
