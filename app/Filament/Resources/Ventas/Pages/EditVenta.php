<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ventas\Pages;

use App\Enums\VentaEstado;
use App\Filament\Actions\PrintAction;
use App\Filament\Resources\Ventas\Actions\MarcarPagada;
use App\Filament\Resources\Ventas\VentaResource;
use App\Services\VentaService;
use App\Traits\CreatesDocumentActions;
use App\Traits\SendsDocumentEmail;
use App\Traits\ValidatesEditableState;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVenta extends EditRecord
{
    use CreatesDocumentActions, SendsDocumentEmail, ValidatesEditableState;

    protected static string $resource = VentaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->makeConfirmarAction(
                entity: 'venta',
                service: fn ($r) => VentaService::confirmar($r),
                estadoBorrador: VentaEstado::BORRADOR,
                modalDescription: '¿Estás seguro de que deseas confirmar esta venta? Se generarán los movimientos de inventario y no podrá ser editada.',
                successBody: 'La venta se ha confirmado y los movimientos de inventario han sido generados.',
            ),
            MarcarPagada::make(),
            $this->makeReversarAction(
                entity: 'venta',
                service: fn ($r) => VentaService::anular($r),
                estadoReversable: VentaEstado::ANULADA,
                requiresReason: true,
            ),

            PrintAction::make('pdf.venta'),

            $this->makeEnviarCorreoAction(
                'venta',
                ['cliente.ciudad', 'bodega', 'detalles.producto', 'detalles.impuesto', 'usuario', 'cotizacion', 'remision'],
            ),

            DeleteAction::make()
                ->visible(fn () => $this->record->estado->isEditable()),
        ];
    }
}
