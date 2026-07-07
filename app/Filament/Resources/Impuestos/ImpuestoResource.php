<?php

declare(strict_types=1);

namespace App\Filament\Resources\Impuestos;

use App\Filament\Resources\Impuestos\Pages\CreateImpuesto;
use App\Filament\Resources\Impuestos\Pages\EditImpuesto;
use App\Filament\Resources\Impuestos\Pages\ListImpuestos;
use App\Filament\Resources\Impuestos\Schemas\ImpuestoForm;
use App\Filament\Resources\Impuestos\Tables\ImpuestosTable;
use App\Models\Impuesto;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ImpuestoResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Impuesto::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Impuestos';

    protected static ?string $modelLabel = 'Impuesto';

    protected static ?string $pluralModelLabel = 'Impuestos';

    protected static ?int $navigationSort = 4;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre'];
    }

    public static function form(Schema $schema): Schema
    {
        return ImpuestoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImpuestosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListImpuestos::route('/'),
            'create' => CreateImpuesto::route('/create'),
            'edit' => EditImpuesto::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('impuesto.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('impuesto.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('impuesto.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('impuesto.eliminar') ?? false;
    }
}
