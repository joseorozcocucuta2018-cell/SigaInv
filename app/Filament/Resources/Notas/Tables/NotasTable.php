<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notas\Tables;

use App\Enums\NotaEstado;
use App\Enums\NotaTipo;
use App\Models\Nota;
use App\Services\NotaService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class NotasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (NotaTipo $state): string => $state->label())
                    ->color(fn (NotaTipo $state): string => $state->color()),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->currency()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (NotaEstado $state): string => $state->label())
                    ->color(fn (NotaEstado $state): string => $state->color()),
            ])
            ->defaultSort('fecha', 'desc')
            ->defaultPaginationPageOption(10)
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Nota $record) => $record->estado?->value === NotaEstado::BORRADOR->value),
                Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Nota $record) => $record->estado?->value === NotaEstado::BORRADOR->value)
                    ->action(function (Nota $record, NotaService $service) {
                        try {
                            $service->confirmar($record);
                            Notification::make()->success()->title('Nota confirmada')->send();
                        } catch (\Exception $e) {
                            Notification::make()->danger()->title('Error')->body($e->getMessage())->send();
                        }
                    }),
                DeleteAction::make()
                    ->visible(fn (Nota $record) => $record->estado?->value === NotaEstado::BORRADOR->value && Auth::user()?->hasRole('administrador')),
            ]);
    }
}
