<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bodegas\Pages;

use App\Enums\BodegaEstado;
use App\Filament\Resources\Bodegas\BodegaResource;
use App\Models\Empresa;
use App\Services\BodegaService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBodega extends CreateRecord
{
    protected static string $resource = BodegaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        if (BodegaService::usaUnaSolaBodega()) {
            $empresa = Empresa::actual();

            $data['nombre'] = 'BODEGA PRINCIPAL';
            $data['descripcion'] = 'Bodega principal creada automáticamente';
            $data['direccion1'] = $empresa->direccion;
            $data['direccion2'] = null;
            $data['departamento_id'] = $empresa->departamento_id;
            $data['ciudad_id'] = $empresa->ciudad_id;
            $data['es_principal'] = true;
            $data['estado'] = BodegaEstado::ACTIVO;
        }

        return parent::handleRecordCreation($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
