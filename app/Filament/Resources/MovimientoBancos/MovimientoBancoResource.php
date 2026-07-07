<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoBancos;

use App\Filament\Resources\MovimientoBancos\Pages\CreateMovimientoBanco;
use App\Filament\Resources\MovimientoBancos\Pages\ListMovimientoBancos;
use App\Filament\Resources\MovimientoBancos\Pages\ViewMovimientoBanco;
use App\Filament\Resources\MovimientoBancos\Schemas\MovimientoBancoForm;
use App\Filament\Resources\MovimientoBancos\Schemas\MovimientoBancoInfolist;
use App\Filament\Resources\MovimientoBancos\Tables\MovimientoBancosTable;
use App\Models\MovimientoBanco;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MovimientoBancoResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = MovimientoBanco::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string|UnitEnum|null $navigationGroup = 'Bancos';

    protected static ?string $navigationLabel = 'Movimientos de Bancos';

    protected static ?string $modelLabel = 'Movimiento Bancario';

    protected static ?string $pluralModelLabel = 'Movimientos Bancarios';

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['referencia'];
    }

    public static function form(Schema $schema): Schema
    {
        return MovimientoBancoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MovimientoBancoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovimientoBancosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovimientoBancos::route('/'),
            'create' => CreateMovimientoBanco::route('/create'),
            'view' => ViewMovimientoBanco::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('banco.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('banco.crear') ?? false;
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
