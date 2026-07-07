<?php

declare(strict_types=1);

namespace App\Filament\Resources\DevolucionCompras;

use App\Filament\Resources\DevolucionCompras\Pages\CreateDevolucionCompra;
use App\Filament\Resources\DevolucionCompras\Pages\EditDevolucionCompra;
use App\Filament\Resources\DevolucionCompras\Pages\ListDevolucionCompras;
use App\Filament\Resources\DevolucionCompras\Schemas\DevolucionCompraForm;
use App\Filament\Resources\DevolucionCompras\Tables\DevolucionComprasTable;
use App\Models\DevolucionCompra;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DevolucionCompraResource extends Resource
{
    protected static ?string $model = DevolucionCompra::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static string|UnitEnum|null $navigationGroup = 'Compras';

    protected static ?string $navigationLabel = 'Devoluciones';

    protected static ?string $modelLabel = 'Devolución en Compra';

    protected static ?string $pluralModelLabel = 'Devoluciones en Compras';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return DevolucionCompraForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DevolucionComprasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDevolucionCompras::route('/'),
            'create' => CreateDevolucionCompra::route('/create'),
            'edit' => EditDevolucionCompra::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('compra.ver') ?? false;
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
}
