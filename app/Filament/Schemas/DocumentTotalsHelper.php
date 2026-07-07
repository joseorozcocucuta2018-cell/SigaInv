<?php

declare(strict_types=1);

namespace App\Filament\Schemas;

use App\Enums\ImpuestoTipo;
use App\Models\Impuesto;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

/**
 * Helper compartido por los Forms de documentos (Venta, Remision, Compra, Cotizacion).
 * Centraliza el cálculo de subtotales de línea, totales de cabecera y la Section
 * de Totales, eliminando ~264 líneas de duplicación.
 */
final class DocumentTotalsHelper
{
    /**
     * Impuesto IVA 0% (plan común para no responsables de IVA).
     * Query cacheable a nivel de request.
     */
    public static function ivaCero(): ?Impuesto
    {
        return Impuesto::where('tipo', ImpuestoTipo::IVA->value)
            ->where('porcentaje', 0)
            ->where('activo', true)
            ->first();
    }

    /**
     * Actualiza el subtotal de una línea del Repeater:
     * (cantidad × precio) − (cantidad × descuento_unitario)
     */
    public static function updateLineSubtotal(Get $get, Set $set): void
    {
        $cantidad = (float) ($get('cantidad') ?? 0);
        $precioUnitario = (float) ($get('precio_unitario') ?? 0);
        $descuentoUnitario = (float) ($get('descuento_unitario') ?? 0);

        $lineSubtotal = ($cantidad * $precioUnitario) - ($cantidad * $descuentoUnitario);
        $set('subtotal', round(max($lineSubtotal, 0), 2));
    }

    /**
     * Recalcula los totales globales del documento (subtotal, descuento, impuestos, total, saldo_pendiente).
     * Optimizado: precarga impuestos con whereIn para evitar N+1.
     *
     * @param  bool  $includeSaldo  Si true, setea también saldo_pendiente (no aplica a Cotización)
     */
    public static function updateTotals(Get $get, Set $set, bool $includeSaldo = true): void
    {
        $detalles = $get('../../detalles') ?? $get('detalles') ?? [];
        $subtotal = 0;
        $descuentoTotal = 0;
        $impuestosTotal = 0;

        $impuestosIds = collect($detalles)->pluck('impuesto_id')->filter()->unique();
        $impuestos = Impuesto::whereIn('id', $impuestosIds)->get()->keyBy('id');

        foreach ($detalles as $detalle) {
            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            $precioUnitario = (float) ($detalle['precio_unitario'] ?? 0);
            $descuentoUnitario = (float) ($detalle['descuento_unitario'] ?? 0);
            $impuestoId = $detalle['impuesto_id'] ?? null;

            $montoOriginal = $cantidad * $precioUnitario;
            $montoDescuento = $cantidad * $descuentoUnitario;

            $subtotal += $montoOriginal;
            $descuentoTotal += $montoDescuento;

            if ($impuestoId && isset($impuestos[$impuestoId])) {
                $impuesto = $impuestos[$impuestoId];
                if ($impuesto->porcentaje > 0) {
                    $baseImponible = $montoOriginal - $montoDescuento;
                    $impuestosTotal += $baseImponible * ($impuesto->porcentaje / 100);
                }
            }
        }

        $total = $subtotal - $descuentoTotal + $impuestosTotal;

        $set('../../subtotal', round($subtotal, 2));
        $set('../../descuento', round($descuentoTotal, 2));
        $set('../../impuestos', round($impuestosTotal, 2));
        $set('../../total', round($total, 2));

        if ($includeSaldo) {
            $set('../../saldo_pendiente', round($total, 2));
        }
    }

    /**
     * Section Totales estándar con 5 campos (subtotal, descuento, impuestos, total, saldo_pendiente).
     * Usada por Venta y Remision.
     */
    public static function totalesSectionConSaldo(): Section
    {
        return Section::make('Totales')
            ->columnSpanFull()
            ->columns(5)
            ->schema([
                self::readonlyMoneyInput('subtotal', 'Subtotal'),
                self::readonlyMoneyInput('descuento', 'Descuento'),
                self::readonlyMoneyInput('impuestos', 'Impuestos'),
                self::readonlyMoneyInput('total', 'Total'),
                self::readonlyMoneyInput('saldo_pendiente', 'Saldo Pendiente'),
            ]);
    }

    /**
     * Section Totales con 4 campos (subtotal, descuento, impuestos, total).
     * Usada por Cotizacion.
     */
    public static function totalesSectionSinSaldo(): Section
    {
        return Section::make('Totales')
            ->columnSpanFull()
            ->columns(4)
            ->schema([
                self::readonlyMoneyInput('subtotal', 'Subtotal'),
                self::readonlyMoneyInput('descuento', 'Descuento'),
                self::readonlyMoneyInput('impuestos', 'Impuestos'),
                self::readonlyMoneyInput('total', 'Total'),
            ]);
    }

    private static function readonlyMoneyInput(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->prefix('$')
            ->default(0)
            ->readOnly();
    }
}
