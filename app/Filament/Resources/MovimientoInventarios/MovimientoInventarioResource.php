<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoInventarios;

use App\Filament\Resources\MovimientoInventarios\Pages\ListMovimientoInventarios;
use App\Filament\Resources\MovimientoInventarios\Pages\ViewMovimientoInventario;
use App\Filament\Resources\MovimientoInventarios\Schemas\MovimientoInventarioInfolist;
use App\Filament\Resources\MovimientoInventarios\Tables\MovimientoInventariosTable;
use App\Models\MovimientoInventario;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MovimientoInventarioResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = MovimientoInventario::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Kardex';

    protected static ?string $modelLabel = 'Movimiento de Inventario';

    protected static ?string $pluralModelLabel = 'Movimientos de Inventario';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MovimientoInventarioInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovimientoInventariosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovimientoInventarios::route('/'),
            'view' => ViewMovimientoInventario::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('movimiento_inventario.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
