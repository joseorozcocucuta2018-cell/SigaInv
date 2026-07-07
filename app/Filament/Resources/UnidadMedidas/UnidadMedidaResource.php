<?php

declare(strict_types=1);

namespace App\Filament\Resources\UnidadMedidas;

use App\Filament\Resources\UnidadMedidas\Pages\CreateUnidadMedida;
use App\Filament\Resources\UnidadMedidas\Pages\EditUnidadMedida;
use App\Filament\Resources\UnidadMedidas\Pages\ListUnidadMedidas;
use App\Filament\Resources\UnidadMedidas\Schemas\UnidadMedidaForm;
use App\Filament\Resources\UnidadMedidas\Tables\UnidadMedidasTable;
use App\Models\UnidadMedida;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class UnidadMedidaResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = UnidadMedida::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Unidades de Medida';

    protected static ?string $modelLabel = 'Unidad de Medida';

    protected static ?string $pluralModelLabel = 'Unidades de Medida';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre'];
    }

    public static function form(Schema $schema): Schema
    {
        return UnidadMedidaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnidadMedidasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnidadMedidas::route('/'),
            'create' => CreateUnidadMedida::route('/create'),
            'edit' => EditUnidadMedida::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('unidad_medida.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('unidad_medida.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('unidad_medida.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('unidad_medida.eliminar') ?? false;
    }
}
