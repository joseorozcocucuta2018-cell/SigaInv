<?php

declare(strict_types=1);

namespace App\Filament\Resources\Empresas;

use App\Filament\Resources\Empresas\Pages\CreateEmpresa;
use App\Filament\Resources\Empresas\Pages\EditEmpresa;
use App\Filament\Resources\Empresas\Pages\ListEmpresas;
use App\Filament\Resources\Empresas\Pages\ViewEmpresa;
use App\Filament\Resources\Empresas\Schemas\EmpresaForm;
use App\Filament\Resources\Empresas\Schemas\EmpresaInfolist;
use App\Filament\Resources\Empresas\Tables\EmpresasTable;
use App\Models\Empresa;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class EmpresaResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Empresa::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Datos de la Empresa';

    protected static ?string $modelLabel = 'Empresa';

    protected static ?string $pluralModelLabel = 'Empresa';

    protected static ?int $navigationSort = 0;

    public static function isSuperAdmin(): bool
    {
        return Auth::user()?->email === 'joseforozco@gmail.com'
            || Auth::id() === 1;
    }

    public static function canCreate(): bool
    {
        return static::isSuperAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return static::isSuperAdmin();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['razon_social', 'nit'];
    }

    public static function form(Schema $schema): Schema
    {
        return EmpresaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmpresaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmpresasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmpresas::route('/'),
            'create' => CreateEmpresa::route('/create'),
            'view' => ViewEmpresa::route('/{record}'),
            'edit' => EditEmpresa::route('/{record}/edit'),
        ];
    }
}
