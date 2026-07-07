<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProveedorResource\RelationManagers;

use App\Models\Producto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CodigosProductosRelationManager extends RelationManager
{
    protected static string $relationship = 'codigosProductos';

    protected static ?string $title = 'Mapeo de Códigos de Producto';

    protected static ?string $modelLabel = 'Mapeo';

    protected static ?string $pluralModelLabel = 'Mapeos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('producto_id')
                    ->label('Producto Interno')
                    ->options(Producto::where('activo', true)->pluck('nombre', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('codigo_proveedor')
                    ->label('Código del Proveedor')
                    ->required()
                    ->maxLength(100),
                TextInput::make('descripcion_proveedor')
                    ->label('Descripción del Proveedor (Opcional)')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('codigo_proveedor')
            ->columns([
                TextColumn::make('codigo_proveedor')
                    ->label('Código Proveedor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('producto.nombre')
                    ->label('Producto Interno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion_proveedor')
                    ->label('Descripción Proveedor')
                    ->searchable()
                    ->placeholder('—'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
