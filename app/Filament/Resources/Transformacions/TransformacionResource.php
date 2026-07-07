<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transformacions;

use App\Filament\Resources\Transformacions\Pages\CreateTransformacion;
use App\Filament\Resources\Transformacions\Pages\EditTransformacion;
use App\Filament\Resources\Transformacions\Pages\ListTransformacions;
use App\Filament\Resources\Transformacions\Pages\ViewTransformacion;
use App\Filament\Resources\Transformacions\Schemas\TransformacionForm;
use App\Filament\Resources\Transformacions\Schemas\TransformacionInfolist;
use App\Filament\Resources\Transformacions\Tables\TransformacionTable;
use App\Models\Transformacion;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TransformacionResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Transformacion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog';

    protected static string|UnitEnum|null $navigationGroup = 'Fórmulas de Transformación';

    protected static ?string $navigationLabel = 'Transformaciones';

    protected static ?string $modelLabel = 'Transformación';

    protected static ?string $pluralModelLabel = 'Transformaciones';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return TransformacionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TransformacionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransformacionTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransformacions::route('/'),
            'create' => CreateTransformacion::route('/create'),
            'view' => ViewTransformacion::route('/{record}'),
            'edit' => EditTransformacion::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('transformacion.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('transformacion.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('transformacion.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('transformacion.eliminar') ?? false;
    }
}
