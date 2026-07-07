<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormaPagos;

use App\Filament\Resources\FormaPagos\Pages\CreateFormaPago;
use App\Filament\Resources\FormaPagos\Pages\EditFormaPago;
use App\Filament\Resources\FormaPagos\Pages\ListFormaPagos;
use App\Filament\Resources\FormaPagos\Schemas\FormaPagoForm;
use App\Filament\Resources\FormaPagos\Tables\FormaPagosTable;
use App\Models\FormaPago;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class FormaPagoResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = FormaPago::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Formas de Pago';

    protected static ?string $modelLabel = 'Forma de Pago';

    protected static ?string $pluralModelLabel = 'Formas de Pago';

    protected static ?int $navigationSort = 5;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre'];
    }

    public static function form(Schema $schema): Schema
    {
        return FormaPagoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormaPagosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFormaPagos::route('/'),
            'create' => CreateFormaPago::route('/create'),
            'edit' => EditFormaPago::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('forma_pago.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('forma_pago.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('forma_pago.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('forma_pago.eliminar') ?? false;
    }
}
