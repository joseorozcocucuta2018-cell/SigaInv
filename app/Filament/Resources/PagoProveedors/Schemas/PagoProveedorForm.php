<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoProveedors\Schemas;

use App\Enums\BancoEstado;
use App\Enums\ProveedorEstado;
use App\Models\Banco;
use App\Models\Caja;
use App\Models\Proveedor;
use App\Services\PagoDistribucionService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PagoProveedorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Comprobante de Egreso')
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero')
                            ->label('Nro. Comprobante')
                            ->default('Automático')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->options(Proveedor::where('estado', ProveedorEstado::ACTIVO)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        DateTimePicker::make('fecha')
                            ->label('Fecha de Pago')
                            ->default(now())
                            ->required(),
                        Select::make('origen')
                            ->label('Origen del Pago')
                            ->options([
                                'caja' => 'Caja',
                                'banco' => 'Banco',
                            ])
                            ->default('caja')
                            ->required()
                            ->live()
                            ->dehydrated(false),
                        Select::make('caja_id')
                            ->label('Caja')
                            ->options(Caja::where('estado', 'activa')->where('tipo', '!=', 'caja_pos')->pluck('nombre', 'id'))
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('origen') === 'caja')
                            ->required(fn (Get $get): bool => $get('origen') === 'caja'),
                        Select::make('banco_id')
                            ->label('Cuenta Bancaria')
                            ->options(Banco::where('estado', BancoEstado::ACTIVO)->pluck('nombre_banco', 'id'))
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('origen') === 'banco')
                            ->required(fn (Get $get): bool => $get('origen') === 'banco'),
                        TextInput::make('monto')
                            ->label('Monto Pagado')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->rules([
                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $proveedorId = $get('proveedor_id');
                                    if (! $proveedorId) {
                                        return;
                                    }
                                    $service = app(PagoDistribucionService::class);
                                    $deuda = $service->obtenerDeudaProveedor((int) $proveedorId);
                                    if ((float) $value > $deuda && $deuda > 0) {
                                        $fail('El monto no puede superar la deuda pendiente: $'.number_format($deuda, 0, ',', '.'));
                                    }
                                },
                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $cajaId = $get('caja_id');
                                    $bancoId = $get('banco_id');
                                    if ($cajaId) {
                                        $caja = Caja::find($cajaId);
                                        if ($caja && (float) $value > $caja->saldo_actual) {
                                            $fail('Saldo insuficiente en caja. Saldo actual: $'.number_format($caja->saldo_actual, 0, ',', '.'));
                                        }
                                    }
                                    if ($bancoId) {
                                        $banco = Banco::find($bancoId);
                                        if ($banco && (float) $value > $banco->saldo_actual) {
                                            $fail('Saldo insuficiente en cuenta. Saldo actual: $'.number_format($banco->saldo_actual, 0, ',', '.'));
                                        }
                                    }
                                },
                            ]),
                        TextInput::make('referencia')
                            ->label('Referencia / Nro. Cheque / Transacción')
                            ->maxLength(50),
                    ]),
                Section::make('Deuda Pendiente con Proveedor')
                    ->visible(fn (Get $get): bool => (bool) $get('proveedor_id'))
                    ->schema([
                        Placeholder::make('resumen_deuda')
                            ->hiddenLabel()
                            ->content(function (Get $get): HtmlString {
                                $proveedorId = $get('proveedor_id');
                                if (! $proveedorId) {
                                    return new HtmlString('');
                                }

                                $service = app(PagoDistribucionService::class);
                                $compras = $service->obtenerComprasPendientesProveedor((int) $proveedorId);
                                $totalDeuda = $compras->sum('saldo_pendiente');

                                if ($compras->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Este proveedor no tiene compras pendientes de pago.</p>');
                                }

                                $html = '<div style="font-size: 0.875rem;">';

                                $html .= '<div style="font-weight: 600; margin-bottom: 12px;">Total Deuda: <span style="color: #e53e3e;">$'.number_format((float) $totalDeuda, 0, ',', '.').'</span></div>';

                                $html .= '<div style="display: flex; font-size: 0.75rem; color: #a0aec0; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0;">';
                                $html .= '<span style="flex: 2;">Factura</span>';
                                $html .= '<span style="flex: 1; text-align: right;">Total</span>';
                                $html .= '<span style="flex: 1; text-align: right;">Pagado</span>';
                                $html .= '<span style="flex: 1; text-align: right;">Pendiente</span>';
                                $html .= '<span style="flex: 1; text-align: right;">Fecha</span>';
                                $html .= '</div>';

                                foreach ($compras as $compra) {
                                    $total = (float) $compra->total;
                                    $saldo = (float) $compra->saldo_pendiente;
                                    $pagado = $total - $saldo;
                                    $pColor = $pagado > 0 ? '#38a169' : '#a0aec0';
                                    $sColor = $saldo > 0 ? '#e53e3e' : '#a0aec0';

                                    $html .= '<div style="display: flex; padding: 6px 0; border-bottom: 1px solid #f7fafc;">';
                                    $html .= '<span style="flex: 2; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">'.e($compra->numero).'</span>';
                                    $html .= '<span style="flex: 1; text-align: right; font-variant-numeric: tabular-nums;">$'.number_format($total, 0, ',', '.').'</span>';
                                    $html .= '<span style="flex: 1; text-align: right; color: '.$pColor.'; font-variant-numeric: tabular-nums;">$'.number_format($pagado, 0, ',', '.').'</span>';
                                    $html .= '<span style="flex: 1; text-align: right; color: '.$sColor.'; font-weight: 600; font-variant-numeric: tabular-nums;">$'.number_format($saldo, 0, ',', '.').'</span>';
                                    $html .= '<span style="flex: 1; text-align: right; color: #a0aec0;">'.$compra->fecha->format('d/m/Y').'</span>';
                                    $html .= '</div>';
                                }

                                $html .= '<div style="font-size: 0.75rem; color: #a0aec0; margin-top: 8px;">Se paga primero la factura más antigua.</div>';
                                $html .= '</div>';

                                return new HtmlString($html);
                            }),
                    ]),
                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->columnSpanFull(),
            ]);
    }
}
