<?php

declare(strict_types=1);

namespace App\Filament\Resources\Auditorias;

use App\Filament\Resources\Auditorias\Tables\AuditoriasTable;
use App\Models\AuditoriaDocumento;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AuditoriaResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = AuditoriaDocumento::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?string $modelLabel = 'Auditoría General';

    protected static ?string $pluralModelLabel = 'Auditorías Generales';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return AuditoriasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditorias::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('administrador') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Eventos de hoy';
    }
}
