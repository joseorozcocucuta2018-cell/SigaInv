<?php

namespace App\Filament\Resources\Cajas;

use App\Filament\Resources\Cajas\Pages\CreateCaja;
use App\Filament\Resources\Cajas\Pages\EditCaja;
use App\Filament\Resources\Cajas\Pages\ListCajas;
use App\Filament\Resources\Cajas\Schemas\CajaForm;
use App\Filament\Resources\Cajas\Tables\CajasTable;
use App\Models\Caja;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CajaResource extends Resource
{
    protected static ?string $model = Caja::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Caja';

    protected static ?string $navigationLabel = 'Cajas';

    protected static ?string $modelLabel = 'Caja';

    protected static ?string $pluralModelLabel = 'Cajas';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre'];
    }

    public static function form(Schema $schema): Schema
    {
        return CajaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CajasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCajas::route('/'),
            'create' => CreateCaja::route('/create'),
            'edit' => EditCaja::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('caja.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('caja.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        if ($record->tieneMovimientos()) {
            return false;
        }

        return Auth::user()?->can('caja.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        if ($record->tieneMovimientos()) {
            return false;
        }

        return Auth::user()?->can('caja.eliminar') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::exists() ? 'success' : 'danger';
    }
}
