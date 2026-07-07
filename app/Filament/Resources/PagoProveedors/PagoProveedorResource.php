<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoProveedors;

use App\Filament\Resources\PagoProveedors\Pages\CreatePagoProveedor;
use App\Filament\Resources\PagoProveedors\Pages\EditPagoProveedor;
use App\Filament\Resources\PagoProveedors\Pages\ListPagoProveedors;
use App\Filament\Resources\PagoProveedors\Pages\ViewPagoProveedor;
use App\Filament\Resources\PagoProveedors\Schemas\PagoProveedorForm;
use App\Filament\Resources\PagoProveedors\Tables\PagoProveedorsTable;
use App\Models\PagoProveedor;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PagoProveedorResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = PagoProveedor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Compras';

    protected static ?string $navigationLabel = 'Pagos a Proveedores';

    protected static ?string $modelLabel = 'Comprobante de Egreso';

    protected static ?string $pluralModelLabel = 'Pagos a Proveedores';

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['numero'];
    }

    public static function form(Schema $schema): Schema
    {
        return PagoProveedorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PagoProveedorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPagoProveedors::route('/'),
            'create' => CreatePagoProveedor::route('/create'),
            'view' => ViewPagoProveedor::route('/{record}'),
            'edit' => EditPagoProveedor::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('pago_proveedor.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('pago_proveedor.crear') ?? false;
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
