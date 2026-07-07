<?php

declare(strict_types=1);

namespace App\Filament\Resources\Devoluciones;

use App\Enums\DevolucionEstado;
use App\Filament\Resources\Devoluciones\Pages\CreateDevolucion;
use App\Filament\Resources\Devoluciones\Pages\EditDevolucion;
use App\Filament\Resources\Devoluciones\Pages\ListDevoluciones;
use App\Filament\Resources\Devoluciones\Pages\ViewDevolucion;
use App\Filament\Resources\Devoluciones\Schemas\DevolucionForm;
use App\Filament\Resources\Devoluciones\Tables\DevolucionesTable;
use App\Models\Devolucion;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DevolucionResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Devolucion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Devoluciones';

    protected static ?string $modelLabel = 'Devolución';

    protected static ?string $pluralModelLabel = 'Devoluciones';

    protected static ?int $navigationSort = 5;

    public static function getGloballySearchableAttributes(): array
    {
        return ['numero'];
    }

    public static function form(Schema $schema): Schema
    {
        return DevolucionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DevolucionesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDevoluciones::route('/'),
            'create' => CreateDevolucion::route('/create'),
            'view' => ViewDevolucion::route('/{record}'),
            'edit' => EditDevolucion::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('venta.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('venta.editar') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return (Auth::user()?->can('venta.editar') ?? false)
            && $record->estado?->value === DevolucionEstado::BORRADOR->value;
    }

    public static function canDelete(Model $record): bool
    {
        return (Auth::user()?->can('venta.editar') ?? false)
            && $record->estado?->value === DevolucionEstado::BORRADOR->value;
    }
}
