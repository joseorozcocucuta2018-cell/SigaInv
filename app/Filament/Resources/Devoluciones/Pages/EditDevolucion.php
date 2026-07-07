<?php

declare(strict_types=1);

namespace App\Filament\Resources\Devoluciones\Pages;

use App\Enums\DevolucionEstado;
use App\Filament\Resources\Devoluciones\DevolucionResource;
use App\Services\DevolucionService;
use App\Traits\CreatesDocumentActions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDevolucion extends EditRecord
{
    use CreatesDocumentActions;

    protected static string $resource = DevolucionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeConfirmarAction(
                entity: 'devolucion',
                service: fn ($r) => DevolucionService::confirmar($r),
                estadoBorrador: DevolucionEstado::BORRADOR,
                modalDescription: '¿Estás seguro de que deseas confirmar esta devolución? Se revertirán los movimientos de inventario.',
                successBody: 'La devolución se ha confirmado correctamente.',
            ),
            $this->makeReversarAction(
                entity: 'devolucion',
                service: fn ($r) => DevolucionService::anular($r),
                estadoReversable: DevolucionEstado::ANULADA,
                requiresReason: true,
            ),
            DeleteAction::make()
                ->visible(fn () => $this->record->estado?->value === DevolucionEstado::BORRADOR->value),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        if ($this->record->estado?->value !== DevolucionEstado::BORRADOR->value) {
            throw new \InvalidArgumentException(
                "No se puede editar una devolución en estado {$this->record->estado}. Solo se pueden editar borradores."
            );
        }
    }
}
