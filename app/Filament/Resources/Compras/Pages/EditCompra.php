<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Pages;

use App\Enums\CompraEstado;
use App\Filament\Resources\Compras\Actions\RegistrarCompra;
use App\Filament\Resources\Compras\Actions\RegistrarPago;
use App\Filament\Resources\Compras\CompraResource;
use App\Services\CompraService;
use App\Traits\CreatesDocumentActions;
use App\Traits\ValidatesEditableState;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompra extends EditRecord
{
    use CreatesDocumentActions, ValidatesEditableState;

    protected static string $resource = CompraResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            RegistrarCompra::make(),
            RegistrarPago::make(),
            $this->makeReversarAction(
                entity: 'compra',
                service: fn ($r) => CompraService::anular($r),
                estadoReversable: CompraEstado::ANULADA,
                requiresReason: true,
                visible: fn ($record) => $record->estado?->value === CompraEstado::REGISTRADA->value,
            ),
            DeleteAction::make()
                ->visible(fn () => $this->record->estado->isEditable()),
        ];
    }
}
