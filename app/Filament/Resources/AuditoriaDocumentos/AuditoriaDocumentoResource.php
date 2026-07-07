<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditoriaDocumentos;

use App\Filament\Resources\AuditoriaDocumentos\Tables\AuditoriaDocumentosTable;
use App\Models\AuditoriaDocumento;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AuditoriaDocumentoResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = AuditoriaDocumento::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-finger-print';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?string $modelLabel = 'Auditoría de Documento';

    protected static ?string $pluralModelLabel = 'Auditoría de Documentos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return AuditoriaDocumentosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditoriaDocumentos::route('/'),
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
