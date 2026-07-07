<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions;

use App\Filament\Resources\Remisions\Pages\CreateRemision;
use App\Filament\Resources\Remisions\Pages\EditRemision;
use App\Filament\Resources\Remisions\Pages\ListRemisions;
use App\Filament\Resources\Remisions\Pages\ViewRemision;
use App\Filament\Resources\Remisions\Schemas\RemisionForm;
use App\Filament\Resources\Remisions\Tables\RemisionsTable;
use App\Models\Remision;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class RemisionResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Remision::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Remisiones';

    protected static ?string $modelLabel = 'Remisión';

    protected static ?string $pluralModelLabel = 'Remisiones';

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['numero'];
    }

    public static function form(Schema $schema): Schema
    {
        return RemisionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RemisionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRemisions::route('/'),
            'create' => CreateRemision::route('/create'),
            'view' => ViewRemision::route('/{record}'),
            'edit' => EditRemision::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('remision.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('remision.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('remision.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('remision.eliminar') ?? false;
    }
}
