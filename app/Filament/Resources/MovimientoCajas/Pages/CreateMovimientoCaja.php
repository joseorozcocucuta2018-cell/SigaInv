<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoCajas\Pages;

use App\Enums\MovimientoCajaTipo;
use App\Filament\Resources\MovimientoCajas\MovimientoCajaResource;
use App\Models\FormaPago;
use App\Models\MovimientoCaja;
use App\Services\CajaService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMovimientoCaja extends CreateRecord
{
    protected static string $resource = MovimientoCajaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = Auth::id();
        $data['forma_pago_id'] = FormaPago::where('nombre', 'Efectivo')->value('id');

        if ($data['tipo'] instanceof MovimientoCajaTipo) {
            $data['tipo'] = $data['tipo']->value;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): MovimientoCaja
    {
        if ($data['tipo'] === 'traslado') {
            $result = CajaService::trasladarACaja(
                (int) $data['caja_id'],
                (int) $data['destino_id'],
                (float) $data['monto'],
                $data['concepto'] ?? null,
                $data['referencia'] ?? null,
            );

            return $result['origen'];
        }

        if ($data['tipo'] === 'consignacion') {
            $result = CajaService::trasladarABanco(
                (int) $data['caja_id'],
                (int) $data['destino_id'],
                (float) $data['monto'],
                $data['concepto'] ?? null,
                $data['referencia'] ?? null,
            );

            return $result['movimiento_caja'];
        }

        if ($data['tipo'] === 'ingreso') {
            return CajaService::registrarIngreso(
                (int) $data['caja_id'],
                (float) $data['monto'],
                $data['concepto'] ?? 'Ingreso manual',
                $data['categoria'] ?? 'otro_ingreso',
                $data['referencia'] ?? null,
                $data['forma_pago_id'] ?? null,
            );
        }

        return CajaService::registrarEgreso(
            (int) $data['caja_id'],
            (float) $data['monto'],
            $data['concepto'] ?? 'Egreso manual',
            $data['categoria'] ?? 'otro_egreso',
            $data['referencia'] ?? null,
            $data['forma_pago_id'] ?? null,
        );
    }
}
