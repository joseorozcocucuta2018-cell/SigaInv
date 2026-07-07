<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Producto;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StockBajoWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Auth::user()?->can('stock.ver') ?? false;
    }

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Productos con stock bajo')
            ->description('Stock actual menor o igual al mínimo configurado')
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->width('110px'),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Producto')
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('stock_total')
                    ->label('Stock actual')
                    ->getStateUsing(fn (Producto $record): float => (float) $record->stock_total)
                    ->formatStateUsing(fn ($state, Producto $record): string => number_format($state, 2).' '.($record->unidadMedida?->simbolo ?? '')
                    )
                    ->color(fn (Producto $record): string => (float) $record->stock_total <= 0 ? 'danger' : 'warning'
                    ),

                Tables\Columns\TextColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->formatStateUsing(fn ($state, Producto $record): string => number_format((float) $state, 2).' '.($record->unidadMedida?->simbolo ?? '')
                    )
                    ->color('gray'),

                Tables\Columns\TextColumn::make('precio_compra')
                    ->label('Costo')
                    ->currency()
                    ->alignRight(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10);
    }

    private function getQuery(): Builder
    {
        return Producto::query()
            ->where('activo', true)
            ->where('stock_minimo', '>', 0)
            ->withSum('stockBodegas as stock_total', 'cantidad')
            ->havingRaw('stock_total <= stock_minimo')
            ->with('unidadMedida', 'categoria')
            ->orderBy('nombre');
    }
}
