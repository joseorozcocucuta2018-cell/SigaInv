<?php

declare(strict_types=1);

namespace App\Filament\Resources\Numeracions\Schemas;

use App\Enums\NumeracionEstado;
use App\Enums\NumeracionTipoDocumento;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class NumeracionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación de la Numeración')
                    ->columns(2)
                    ->schema([
                        Select::make('tipo_documento')
                            ->label('Tipo de Documento')
                            ->options(NumeracionTipoDocumento::class)
                            ->disabled(fn ($operation) => $operation === 'edit')
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        TextInput::make('prefijo')
                            ->label('Prefijo')
                            ->maxLength(10)
                            ->placeholder('FA, CO, RE...')
                            ->helperText('Código que precede al número (ej: FA para facturas)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                if ($state && ! str_ends_with($state, '-') && ! str_ends_with($state, '.')) {
                                    $set('prefijo', $state.'-');
                                }
                            })
                            ->dehydrateStateUsing(fn (?string $state) => ($state && ! str_ends_with($state, '-') && ! str_ends_with($state, '.'))
                                ? $state.'-'
                                : $state),
                        TextInput::make('anno')
                            ->label('Año')
                            ->numeric()
                            ->default(date('Y'))
                            ->disabled(fn ($operation) => $operation === 'edit')
                            ->required(),
                        Placeholder::make('preview')
                            ->label('Vista Previa del Formato')
                            ->content(fn (Get $get): string => ($get('prefijo') ?? '???').str_pad($get('consecutivo_desde') ?? '1', 6, '0', STR_PAD_LEFT))
                            ->columnSpanFull(),
                    ]),

                Section::make('Resolución DIAN')
                    ->visible(fn (Get $get) => ! in_array($get('tipo_documento'), ['remision', 'cotizacion']))
                    ->description('Aplica para documentos electrónicos autorizados por la DIAN. Complete estos campos si tiene resolución de numeración.')
                    ->schema([
                        TextInput::make('resolucion_numero')
                            ->label('Número de Resolución')
                            ->maxLength(30)
                            ->placeholder('18760000001-30')
                            ->helperText('Número de resolución de facturación electrónica')
                            ->required(fn (Get $get) => in_array($get('tipo_documento'), ['venta', 'nota_credito', 'nota_debito', 'documento_equivalente'])),
                        DatePicker::make('resolucion_fecha_expedicion')
                            ->label('Fecha de Expedición'),
                        DatePicker::make('resolucion_fecha_vencimiento')
                            ->label('Fecha de Vencimiento')
                            ->afterOrEqual('resolucion_fecha_expedicion'),
                    ]),

                Section::make('Rango de Numeración')
                    ->schema([
                        TextInput::make('consecutivo_desde')
                            ->label('Consecutivo Desde')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->disabled(fn ($operation, $record) => $operation === 'edit' && $record && $record->consecutivo_actual > 0),
                        TextInput::make('consecutivo_hasta')
                            ->label('Consecutivo Hasta')
                            ->numeric()
                            ->default(9999999)
                            ->required()
                            ->disabled(fn ($operation, $record) => $operation === 'edit' && $record && $record->consecutivo_actual > 0),
                        TextInput::make('consecutivo_actual')
                            ->label('Consecutivo Actual')
                            ->numeric()
                            ->default(0)
                            ->disabled(fn ($operation) => $operation === 'edit')
                            ->helperText(fn ($operation, $record) => $operation === 'edit' && $record && $record->consecutivo_actual > 0
                                ? 'No editable - ya hay documentos emitidos con esta numeración'
                                : 'Se actualiza automáticamente al crear documentos'),
                    ]),

                Section::make('Estado')
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(NumeracionEstado::class)
                            ->default(NumeracionEstado::ACTIVO)
                            ->required()
                            ->helperText('Solo las numeraciones activas se usan para generar documentos'),
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('Notas adicionales sobre esta numeración...'),
                    ]),
            ]);
    }
}
