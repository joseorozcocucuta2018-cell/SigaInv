<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions\Pages;

use App\Enums\RemisionEstado;
use App\Filament\Actions\PrintAction;
use App\Filament\Resources\Remisions\Actions\RegistrarPago;
use App\Filament\Resources\Remisions\RemisionResource;
use App\Services\RemisionService;
use App\Traits\CreatesDocumentActions;
use App\Traits\SendsDocumentEmail;
use App\Traits\ValidatesEditableState;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRemision extends EditRecord
{
    use CreatesDocumentActions, SendsDocumentEmail, ValidatesEditableState;

    protected static string $resource = RemisionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->makeConfirmarAction(
                entity: 'remision',
                service: fn ($r) => RemisionService::confirmar($r),
                estadoBorrador: RemisionEstado::BORRADOR,
                modalDescription: '¿Estás seguro de que deseas confirmar esta remisión? Se generarán los movimientos de inventario y no podrá ser editada.',
                successBody: 'La remisión se ha confirmado y los movimientos de inventario han sido generados.',
            ),
            RegistrarPago::make(),
            $this->makeReversarAction(
                entity: 'remision',
                service: fn ($r) => RemisionService::anular($r),
                estadoReversable: RemisionEstado::ANULADA,
                requiresReason: true,
            ),

            PrintAction::make('pdf.remision'),

            $this->makeEnviarCorreoAction(
                'remision',
                ['cliente.ciudad', 'bodega', 'detalles.producto', 'detalles.impuesto', 'usuario', 'cotizacion'],
            ),

            DeleteAction::make()
                ->visible(fn () => $this->record->estado->isEditable()),
        ];
    }
}
