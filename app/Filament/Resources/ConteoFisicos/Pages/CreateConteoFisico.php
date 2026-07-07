<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConteoFisicos\Pages;

use App\Filament\Resources\ConteoFisicos\ConteoFisicoResource;
use App\Services\BodegaService;
use App\Services\ConteoFisicoService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateConteoFisico extends CreateRecord
{
    protected static string $resource = ConteoFisicoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(ConteoFisicoService::class);

        return $service->generarConteo(
            bodegaId: (int) ($data['bodega_id'] ?? BodegaService::bodegaDefaultId()),
            observacion: $data['observacion'] ?? null,
            esSaldoInicial: (bool) ($data['es_saldo_inicial'] ?? false),
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
