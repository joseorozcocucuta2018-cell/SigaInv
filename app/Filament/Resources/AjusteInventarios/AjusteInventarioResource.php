<?php

declare(strict_types=1);

namespace App\Filament\Resources\AjusteInventarios;

use App\Enums\AjusteEstado;
use App\Filament\Resources\AjusteInventarios\Pages\CreateAjusteInventario;
use App\Filament\Resources\AjusteInventarios\Pages\EditAjusteInventario;
use App\Filament\Resources\AjusteInventarios\Pages\ListAjusteInventarios;
use App\Filament\Resources\AjusteInventarios\Pages\ViewAjusteInventario;
use App\Filament\Resources\AjusteInventarios\Schemas\AjusteInventarioForm;
use App\Filament\Resources\AjusteInventarios\Tables\AjusteInventariosTable;
use App\Models\AjusteInventario;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AjusteInventarioResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = AjusteInventario::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Ajustes de Inventario';

    protected static ?string $modelLabel = 'Ajuste de Inventario';

    protected static ?string $pluralModelLabel = 'Ajustes de Inventario';

    protected static ?int $navigationSort = 7;

    public static function getGloballySearchableAttributes(): array
    {
        return ['numero'];
    }

    public static function form(Schema $schema): Schema
    {
        return AjusteInventarioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AjusteInventariosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAjusteInventarios::route('/'),
            'create' => CreateAjusteInventario::route('/create'),
            'view' => ViewAjusteInventario::route('/{record}'),
            'edit' => EditAjusteInventario::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('ajuste_inventario.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('ajuste_inventario.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return (Auth::user()?->can('ajuste_inventario.editar') ?? false)
            && $record->estado?->value === AjusteEstado::BORRADOR->value;
    }

    public static function canDelete(Model $record): bool
    {
        return (Auth::user()?->can('ajuste_inventario.eliminar') ?? false)
            && $record->estado?->value === AjusteEstado::BORRADOR->value;
    }
}
