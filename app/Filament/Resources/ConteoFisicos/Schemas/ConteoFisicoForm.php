<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConteoFisicos\Schemas;

use App\Enums\BodegaEstado;
use App\Models\ConteoFisico;
use App\Services\BodegaService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConteoFisicoForm
{
    public static function configure(Schema $schema): Schema
    {
        $bodegaDefaultId = BodegaService::bodegaDefaultId();
        $bodegaDeshabilitada = BodegaService::bodegaDeshabilitada();

        return $schema
            ->components([
                Section::make('Información del Conteo')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('bodega_id')
                            ->label('Bodega')
                            ->relationship('bodega', 'nombre', fn ($query) => $query->where('estado', BodegaEstado::ACTIVO))
                            ->required()
                            ->default(fn () => $bodegaDefaultId)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled($bodegaDeshabilitada || fn ($operation) => $operation === 'edit')
                            ->dehydrated(true),

                        DatePicker::make('fecha_inicio')
                            ->label('Fecha Inicio')
                            ->required()
                            ->default(now()),

                        Toggle::make('es_saldo_inicial')
                            ->label('¿Es Saldo Inicial?')
                            ->helperText('Habilita la creación de productos durante el conteo. Solo se permite uno por bodega.')
                            ->default(false)
                            ->live()
                            ->disabled(fn ($operation) => $operation === 'edit')
                            ->visible(fn ($operation, $get) => $operation === 'edit' || ! $get('bodega_id') || ! ConteoFisico::where('bodega_id', $get('bodega_id'))->where('es_saldo_inicial', true)->exists()),

                        Placeholder::make('saldo_inicial_existente')
                            ->label('')
                            ->content('Ya existe un saldo inicial para esta bodega. No se permite crear otro.')
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('bodega_id') && ConteoFisico::where('bodega_id', $get('bodega_id'))->where('es_saldo_inicial', true)->exists()),

                        Textarea::make('observacion')
                            ->label('Observación')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->disabled(fn ($record) => $record?->estado && ! $record->estado->isEditable()),
            ]);
    }
}
