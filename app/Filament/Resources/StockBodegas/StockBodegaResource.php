<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockBodegas;

use App\Filament\Resources\StockBodegas\Pages\ListStockBodegas;
use App\Filament\Resources\StockBodegas\Schemas\StockBodegaForm;
use App\Filament\Resources\StockBodegas\Schemas\StockBodegaInfolist;
use App\Filament\Resources\StockBodegas\Tables\StockBodegasTable;
use App\Models\StockBodega;
use App\Traits\HasNavigationBadgeColor;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StockBodegaResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = StockBodega::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Stock';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return StockBodegaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StockBodegaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockBodegasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockBodegas::route('/'),
        ];
    }
}
