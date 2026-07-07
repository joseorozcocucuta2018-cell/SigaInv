<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClienteResource\Schemas;

use App\Enums\ClienteEstado;
use App\Enums\DocumentoTipoEnum;
use App\Enums\PortalAccesoEnum;
use App\Models\Ciudad;
use App\Models\Departamento;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

/**
 * Schema compartido del Cliente — usado por:
 *  - App\Filament\Resources\ClienteResource::form()  (scope=admin)
 *  - App\Filament\Cliente\Pages\EditarPerfil::form()  (scope=cliente)
 *
 * Por scope:
 *  - 'admin'   : todos los campos (admin puede editarlo todo)
 *  - 'cliente' : solo los datos que el cliente puede actualizar
 *                 desde el portal. NO incluye: tipo_documento, documento,
 *                 email, estado, portal_acceso, sección de crédito
 *                 (estos son admin-only — los gestiona el personal de SIGAINV).
 */
class ClienteForm
{
    public const SCOPE_ADMIN = 'admin';

    public const SCOPE_CLIENTE = 'cliente';

    public static function configure(Schema $schema, string $scope = self::SCOPE_ADMIN): Schema
    {
        return $schema
            ->components([
                self::identificacionSection($scope),
                self::contactoSection($scope),
                self::ubicacionSection(),
                self::creditoSection($scope),
                self::estadoSection($scope),
            ]);
    }

    protected static function identificacionSection(string $scope): Section
    {
        $fields = [
            TextInput::make('nombre')
                ->label('Nombre / Razón Social')
                ->required()
                ->maxLength(100),
            TextInput::make('contacto_principal')
                ->label('Contacto Principal')
                ->maxLength(100),
        ];

        if ($scope === self::SCOPE_ADMIN) {
            $fields[] = Select::make('tipo_documento')
                ->label('Tipo Documento')
                ->options(DocumentoTipoEnum::class)
                ->default(DocumentoTipoEnum::CC->value)
                ->required();
            $fields[] = TextInput::make('documento')
                ->label('Número Documento')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(20);
        }

        return Section::make('Identificación')
            ->columns(2)
            ->schema($fields);
    }

    protected static function contactoSection(string $scope): Section
    {
        $fields = [
            TextInput::make('telefono')
                ->label('Teléfono')
                ->tel()
                ->required()
                ->maxLength(30),
            TextInput::make('sitio_web')
                ->label('Sitio Web')
                ->url()
                ->maxLength(255),
        ];

        if ($scope === self::SCOPE_ADMIN) {
            $fields[] = TextInput::make('email')
                ->label('Correo Electrónico')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(100);
        }

        return Section::make('Contacto')
            ->columns(2)
            ->schema($fields);
    }

    protected static function ubicacionSection(): Section
    {
        return Section::make('Ubicación')
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
            ]);
    }

    protected static function creditoSection(string $scope): Section
    {
        if ($scope !== self::SCOPE_ADMIN) {
            // Sección invisible para clientes — su crédito lo gestiona el admin.
            return Section::make('')->hidden();
        }

        return Section::make('Crédito y Precios')
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
                TextInput::make('porcentaje_descuento')
                    ->label('% Descuento')
                    ->numeric()
                    ->suffix('%')
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Descuento comercial (integradores/subdistribuidores)'),
                TextInput::make('saldo')
                    ->label('Saldo')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    protected static function estadoSection(string $scope): Section
    {
        if ($scope !== self::SCOPE_ADMIN) {
            return Section::make('')->hidden();
        }

        return Section::make('Estado')
            ->schema([
                Select::make('estado')
                    ->label('Estado')
                    ->options(ClienteEstado::class)
                    ->default(ClienteEstado::ACTIVO)
                    ->required(),
                Select::make('portal_acceso')
                    ->label('Acceso al Portal')
                    ->options(PortalAccesoEnum::class)
                    ->default(PortalAccesoEnum::SIN_ACCESO)
                    ->required(),
            ]);
    }

    /**
     * Lista de campos permitidos para el scope dado.
     *
     * El EditarPerfil lo usa en el save() para descartar cualquier
     * intento de inyección de campos no permitidos (mass assignment).
     */
    public static function camposEditables(string $scope): array
    {
        return match ($scope) {
            self::SCOPE_ADMIN => [
                'nombre',
                'contacto_principal',
                'tipo_documento',
                'documento',
                'telefono',
                'sitio_web',
                'email',
                'departamento_id',
                'ciudad_id',
                'direccion1',
                'direccion2',
                'limite_credito',
                'dias_credito',
                'dias_pago',
                'porcentaje_descuento',
                'estado',
                'portal_acceso',
            ],
            self::SCOPE_CLIENTE => [
                'nombre',
                'contacto_principal',
                'telefono',
                'sitio_web',
                'departamento_id',
                'ciudad_id',
                'direccion1',
                'direccion2',
            ],
            default => [],
        };
    }
}
