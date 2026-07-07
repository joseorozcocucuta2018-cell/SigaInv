<?php

namespace App\Filament\Resources\Bancos\Tables;

use App\Enums\BancoEstado;
use App\Enums\BancoTipoCuenta;
use App\Models\MovimientoBanco;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BancosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_banco')
                    ->label('Banco')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('numero_cuenta')
                    ->label('Nro. Cuenta')
                    ->searchable(),
                TextColumn::make('tipo_cuenta')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => BancoTipoCuenta::tryFrom($state)?->label() ?? $state),
                TextColumn::make('saldo_inicial')
                    ->label('Saldo Inicial')
                    ->currency()
                    ->sortable(),
                TextColumn::make('saldo_actual')
                    ->label('Saldo Actual')
                    ->currency()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (BancoEstado $state): string => $state->label())
                    ->color(fn (BancoEstado $state): string => $state->color()),
            ])
            ->filters([
                SelectFilter::make('tipo_cuenta')
                    ->label('Tipo')
                    ->options([
                        'ahorros' => 'Ahorros',
                        'corriente' => 'Corriente',
                    ]),
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(BancoEstado::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => ! MovimientoBanco::where('banco_id', $record->id)->exists()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
