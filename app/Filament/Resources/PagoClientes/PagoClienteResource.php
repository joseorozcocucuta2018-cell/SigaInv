<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoClientes;

use App\Filament\Resources\PagoClientes\Pages\CreatePagoCliente;
use App\Filament\Resources\PagoClientes\Pages\EditPagoCliente;
use App\Filament\Resources\PagoClientes\Pages\ListPagoClientes;
use App\Filament\Resources\PagoClientes\Pages\ViewPagoCliente;
use App\Filament\Resources\PagoClientes\Schemas\PagoClienteForm;
use App\Filament\Resources\PagoClientes\Tables\PagoClientesTable;
use App\Models\PagoCliente;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PagoClienteResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = PagoCliente::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Pagos de Clientes';

    protected static ?string $modelLabel = 'Pago de Cliente';

    protected static ?string $pluralModelLabel = 'Pagos de Clientes';

    protected static ?int $navigationSort = 4;

    public static function getGloballySearchableAttributes(): array
    {
        return ['numero'];
    }

    public static function form(Schema $schema): Schema
    {
        return PagoClienteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PagoClientesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPagoClientes::route('/'),
            'create' => CreatePagoCliente::route('/create'),
            'view' => ViewPagoCliente::route('/{record}'),
            'edit' => EditPagoCliente::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('pago_cliente.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('pago_cliente.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('pago_cliente.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('pago_cliente.eliminar') ?? false;
    }
}
