<?php

declare(strict_types=1);

namespace App\Filament\Resources\Turnos;

use App\Filament\Resources\Turnos\Pages\ListTurnos;
use App\Filament\Resources\Turnos\Tables\TurnosTable;
use App\Models\Turno;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TurnoResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Turno::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|UnitEnum|null $navigationGroup = 'Caja';

    protected static ?string $navigationLabel = 'Turnos';

    protected static ?string $modelLabel = 'Turno';

    protected static ?string $pluralModelLabel = 'Turnos';

    protected static ?int $navigationSort = 3;

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return TurnosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTurnos::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('turno.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
