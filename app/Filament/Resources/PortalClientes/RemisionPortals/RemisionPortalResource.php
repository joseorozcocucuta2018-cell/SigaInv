<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\RemisionPortals;

use App\Filament\Resources\PortalClientes\RemisionPortals\Pages\ListRemisionPortals;
use App\Filament\Resources\PortalClientes\RemisionPortals\Tables\RemisionPortalsTable;
use App\Models\Remision;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class RemisionPortalResource extends Resource
{
    protected static ?string $model = Remision::class;

    protected static ?string $navigationLabel = 'Mis Remisiones';

    protected static string|UnitEnum|null $navigationGroup = 'Mis Documentos';

    protected static ?int $navigationSort = 2;

    public static function getLabel(): string
    {
        return 'Mis Remisiones';
    }

    public static function getPluralLabel(): string
    {
        return 'Mis Remisiones';
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
        return RemisionPortalsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRemisionPortals::route('/'),
        ];
    }
}
