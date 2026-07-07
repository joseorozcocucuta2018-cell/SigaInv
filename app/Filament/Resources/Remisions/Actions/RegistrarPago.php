<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions\Actions;

use App\Enums\BancoEstado;
use App\Enums\CajaEstado;
use App\Enums\CajaTipo;
use App\Enums\EstadoPagoEnum;
use App\Enums\PagoMedioEnum;
use App\Enums\RemisionEstado;
use App\Models\Banco;
use App\Models\Caja;
use App\Services\PagoClienteService;
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
                    Select::make('destino')
                        ->label('Destino del Pago')
                        ->options(PagoMedioEnum::class)
                        ->default(PagoMedioEnum::CAJA->value)
                        ->required()
                        ->live(),

                    Select::make('caja_id')
                        ->label('Caja')
                        ->options(Caja::where('estado', CajaEstado::ACTIVA->value)->where('tipo', '!=', CajaTipo::POS->value)->pluck('nombre', 'id'))
                        ->searchable()
                        ->visible(fn ($get) => $get('destino') === PagoMedioEnum::CAJA->value)
                        ->required(fn ($get) => $get('destino') === PagoMedioEnum::CAJA->value),

                    Select::make('banco_id')
                        ->label('Cuenta Bancaria')
                        ->options(Banco::where('estado', BancoEstado::ACTIVO)->pluck('nombre_banco', 'id'))
                        ->searchable()
                        ->visible(fn ($get) => $get('destino') === PagoMedioEnum::BANCO->value)
                        ->required(fn ($get) => $get('destino') === PagoMedioEnum::BANCO->value),

                    TextInput::make('monto')
                        ->label('Monto a Recibir')
                        ->numeric()
                        ->prefix('$')
                        ->default(fn ($record) => (float) $record->saldo_pendiente)
                        ->readOnly()
                        ->required(),

                    TextInput::make('referencia')
                        ->label('Referencia')
                        ->maxLength(100),
                ];
            })
            ->action(function ($record, array $data) {
                try {
                    PagoClienteService::crear([
                        'cliente_id' => $record->cliente_id,
                        'caja_id' => $data['destino'] === PagoMedioEnum::CAJA->value ? $data['caja_id'] : null,
                        'banco_id' => $data['destino'] === PagoMedioEnum::BANCO->value ? $data['banco_id'] : null,
                        'forma_pago_id' => $data['destino'] === PagoMedioEnum::CAJA->value ? 1 : 2,
                        'monto' => $data['monto'],
                        'fecha' => now(),
                        'referencia' => $data['referencia'] ?? null,
                        'observaciones' => "Pago de remisión {$record->numero}",
                    ]);

                    Notification::make()->title('Pago registrado')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Error al registrar pago')->body($e->getMessage())->danger()->send();
                }
            })
            ->visible(fn ($record) => in_array($record->estado?->value, [
                RemisionEstado::CONFIRMADA->value,
                RemisionEstado::FACTURADA->value,
            ]) && in_array($record->estado_pago?->value, [EstadoPagoEnum::PENDIENTE->value, EstadoPagoEnum::PARCIAL->value], true));
    }
}
