<?php

declare(strict_types=1);

namespace App\Filament\Resources\Productos\Tables;

use App\Filament\Exports\ProductoExporter;
use App\Filament\Resources\Productos\ProductoResource;
use App\Filament\Tables\CommonTableFilters;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagen')
                    ->label('')
                    ->circular()
                    ->disk('directo')
                    ->defaultImageUrl(url('/images/producto.png')),
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('nombre_comun')
                    ->label('Nombre Común')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('codigo_barras')
                    ->label('Código de Barras')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('marca.nombre')
                    ->label('Marca')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('unidadMedida.simbolo')
                    ->label('Unidad')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('precio_venta')
                    ->label('Precio Venta')
                    ->currency()
                    ->sortable(),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                CommonTableFilters::categoria(),
                CommonTableFilters::marca(),
                TernaryFilter::make('activo')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->hidden(fn ($record) => $record->esInmutable()),
                Action::make('kardex')
                    ->label('Kardex')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('info')
                    ->url(fn ($record) => ProductoResource::getUrl('kardex', ['record' => $record])),
                DeleteAction::make()
                    ->hidden(fn ($record) => $record->esInmutable()),
            ])
            ->defaultPaginationPageOption(10)
            ->toolbarActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->exporter(ProductoExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
