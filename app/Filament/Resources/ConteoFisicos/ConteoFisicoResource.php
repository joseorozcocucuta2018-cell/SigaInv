<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConteoFisicos;

use App\Enums\ConteoFisicoEstado;
use App\Filament\Resources\ConteoFisicos\Pages\CreateConteoFisico;
use App\Filament\Resources\ConteoFisicos\Pages\EditConteoFisico;
use App\Filament\Resources\ConteoFisicos\Pages\ListConteoFisicos;
use App\Filament\Resources\ConteoFisicos\Pages\ViewConteoFisico;
use App\Filament\Resources\ConteoFisicos\Schemas\ConteoFisicoForm;
use App\Filament\Resources\ConteoFisicos\Tables\ConteoFisicosTable;
use App\Models\ConteoFisico;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ConteoFisicoResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = ConteoFisico::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Conteos Físicos';

    protected static ?string $modelLabel = 'Conteo Físico';

    protected static ?int $navigationSort = 6;

    protected static ?string $pluralModelLabel = 'Conteos Físicos';

    public static function getGloballySearchableAttributes(): array
    {
        return ['numero'];
    }

    public static function form(Schema $schema): Schema
    {
        return ConteoFisicoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConteoFisicosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConteoFisicos::route('/'),
            'create' => CreateConteoFisico::route('/create'),
            'view' => ViewConteoFisico::route('/{record}'),
            'edit' => EditConteoFisico::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('conteo_fisico.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('conteo_fisico.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return (Auth::user()?->can('conteo_fisico.editar') ?? false)
            && $record->estado?->value === ConteoFisicoEstado::ABIERTO->value;
    }

    public static function canDelete(Model $record): bool
    {
        return (Auth::user()?->can('conteo_fisico.eliminar') ?? false)
            && $record->estado?->value === ConteoFisicoEstado::ABIERTO->value;
    }
}
