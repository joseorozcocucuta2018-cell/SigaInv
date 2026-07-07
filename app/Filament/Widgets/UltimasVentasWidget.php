<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\VentaEstado;
use App\Models\Venta;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class UltimasVentasWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Auth::user()?->can('venta.ver') ?? false;
    }

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Últimas ventas')
            ->description('Las 10 ventas más recientes')
            ->query(
                Venta::query()
                    ->with('cliente', 'usuario')
                    ->whereNot('estado', VentaEstado::BORRADOR->value)
                    ->latest('fecha')
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N°')
                    ->searchable()
                    ->width('120px')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (VentaEstado $state): string => $state->label())
                    ->color(fn (VentaEstado $state): string => $state->color()),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->currency()
                    ->alignRight()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('saldo_pendiente')
                    ->label('Saldo')
                    ->currency()
                    ->alignRight()
                    ->color(fn ($state): string => (float) $state > 0 ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fecha', 'desc')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(10);
    }
}
