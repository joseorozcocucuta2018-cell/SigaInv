<?php

declare(strict_types=1);

namespace App\Filament\Resources\Empresas\Tables;

use App\Filament\Resources\Empresas\EmpresaResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmpresasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('directo')
                    ->circular()
                    ->size(50),
                TextColumn::make('nombre_comercial')
                    ->label('Nombre Comercial')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nit')
                    ->label('NIT')
                    ->formatStateUsing(fn ($record) => $record->nit.'-'.$record->digito_verificacion)
                    ->searchable(),
                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('ciudad.nombre')
                    ->label('Ciudad')
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('celular')
                    ->label('Celular')
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('email')
                    ->label('Email')
                    ->toggleable()
                    ->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => EmpresaResource::isSuperAdmin()),
                ViewAction::make()
                    ->visible(fn () => ! EmpresaResource::isSuperAdmin()),
            ]);
    }
}
