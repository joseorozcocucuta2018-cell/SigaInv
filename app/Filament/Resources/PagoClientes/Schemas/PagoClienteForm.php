<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoClientes\Schemas;

use App\Enums\BancoEstado;
use App\Enums\CajaEstado;
use App\Enums\CajaTipo;
use App\Enums\ClienteEstado;
use App\Models\Banco;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\FormaPago;
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

class PagoClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Pago')
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número de Recibo')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Se genera automáticamente al guardar.'),
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->options(Cliente::where('estado', ClienteEstado::ACTIVO)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        DateTimePicker::make('fecha')
                            ->label('Fecha de Pago')
                            ->default(now())
                            ->required(),
                        Select::make('forma_pago_id')
                            ->label('Forma de Pago')
                            ->options(FormaPago::where('activo', true)->pluck('nombre', 'id'))
                            ->required()
                            ->live(),
                        Select::make('banco_id')
                            ->label('Cuenta Bancaria')
                            ->options(Banco::where('estado', BancoEstado::ACTIVO)->pluck('nombre_banco', 'id'))
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('forma_pago_id') ? (FormaPago::find($get('forma_pago_id'))?->requiere_banco ?? false) : false)
                            ->required(fn (Get $get): bool => $get('forma_pago_id') ? (FormaPago::find($get('forma_pago_id'))?->requiere_banco ?? false) : false),
                        Select::make('caja_id')
                            ->label('Caja')
                            ->options(Caja::where('estado', CajaEstado::ACTIVA->value)->where('tipo', '!=', CajaTipo::POS->value)->pluck('nombre', 'id'))
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('forma_pago_id') ? ! (FormaPago::find($get('forma_pago_id'))?->requiere_banco ?? true) : false)
                            ->required(fn (Get $get): bool => $get('forma_pago_id') ? ! (FormaPago::find($get('forma_pago_id'))?->requiere_banco ?? true) : false),
                        TextInput::make('monto')
                            ->label('Monto Pagado')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->rules([
                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $clienteId = $get('cliente_id');
                                    if (! $clienteId) {
                                        return;
                                    }
                                    $service = app(PagoDistribucionService::class);
                                    $deuda = $service->obtenerDeudaCliente((int) $clienteId);
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
                            ->label('Referencia / Nro. Operación')
                            ->maxLength(50),
                    ]),
                Section::make('Deuda Pendiente del Cliente')
                    ->visible(fn (Get $get): bool => (bool) $get('cliente_id'))
                    ->schema([
                        Placeholder::make('resumen_deuda')
                            ->label('')
                            ->content(function (Get $get): HtmlString {
                                $clienteId = $get('cliente_id');
                                if (! $clienteId) {
                                    return new HtmlString('');
                                }

                                $service = app(PagoDistribucionService::class);
                                $documentos = $service->obtenerDocumentosPendientesCliente((int) $clienteId);
                                $totalDeuda = $documentos->sum(fn ($item) => (float) $item['modelo']->saldo_pendiente);

                                if ($documentos->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Este cliente no tiene documentos pendientes de pago.</p>');
                                }

                                $html = '<div style="font-size: 0.875rem;">';

                                $html .= '<div style="font-weight: 600; margin-bottom: 12px;">Total Deuda: <span style="color: #e53e3e;">$'.number_format($totalDeuda, 0, ',', '.').'</span></div>';

                                $html .= '<div style="display: flex; font-size: 0.75rem; color: #a0aec0; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0;">';
                                $html .= '<span style="flex: 2;">Documento</span>';
                                $html .= '<span style="flex: 1; text-align: right;">Total</span>';
                                $html .= '<span style="flex: 1; text-align: right;">Pagado</span>';
                                $html .= '<span style="flex: 1; text-align: right;">Pendiente</span>';
                                $html .= '<span style="flex: 1; text-align: right;">Fecha</span>';
                                $html .= '</div>';

                                foreach ($documentos as ['tipo' => $tipo, 'modelo' => $doc]) {
                                    $pagado = (float) $doc->total - (float) $doc->saldo_pendiente;
                                    $pColor = $pagado > 0 ? '#38a169' : '#a0aec0';
                                    $sColor = $doc->saldo_pendiente > 0 ? '#e53e3e' : '#a0aec0';
                                    $tipoLabel = $tipo === 'venta' ? 'VTA' : 'REM';

                                    $html .= '<div style="display: flex; padding: 6px 0; border-bottom: 1px solid #f7fafc;">';
                                    $html .= '<span style="flex: 2; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">'.$tipoLabel.' '.e($doc->numero).'</span>';
                                    $html .= '<span style="flex: 1; text-align: right; font-variant-numeric: tabular-nums;">$'.number_format($doc->total, 0, ',', '.').'</span>';
                                    $html .= '<span style="flex: 1; text-align: right; color: '.$pColor.'; font-variant-numeric: tabular-nums;">$'.number_format($pagado, 0, ',', '.').'</span>';
                                    $html .= '<span style="flex: 1; text-align: right; color: '.$sColor.'; font-weight: 600; font-variant-numeric: tabular-nums;">$'.number_format($doc->saldo_pendiente, 0, ',', '.').'</span>';
                                    $html .= '<span style="flex: 1; text-align: right; color: #a0aec0;">'.$doc->fecha->format('d/m/Y').'</span>';
                                    $html .= '</div>';
                                }

                                $html .= '<div style="font-size: 0.75rem; color: #a0aec0; margin-top: 8px;">Se paga primero el documento más antiguo.</div>';
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
