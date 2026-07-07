<?php

namespace App\Filament\Resources\MovimientoCajas;

use App\Filament\Resources\MovimientoCajas\Pages\CreateMovimientoCaja;
use App\Filament\Resources\MovimientoCajas\Pages\EditMovimientoCaja;
use App\Filament\Resources\MovimientoCajas\Pages\ListMovimientoCajas;
use App\Filament\Resources\MovimientoCajas\Pages\ViewMovimientoCaja;
use App\Filament\Resources\MovimientoCajas\Schemas\MovimientoCajaForm;
use App\Filament\Resources\MovimientoCajas\Tables\MovimientoCajasTable;
use App\Models\MovimientoCaja;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MovimientoCajaResource extends Resource
{
    protected static ?string $model = MovimientoCaja::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = 'Caja';

    protected static ?string $navigationLabel = 'Movimientos de Caja';

    protected static ?string $modelLabel = 'Movimiento de Caja';

    protected static ?string $pluralModelLabel = 'Movimientos de Caja';

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['referencia', 'concepto'];
    }

    public static function form(Schema $schema): Schema
    {
        return MovimientoCajaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovimientoCajasTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('movimiento_caja.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('movimiento_caja.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::exists() ? 'success' : 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovimientoCajas::route('/'),
            'create' => CreateMovimientoCaja::route('/create'),
            'edit' => EditMovimientoCaja::route('/{record}/edit'),
            'view' => ViewMovimientoCaja::route('/{record}'),
        ];
    }
}
