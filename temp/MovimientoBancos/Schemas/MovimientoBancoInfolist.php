<?php

namespace App\Filament\Resources\MovimientoBancos\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MovimientoBancoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle del Movimiento Bancario')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('banco.nombre_banco')
                            ->label('Banco / Cuenta'),
                        TextEntry::make('fecha_movimiento')
                            ->label('Fecha')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('tipo')
                            ->label('Tipo Operación')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'deposito' => 'success',
                                'retiro' => 'danger',
                                'transferencia' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('monto')
                            ->label('Monto')
                            ->currency(),
                        TextEntry::make('saldo_actual')
                            ->label('Saldo Post-movimiento')
                            ->currency(),
                        TextEntry::make('referencia')
                            ->label('Referencia / Nro. Operación'),
                        TextEntry::make('usuario.name')
                            ->label('Registrado por'),
                    ]),
                TextEntry::make('concepto')
                    ->label('Concepto')
                    ->columnSpanFull(),
                Section::make('Origen')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('documento_tipo')
                            ->label('Tipo Documento Origen'),
                        TextEntry::make('documento_id')
                            ->label('ID Documento Origen'),
                    ]),
            ]);
    }
}
