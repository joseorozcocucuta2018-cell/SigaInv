<?php

declare(strict_types=1);

namespace App\Filament\Resources\Empresas\Schemas;

use App\Enums\EmpresaRegimenTributario;
use App\Enums\EmpresaTipoPersona;
use App\Models\Ciudad;
use App\Models\Departamento;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
// use Filament\Forms\Components\Textarea;
// use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Schmeits\FilamentCharacterCounter\Forms\Components\Textarea;
use Schmeits\FilamentCharacterCounter\Forms\Components\TextInput;

class EmpresaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación de la Empresa')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre_comercial')
                            ->label('Nombre Comercial')
                            ->maxLength(150),
                        TextInput::make('razon_social')
                            ->label('Razón Social')
                            ->required()
                            ->maxLength(150),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nit')
                                    ->label('NIT')
                                    ->required()
                                    ->maxLength(20),
                                TextInput::make('digito_verificacion')
                                    ->label('DV')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(9),
                            ]),
                        Grid::make(1)->schema([
                            Select::make('tipo_persona')
                                ->label('Tipo Persona')
                                ->options(EmpresaTipoPersona::class)
                                ->default(EmpresaTipoPersona::JURIDICA)
                                ->required(),
                        ]),
                    ])->columnSpanFull(),

                Section::make('Ubicación y Contacto')
                    ->columns(3)
                    ->schema([
                        TextInput::make('direccion')
                            ->label('Dirección')
                            ->required()
                            ->maxLength(150),
                        Select::make('departamento_id')
                            ->label('Departamento')
                            ->options(Departamento::pluck('nombre', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('ciudad_id', null)),
                        Select::make('ciudad_id')
                            ->label('Ciudad')
                            ->options(fn (Get $get): Collection => Ciudad::where('departamento_id', $get('departamento_id'))->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->createOptionForm([
                                \Filament\Forms\Components\TextInput::make('nombre')
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
                        TextInput::make('telefono')
                            ->label('Teléfono Fijo')
                            ->tel(),
                        TextInput::make('celular')
                            ->label('Celular / WhatsApp')
                            ->tel(),
                        TextInput::make('email')
                            ->label('Email General')
                            ->email(),
                        TextInput::make('email_documentos')
                            ->label('Email Envío Facturas')
                            ->email(),
                    ])->columnSpanFull(),

                Section::make('Información Tributaria')
                    ->columns(2)
                    ->schema([
                        Select::make('regimen_tributario')
                            ->label('Régimen Tributario')
                            ->options(EmpresaRegimenTributario::class)
                            ->default(EmpresaRegimenTributario::COMUN)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (EmpresaRegimenTributario|string|null $state, Set $set): void {
                                $value = $state instanceof EmpresaRegimenTributario ? $state->value : $state;
                                $set('responsable_iva', $value !== EmpresaRegimenTributario::SIMPLIFICADO->value);
                            }),
                        TextInput::make('actividad_ciiu')
                            ->label('Actividad CIIU')
                            ->maxLength(10),
                        Toggle::make('responsable_iva')
                            ->label('Responsable de IVA')
                            ->default(true),
                        Toggle::make('usa_seriales')
                            ->label('Usar control de seriales por producto')
                            ->helperText('Activa el manejo opcional de seriales unitarios en el inventario.')
                            ->default(false),
                        Toggle::make('una_sola_bodega')
                            ->label('Empresa con una sola bodega')
                            ->helperText('Al activar esta opción, se asumirá que la empresa opera únicamente con "BODEGA PRINCIPAL". Los datos de dirección se tomarán automáticamente de la empresa.')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),

                    ]),

                Section::make('Documentos y Logo')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo Panel (Sidebar)')
                            ->image()
                            ->disk('directo')
                            ->directory('empresa')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Se muestra en el sidebar/header del panel')
                            ->imageEditor(),

                        FileUpload::make('logo_impresion')
                            ->label('Logo Impresión (PDFs)')
                            ->image()
                            ->disk('directo')
                            ->directory('empresa')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Facturas, kardex, listados (carta/media carta)')
                            ->imageEditor(),

                        FileUpload::make('logo_pos')
                            ->label('Logo POS (Tickets)')
                            ->image()
                            ->disk('directo')
                            ->directory('empresa')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->helperText('Tickets térmicos 80mm (monocromo, compacto)')
                            ->imageEditor()
                            ->previewable(true)
                            ->openable(true),
                    ]),

                Section::make('Resolución DIAN')
                    ->columns(2)
                    ->schema([
                        TextInput::make('resolucion_dian')
                            ->label('Número de Resolución')
                            ->maxLength(30),
                        TextInput::make('actividad_ciiu')
                            ->label('Actividad CIIU')
                            ->maxLength(10),
                        TextInput::make('resolucion_desde')
                            ->label('Consecutivo Desde')
                            ->numeric(),
                        TextInput::make('resolucion_hasta')
                            ->label('Consecutivo Hasta')
                            ->numeric(),
                        DatePicker::make('resolucion_fecha_expedicion')
                            ->label('Fecha Expedición'),
                        DatePicker::make('resolucion_fecha_vencimiento')
                            ->label('Fecha Vencimiento'),
                    ]),

                Section::make('Márgenes de Ganancia')
                    ->columns(2)
                    ->schema([
                        TextInput::make('margen_ganancia_minimo')
                            ->label('Margen Mínimo (%)')
                            ->numeric()
                            ->default(25.00)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Margen mínimo permitido para productos regulares'),
                        TextInput::make('margen_ganancia_default')
                            ->label('Margen Máximo / Default (%)')
                            ->numeric()
                            ->default(30.00)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Margen por defecto y máximo. Las transformaciones no tienen límite.'),
                        Textarea::make('pie_pagina')
                            ->label('Pie de Página Documentos')
                            ->maxLength(100),
                        Textarea::make('notas_factura')
                            ->label('Notas Legales Factura')
                            ->characterLimit(50)
                            ->maxLength(100),
                    ]),
            ]);
    }
}
