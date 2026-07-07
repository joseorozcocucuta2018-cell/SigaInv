<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormulaTransformacions;

use App\Filament\Resources\FormulaTransformacions\Pages\CreateFormulaTransformacion;
use App\Filament\Resources\FormulaTransformacions\Pages\EditFormulaTransformacion;
use App\Filament\Resources\FormulaTransformacions\Pages\ListFormulaTransformacions;
use App\Filament\Resources\FormulaTransformacions\Pages\ViewFormulaTransformacion;
use App\Filament\Resources\FormulaTransformacions\Schemas\FormulaTransformacionForm;
use App\Filament\Resources\FormulaTransformacions\Tables\FormulaTransformacionTable;
use App\Models\FormulaTransformacion;
use App\Traits\HasNavigationBadgeColor;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FormulaTransformacionResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = FormulaTransformacion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|\UnitEnum|null $navigationGroup = 'Fórmulas de Transformación';

    protected static ?string $navigationLabel = 'Fórmulas';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['producto_final_nombre'];
    }

    public static function form(Schema $schema): Schema
    {
        return FormulaTransformacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormulaTransformacionTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFormulaTransformacions::route('/'),
            'create' => CreateFormulaTransformacion::route('/create'),
            'view' => ViewFormulaTransformacion::route('/{record}'),
            'edit' => EditFormulaTransformacion::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return 'Fórmula de transformación';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Fórmulas de transformación';
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('formula_transformacion.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('formula_transformacion.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        /** @var FormulaTransformacion $record */
        return ! $record->tiene_transformaciones && ! $record->bloqueada && (Auth::user()?->can('formula_transformacion.editar') ?? false);
    }

    public static function canDelete(Model $record): bool
    {
        /** @var FormulaTransformacion $record */
        return ! $record->tiene_transformaciones && ! $record->bloqueada && (Auth::user()?->can('formula_transformacion.eliminar') ?? false);
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total de fórmulas';
    }
}
