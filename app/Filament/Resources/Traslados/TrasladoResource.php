<?php

declare(strict_types=1);

namespace App\Filament\Resources\Traslados;

use App\Filament\Resources\Traslados\Pages\CreateTraslado;
use App\Filament\Resources\Traslados\Pages\EditTraslado;
use App\Filament\Resources\Traslados\Pages\ListTraslados;
use App\Filament\Resources\Traslados\Schemas\TrasladoForm;
use App\Filament\Resources\Traslados\Tables\TrasladoTable;
use App\Models\Bodega;
use App\Models\Traslado;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TrasladoResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Traslado::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Traslados entre Bodegas';

    protected static ?string $modelLabel = 'Traslado';

    protected static ?string $pluralModelLabel = 'Traslados';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return TrasladoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrasladoTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return (Bodega::count() > 1) && (Auth::user()?->can('traslado.ver') ?? false);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Bodega::count() > 1;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('traslado.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('traslado.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('traslado.eliminar') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTraslados::route('/'),
            'create' => CreateTraslado::route('/create'),
            'edit' => EditTraslado::route('/{record}/edit'),
        ];
    }
}
