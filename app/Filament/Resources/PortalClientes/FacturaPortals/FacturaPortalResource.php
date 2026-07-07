<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\FacturaPortals;

use App\Filament\Resources\PortalClientes\FacturaPortals\Pages\ListFacturaPortals;
use App\Filament\Resources\PortalClientes\FacturaPortals\Tables\FacturaPortalsTable;
use App\Models\Venta;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class FacturaPortalResource extends Resource
{
    protected static ?string $model = Venta::class;

    protected static ?string $navigationLabel = 'Mis Facturas';

    protected static string|UnitEnum|null $navigationGroup = 'Mis Documentos';

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return 'Mis Facturas';
    }

    public static function getPluralLabel(): string
    {
        return 'Mis Facturas';
    }

    public static function canAccess(): bool
    {
        return Auth::guard('cliente')->check();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return FacturaPortalsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacturaPortals::route('/'),
        ];
    }
}
