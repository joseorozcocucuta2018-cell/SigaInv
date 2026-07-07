<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notas;

use App\Filament\Resources\Notas\Schemas\NotaForm;
use App\Filament\Resources\Notas\Tables\NotasTable;
use App\Models\Nota;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class NotaResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Nota::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $modelLabel = 'Nota Crédito/Débito';

    protected static ?string $pluralModelLabel = 'Notas Crédito/Débito';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return NotaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotas::route('/'),
            'create' => Pages\CreateNota::route('/create'),
            'edit' => Pages\EditNota::route('/{record}/edit'),
            'view' => Pages\ViewNota::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('venta.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('venta.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('venta.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('venta.eliminar') ?? false;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Notas pendientes por confirmar';
    }
}
