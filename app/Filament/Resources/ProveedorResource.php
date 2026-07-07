<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\DocumentoTipoEnum;
use App\Enums\ProveedorEstado;
use App\Filament\Exports\ProveedorExporter;
use App\Filament\Resources\ProveedorResource\Pages;
use App\Models\Ciudad;
use App\Models\Compra;
use App\Models\Departamento;
use App\Models\PagoProveedor;
use App\Models\Proveedor;
use App\Traits\HasNavigationBadgeColor;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProveedorResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = Proveedor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?string $modelLabel = 'Proveedor';

    protected static ?string $pluralModelLabel = 'Proveedores';

    protected static ?int $navigationSort = 3;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre', 'documento', 'email'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificación')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre / Razón Social')
                        ->required()
                        ->maxLength(100),
                    Select::make('tipo_documento')
                        ->label('Tipo Documento')
                        ->options(DocumentoTipoEnum::class)
                        ->default(DocumentoTipoEnum::NIT->value)
                        ->required(),
                    TextInput::make('documento')
                        ->label('Número Documento')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20),
                    TextInput::make('contacto_principal')
                        ->label('Contacto Principal')
                        ->maxLength(100),
                ]),
            Section::make('Contacto')
                ->columns(2)
                ->schema([
                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->required()
                        ->maxLength(30),
                    TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),
                    TextInput::make('sitio_web')
                        ->label('Sitio Web')
                        ->url()
                        ->maxLength(255),
                    TextInput::make('pais')
                        ->label('País')
                        ->default('Colombia')
                        ->maxLength(100),
                ]),
            Section::make('Ubicación')
                ->columns(2)
                ->schema([
                    Select::make('departamento_id')
                        ->label('Departamento')
                        ->options(Departamento::orderBy('nombre')->pluck('nombre', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('ciudad_id', null)),
                    Select::make('ciudad_id')
                        ->label('Ciudad')
                        ->options(fn (Get $get): Collection => Ciudad::where('departamento_id', $get('departamento_id'))
                            ->orderBy('nombre')
                            ->pluck('nombre', 'id'))
                        ->searchable()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('nombre')
                                ->required()
                                ->maxLength(100),
                        ])
                        ->createOptionUsing(function (array $data, Get $get): int {
                            return Ciudad::create([
                                'nombre' => strtoupper($data['nombre']),
                                'departamento_id' => $get('departamento_id'),
                            ])->id;
                        })
                        ->visible(fn (Get $get) => filled($get('departamento_id'))),
                    TextInput::make('direccion1')
                        ->label('Dirección')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('direccion2')
                        ->label('Dirección 2')
                        ->maxLength(100),
                ]),
            Section::make('Condiciones comerciales')
                ->columns(2)
                ->schema([
                    TextInput::make('limite_credito')
                        ->label('Límite de Crédito')
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    TextInput::make('dias_credito')
                        ->label('Días de Crédito')
                        ->numeric()
                        ->default(0),
                    TextInput::make('dias_pago')
                        ->label('Días de Pago')
                        ->numeric()
                        ->default(0),
                    TextInput::make('saldo')
                        ->label('Saldo')
                        ->numeric()
                        ->prefix('$')
                        ->disabled()
                        ->dehydrated(false),
                ]),
            Section::make('Estado')
                ->schema([
                    Select::make('estado')
                        ->label('Estado')
                        ->options(ProveedorEstado::class)
                        ->default(ProveedorEstado::ACTIVO)
                        ->required(),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificación')
                ->columns(2)
                ->schema([
                    TextEntry::make('nombre')->label('Nombre / Razón Social'),
                    TextEntry::make('tipo_documento')->label('Tipo Documento'),
                    TextEntry::make('documento')->label('Número Documento'),
                    TextEntry::make('contacto_principal')->label('Contacto Principal')->placeholder('—'),
                ]),
            Section::make('Contacto')
                ->columns(2)
                ->schema([
                    TextEntry::make('telefono')->label('Teléfono'),
                    TextEntry::make('email')->label('Correo')->copyable(),
                    TextEntry::make('sitio_web')->label('Sitio Web')->placeholder('—'),
                    TextEntry::make('pais')->label('País'),
                ]),
            Section::make('Ubicación')
                ->columns(2)
                ->schema([
                    TextEntry::make('departamento.nombre')->label('Departamento'),
                    TextEntry::make('ciudad.nombre')->label('Ciudad'),
                    TextEntry::make('direccion1')->label('Dirección'),
                    TextEntry::make('direccion2')->label('Dirección 2')->placeholder('—'),
                ]),
            Section::make('Condiciones comerciales')
                ->columns(2)
                ->schema([
                    TextEntry::make('saldo')->label('Saldo')->currency(),
                    TextEntry::make('limite_credito')->label('Límite de Crédito')->currency(),
                    TextEntry::make('dias_credito')->label('Días de Crédito'),
                    TextEntry::make('dias_pago')->label('Días de Pago'),
                ]),
            Section::make('Estado')
                ->columns(2)
                ->schema([
                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn (ProveedorEstado $state): string => $state->label())
                        ->color(fn (ProveedorEstado $state): string => $state->color()),
                    TextEntry::make('usuario.name')->label('Registrado por'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('documento')
                    ->label('Documento')
                    ->searchable(),
                TextColumn::make('telefono')
                    ->label('Teléfono'),
                TextColumn::make('ciudad.nombre')
                    ->label('Ciudad')
                    ->sortable(),
                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->currency()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (ProveedorEstado $state): string => $state->label())
                    ->color(fn (ProveedorEstado $state): string => $state->color()),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(ProveedorEstado::class),
            ])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->id !== 1),
                DeleteAction::make()
                    ->visible(fn ($record) => Auth::user()?->hasRole('administrador') && $record->id !== 1
                    )
                    ->before(function (DeleteAction $action, $record) {
                        $tieneMovimientos =
                            Compra::where('proveedor_id', $record->id)->exists() ||
                            PagoProveedor::where('proveedor_id', $record->id)->exists();

                        if ($tieneMovimientos) {
                            Notification::make()
                                ->title('No se puede eliminar')
                                ->body('El proveedor tiene compras o pagos registrados.')
                                ->danger()->send();
                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->exporter(ProveedorExporter::class),
                BulkActionGroup::make([
                    // Sin bulk actions destructivas
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProveedorResource\RelationManagers\ComprasRelationManager::class,
            ProveedorResource\RelationManagers\PagosProveedorRelationManager::class,
            ProveedorResource\RelationManagers\ProductosCompradosRelationManager::class,
            ProveedorResource\RelationManagers\CodigosProductosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProveedores::route('/'),
            'create' => Pages\CreateProveedor::route('/create'),
            'view' => Pages\ViewProveedor::route('/{record}'),
            'edit' => Pages\EditProveedor::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('proveedor.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('proveedor.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('proveedor.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('proveedor.eliminar') ?? false;
    }
}
