<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\CotizacionPortals;

use App\Filament\Resources\PortalClientes\CotizacionPortals\Pages\ListCotizacionPortals;
use App\Filament\Resources\PortalClientes\CotizacionPortals\Tables\CotizacionPortalsTable;
use App\Models\Cotizacion;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CotizacionPortalResource extends Resource
{
    protected static ?string $model = Cotizacion::class;

    protected static ?string $navigationLabel = 'Mis Cotizaciones';

    protected static string|UnitEnum|null $navigationGroup = 'Mis Documentos';

    protected static ?int $navigationSort = 3;

    public static function getLabel(): string
    {
        return 'Mis Cotizaciones';
    }

    public static function getPluralLabel(): string
    {
        return 'Mis Cotizaciones';
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
        return CotizacionPortalsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCotizacionPortals::route('/'),
        ];
    }
}
