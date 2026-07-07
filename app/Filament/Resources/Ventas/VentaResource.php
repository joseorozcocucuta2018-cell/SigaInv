<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ventas;

use App\Enums\NumeracionEstado;
use App\Filament\Resources\Ventas\Pages\CreateVenta;
use App\Filament\Resources\Ventas\Pages\EditVenta;
use App\Filament\Resources\Ventas\Pages\ListVentas;
use App\Filament\Resources\Ventas\Pages\ViewVenta;
use App\Filament\Resources\Ventas\Schemas\VentaForm;
use App\Filament\Resources\Ventas\Tables\VentasTable;
use App\Models\Numeracion;
use App\Models\Venta;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class VentaResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Venta::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $modelLabel = 'Venta';

    protected static ?string $pluralModelLabel = 'Ventas';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return Auth::user()?->can('venta.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('venta.crear')
            && Numeracion::where('tipo_documento', 'venta')
                ->where('estado', NumeracionEstado::ACTIVO)
                ->whereNotNull('resolucion_numero')
                ->exists();
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('venta.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('venta.eliminar') ?? false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['numero'];
    }

    public static function form(Schema $schema): Schema
    {
        return VentaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VentasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVentas::route('/'),
            'create' => CreateVenta::route('/create'),
            'view' => ViewVenta::route('/{record}'),
            'edit' => EditVenta::route('/{record}/edit'),
        ];
    }
}
