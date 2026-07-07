<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\CompraEstado;
use App\Enums\CotizacionEstado;
use App\Enums\RemisionEstado;
use App\Enums\VentaEstado;
use App\Models\Compra;
use App\Models\Cotizacion;
use App\Models\Remision;
use App\Models\Venta;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ResumenGeneralWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()?->can('dashboard.ver') ?? false;
    }

    protected function getStats(): array
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $ventasMes = (float) Venta::whereIn('estado', [VentaEstado::CONFIRMADA->value, VentaEstado::PAGADA->value])
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('total');

        $comprasMes = (float) Compra::whereIn('estado', [CompraEstado::REGISTRADA->value, CompraEstado::PENDIENTE->value, CompraEstado::PAGADA->value])
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('total');

        $porCobrar = (float) Venta::whereIn('estado', [VentaEstado::CONFIRMADA->value])
            ->where('saldo_pendiente', '>', 0)
            ->sum('saldo_pendiente');

        $porPagar = (float) Compra::whereIn('estado', [CompraEstado::REGISTRADA->value, CompraEstado::PENDIENTE->value])
            ->where('saldo_pendiente', '>', 0)
            ->sum('saldo_pendiente');

        $remisionesPendientes = Remision::where('estado', RemisionEstado::CONFIRMADA->value)->count();

        $cotizacionesPendientes = Cotizacion::where('estado', CotizacionEstado::PENDIENTE)->count();

        return [
            Stat::make('Ventas del mes', '$'.number_format($ventasMes, 0, ',', '.'))
                ->description('Confirmadas y pagadas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Compras del mes', '$'.number_format($comprasMes, 0, ',', '.'))
                ->description('Confirmadas y pagadas')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('info'),

            Stat::make('Por cobrar', '$'.number_format($porCobrar, 0, ',', '.'))
                ->description('Ventas con saldo pendiente')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Por pagar', '$'.number_format($porPagar, 0, ',', '.'))
                ->description('Compras con saldo pendiente')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('danger'),

            Stat::make('Remisiones por facturar', $remisionesPendientes)
                ->description('Confirmadas sin venta')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),

            Stat::make('Cotizaciones pendientes', $cotizacionesPendientes)
                ->description('Esperando respuesta')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),
        ];
    }
}
