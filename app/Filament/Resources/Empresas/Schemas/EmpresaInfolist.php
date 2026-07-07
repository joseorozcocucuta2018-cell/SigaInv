<?php

declare(strict_types=1);

namespace App\Filament\Resources\Empresas\Schemas;

use App\Enums\EmpresaRegimenTributario;
use App\Enums\EmpresaTipoPersona;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmpresaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación de la Empresa')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nombre_comercial')
                            ->label('Nombre Comercial / Razón Social'),
                        TextEntry::make('nit')
                            ->label('NIT')
                            ->formatStateUsing(fn (?string $state, $record) => $state
                                ? "{$state}-{$record->digito_verificacion}"
                                : null),
                        TextEntry::make('tipo_persona')
                            ->label('Tipo Persona')
                            ->formatStateUsing(fn (?EmpresaTipoPersona $state) => $state?->label()),
                    ]),

                Section::make('Ubicación y Contacto')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('direccion')
                            ->label('Dirección'),
                        TextEntry::make('ciudad.nombre')
                            ->label('Ciudad'),
                        TextEntry::make('telefono')
                            ->label('Teléfono'),
                        TextEntry::make('celular')
                            ->label('Celular / WhatsApp'),
                        TextEntry::make('email')
                            ->label('Email'),
                        TextEntry::make('email_documentos')
                            ->label('Email Facturación'),
                    ]),

                Section::make('Información Tributaria')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('regimen_tributario')
                            ->label('Régimen Tributario')
                            ->formatStateUsing(fn (?EmpresaRegimenTributario $state) => $state?->label()),
                        TextEntry::make('actividad_ciiu')
                            ->label('Actividad CIIU'),
                        TextEntry::make('responsable_iva')
                            ->label('Responsable de IVA')
                            ->formatStateUsing(fn (bool $state) => $state ? 'Sí' : 'No'),
                        TextEntry::make('usa_seriales')
                            ->label('Control de Seriales')
                            ->formatStateUsing(fn (bool $state) => $state ? 'Sí' : 'No'),
                        TextEntry::make('una_sola_bodega')
                            ->label('Una Sola Bodega')
                            ->formatStateUsing(fn (bool $state) => $state ? 'Sí' : 'No')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
