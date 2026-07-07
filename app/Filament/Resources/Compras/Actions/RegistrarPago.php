<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Actions;

use App\Enums\BancoEstado;
use App\Enums\CajaEstado;
use App\Enums\CajaTipo;
use App\Enums\CompraEstado;
use App\Enums\PagoMedioEnum;
use App\Models\Banco;
use App\Models\Caja;
use App\Services\PagoProveedorService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class RegistrarPago extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'registrar-pago')
            ->label('Registrar Pago')
            ->icon('heroicon-o-credit-card')
            ->color('success')
            ->modalHeading('Registrar Pago')
            ->modalSubmitActionLabel('Pagar')
            ->form(function ($record) {
                return [
                    Select::make('origen')
                        ->label('Origen del Pago')
                        ->options(PagoMedioEnum::class)
                        ->default(PagoMedioEnum::CAJA->value)
                        ->required()
                        ->live(),
                    Select::make('caja_id')
                        ->label('Caja')
                        ->options(Caja::where('estado', CajaEstado::ACTIVA->value)->where('tipo', '!=', CajaTipo::POS->value)->pluck('nombre', 'id'))
                        ->searchable()
                        ->visible(fn ($get) => $get('origen') === PagoMedioEnum::CAJA->value)
                        ->required(fn ($get) => $get('origen') === PagoMedioEnum::CAJA->value),
                    Select::make('banco_id')
                        ->label('Cuenta Bancaria')
                        ->options(Banco::where('estado', BancoEstado::ACTIVO)->pluck('nombre_banco', 'id'))
                        ->searchable()
                        ->visible(fn ($get) => $get('origen') === PagoMedioEnum::BANCO->value)
                        ->required(fn ($get) => $get('origen') === PagoMedioEnum::BANCO->value),
                    TextInput::make('monto')
                        ->label('Monto a Pagar')
                        ->numeric()
                        ->prefix('$')
                        ->default(fn ($record) => (float) $record->total)
                        ->readOnly()
                        ->required(),
                    TextInput::make('referencia')
                        ->label('Referencia')
                        ->maxLength(100),
                ];
            })
            ->action(function ($record, array $data) {
                try {
                    PagoProveedorService::crear([
                        'proveedor_id' => $record->proveedor_id,
                        'caja_id' => $data['origen'] === PagoMedioEnum::CAJA->value ? $data['caja_id'] : null,
                        'banco_id' => $data['origen'] === PagoMedioEnum::BANCO->value ? $data['banco_id'] : null,
                        'monto' => $data['monto'],
                        'fecha' => now(),
                        'referencia' => $data['referencia'] ?? null,
                        'observaciones' => "Pago de factura {$record->numero}",
                    ]);

                    Notification::make()
                        ->title('Pago registrado')
                        ->success()
                        ->send();

                    $record->refresh();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al registrar pago')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->visible(fn ($record) => in_array($record->estado?->value, [
                CompraEstado::REGISTRADA->value,
                CompraEstado::PENDIENTE->value,
            ]));
    }
}
