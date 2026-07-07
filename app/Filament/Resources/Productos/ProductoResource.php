<?php

declare(strict_types=1);

namespace App\Filament\Resources\Productos;

use App\Filament\Resources\Productos\Pages\CreateProducto;
use App\Filament\Resources\Productos\Pages\EditProducto;
use App\Filament\Resources\Productos\Pages\KardexProducto;
use App\Filament\Resources\Productos\Pages\ListProductos;
use App\Filament\Resources\Productos\Pages\ViewProducto;
use App\Filament\Resources\Productos\Schemas\ProductoForm;
use App\Filament\Resources\Productos\Schemas\ProductoInfolist;
use App\Filament\Resources\Productos\Tables\ProductosTable;
use App\Models\Producto;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProductoResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Producto::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['codigo', 'nombre'];
    }

    public static function form(Schema $schema): Schema
    {
        return ProductoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductosTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductoInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductos::route('/'),
            'create' => CreateProducto::route('/create'),
            'view' => ViewProducto::route('/{record}'),
            'edit' => EditProducto::route('/{record}/edit'),
            'kardex' => KardexProducto::route('/{record}/kardex'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('producto.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('producto.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        /** @var Producto $record */
        if ($record->esInmutable()) {
            return false;
        }

        return Auth::user()?->can('producto.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        /** @var Producto $record */
        if ($record->esInmutable()) {
            return false;
        }

        return Auth::user()?->can('producto.eliminar') ?? false;
    }
}
