<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bancos;

use App\Filament\Resources\Bancos\Pages\CreateBanco;
use App\Filament\Resources\Bancos\Pages\EditBanco;
use App\Filament\Resources\Bancos\Pages\ListBancos;
use App\Filament\Resources\Bancos\Schemas\BancoForm;
use App\Filament\Resources\Bancos\Tables\BancosTable;
use App\Models\Banco;
use App\Models\MovimientoBanco;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class BancoResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Banco::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static string|UnitEnum|null $navigationGroup = 'Bancos';

    protected static ?string $navigationLabel = 'Bancos';

    protected static ?string $modelLabel = 'Banco';

    protected static ?string $pluralModelLabel = 'Bancos';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre_banco', 'numero_cuenta'];
    }

    public static function form(Schema $schema): Schema
    {
        return BancoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BancosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBancos::route('/'),
            'create' => CreateBanco::route('/create'),
            'edit' => EditBanco::route('/{record}/edit'),
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
        return Auth::user()?->can('banco.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        if (MovimientoBanco::where('banco_id', $record->id)->exists()) {
            return false;
        }

        return Auth::user()?->can('banco.eliminar') ?? false;
    }
}
