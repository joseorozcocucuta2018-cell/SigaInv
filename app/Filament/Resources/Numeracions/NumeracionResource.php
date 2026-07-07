<?php

declare(strict_types=1);

namespace App\Filament\Resources\Numeracions;

use App\Filament\Resources\Numeracions\Pages\CreateNumeracion;
use App\Filament\Resources\Numeracions\Pages\EditNumeracion;
use App\Filament\Resources\Numeracions\Pages\ListNumeracions;
use App\Filament\Resources\Numeracions\Schemas\NumeracionForm;
use App\Filament\Resources\Numeracions\Tables\NumeracionsTable;
use App\Models\Numeracion;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class NumeracionResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Numeracion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Numeraciones';

    protected static ?string $modelLabel = 'Numeración';

    protected static ?string $pluralModelLabel = 'Numeraciones';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return NumeracionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NumeracionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNumeracions::route('/'),
            'create' => CreateNumeracion::route('/create'),
            'edit' => EditNumeracion::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('numeracion.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('numeracion.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('numeracion.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('numeracion.eliminar') ?? false;
    }
}
