<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cotizacions;

use App\Filament\Resources\Cotizacions\Pages\CreateCotizacion;
use App\Filament\Resources\Cotizacions\Pages\EditCotizacion;
use App\Filament\Resources\Cotizacions\Pages\ListCotizacions;
use App\Filament\Resources\Cotizacions\Pages\ViewCotizacion;
use App\Filament\Resources\Cotizacions\Schemas\CotizacionForm;
use App\Filament\Resources\Cotizacions\Tables\CotizacionsTable;
use App\Models\Cotizacion;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CotizacionResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Cotizacion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Cotizaciones';

    protected static ?string $modelLabel = 'Cotización';

    protected static ?string $pluralModelLabel = 'Cotizaciones';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['numero'];
    }

    public static function form(Schema $schema): Schema
    {
        return CotizacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CotizacionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCotizacions::route('/'),
            'create' => CreateCotizacion::route('/create'),
            'view' => ViewCotizacion::route('/{record}'),
            'edit' => EditCotizacion::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('cotizacion.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('cotizacion.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('cotizacion.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('cotizacion.eliminar') ?? false;
    }
}
