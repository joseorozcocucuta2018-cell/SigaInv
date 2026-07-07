<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConteoFisicos\Pages;

use App\Enums\ConteoFisicoEstado;
use App\Filament\Resources\ConteoFisicos\ConteoFisicoResource;
use App\Services\ConteoFisicoService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;

class ViewConteoFisico extends ViewRecord
{
    protected static string $resource = ConteoFisicoResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información del Conteo')
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    TextEntry::make('numero')
                        ->label('Número')
                        ->weight(FontWeight::Bold),
                    TextEntry::make('bodega.nombre')
                        ->label('Bodega'),
                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn ($state) => $state->label())
                        ->color(fn ($state) => $state->color()),

                    TextEntry::make('fecha_inicio')
                        ->label('Fecha Inicio')
                        ->date('d/m/Y'),
                    TextEntry::make('fecha_cierre')
                        ->label('Fecha Cierre')
                        ->date('d/m/Y')
                        ->placeholder('Aún abierto'),
                    TextEntry::make('usuario.name')
                        ->label('Creado por'),

                    TextEntry::make('observacion')
                        ->label('Observación')
                        ->placeholder('Sin observaciones')
                        ->columnSpanFull(),
                ]),

            Section::make('Productos Contados')
                ->columnSpanFull()
                ->schema([
                    RepeatableEntry::make('detalles')
                        ->label('')
                        ->columns(4)
                        ->contained(false)
                        ->schema([
                            TextEntry::make('producto.nombre')
                                ->label('Producto'),
                            TextEntry::make('stock_sistema')
                                ->label('Stock Sistema')
                                ->numeric(3),
                            TextEntry::make('cantidad_contada')
                                ->label('Cantidad Contada')
                                ->numeric(3)
                                ->placeholder('No contado'),
                            TextEntry::make('diferencia')
                                ->label('Diferencia')
                                ->numeric(3)
                                ->badge()
                                ->color(fn ($state): string => match (true) {
                                    $state !== null && (float) $state > 0 => 'success',
                                    $state !== null && (float) $state < 0 => 'danger',
                                    default => 'gray',
                                }),
                        ]),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->record->estado?->value === ConteoFisicoEstado::ABIERTO->value),

            Action::make('imprimir_guia')
                ->label('Imprimir guía')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('pdf.conteo.guia', $this->record))
                ->openUrlInNewTab(),

            Action::make('ver_diferencias')
                ->label('Ver diferencias')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->visible(fn () => $this->record->estado?->value !== ConteoFisicoEstado::ABIERTO->value)
                ->url(fn () => route('pdf.conteo.diferencias', $this->record))
                ->openUrlInNewTab(),

            Action::make('generar_ajuste')
                ->label('Generar Ajuste')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generar Ajuste de Inventario')
                ->modalDescription('Se creará un ajuste de inventario en borrador con las diferencias encontradas.')
                ->modalSubmitActionLabel('Sí, Generar Ajuste')
                ->visible(fn () => $this->record->estado?->value === ConteoFisicoEstado::CERRADO->value
                    && (Auth::user()?->can('conteo_fisico.generar_ajuste') ?? false))
                ->action(function () {
                    $ajuste = app(ConteoFisicoService::class)->generarAjuste($this->record);
                    Notification::make()->title('Ajuste generado')->success()->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('volver')
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => $this->getResource()::getUrl('index')),
        ];
    }
}
