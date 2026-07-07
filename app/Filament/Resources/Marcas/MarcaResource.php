<?php

declare(strict_types=1);

namespace App\Filament\Resources\Marcas;

use App\Filament\Resources\Marcas\Pages\CreateMarca;
use App\Filament\Resources\Marcas\Pages\EditMarca;
use App\Filament\Resources\Marcas\Pages\ListMarcas;
use App\Filament\Resources\Marcas\Schemas\MarcaForm;
use App\Filament\Resources\Marcas\Tables\MarcasTable;
use App\Models\Marca;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MarcaResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Marca::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bookmark';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Marcas';

    protected static ?string $modelLabel = 'Marca';

    protected static ?string $pluralModelLabel = 'Marcas';

    protected static ?int $navigationSort = 3;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre'];
    }

    public static function form(Schema $schema): Schema
    {
        return MarcaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarcasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarcas::route('/'),
            'create' => CreateMarca::route('/create'),
            'edit' => EditMarca::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('marca.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('marca.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('marca.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('marca.eliminar') ?? false;
    }
}
