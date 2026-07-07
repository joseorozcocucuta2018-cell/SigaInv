<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoInventarios\Schemas;

use App\Enums\MovimientoInventarioTipo;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MovimientoInventarioInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Movimiento')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('producto.nombre')
                            ->label('Producto'),
                        TextEntry::make('bodega.nombre')
                            ->label('Bodega'),
                        TextEntry::make('tipo_movimiento')
                            ->label('Tipo')
                            ->badge()
                            ->formatStateUsing(fn (MovimientoInventarioTipo $state): string => $state->label())
                            ->color(fn (MovimientoInventarioTipo $state): string => $state->color()),
                        TextEntry::make('cantidad')
                            ->label('Cantidad')
                            ->numeric(3),
                        TextEntry::make('costo_unitario')
                            ->label('Costo Unitario')
                            ->currency(),
                        TextEntry::make('stock_resultante')
                            ->label('Stock Resultante')
                            ->numeric(3),
                        TextEntry::make('fecha_movimiento')
                            ->label('Fecha')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('usuario.name')
                            ->label('Usuario'),
                    ]),
                Section::make('Documento Relacionado')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('documento_tipo')
                            ->label('Tipo Documento'),
                        TextEntry::make('documento_id')
                            ->label('ID Documento'),
                    ]),
                TextEntry::make('observacion')
                    ->label('Observación')
                    ->columnSpanFull(),
            ]);
    }
}
