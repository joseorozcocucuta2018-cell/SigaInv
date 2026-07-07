<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bodegas;

use App\Filament\Resources\Bodegas\Pages\CreateBodega;
use App\Filament\Resources\Bodegas\Pages\EditBodega;
use App\Filament\Resources\Bodegas\Pages\ListBodegas;
use App\Filament\Resources\Bodegas\Schemas\BodegaForm;
use App\Filament\Resources\Bodegas\Tables\BodegasTable;
use App\Models\Bodega;
use App\Models\Empresa;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class BodegaResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Bodega::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Bodegas';

    protected static ?string $modelLabel = 'Bodega';

    protected static ?string $pluralModelLabel = 'Bodegas';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre'];
    }

    public static function form(Schema $schema): Schema
    {
        return BodegaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BodegasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBodegas::route('/'),
            'create' => CreateBodega::route('/create'),
            'edit' => EditBodega::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('bodega.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        if (! (Auth::user()?->can('bodega.crear') ?? false)) {
            return false;
        }

        return ! Empresa::usaUnaSolaBodega();
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('bodega.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('bodega.eliminar') ?? false;
    }
}
