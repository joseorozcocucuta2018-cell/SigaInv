<?php

namespace App\Filament\Resources\MovimientoCajas\Pages;

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

        return $data;
    }

    protected function handleRecordCreation(array $data): MovimientoCaja
    {
        if ($data['tipo'] === 'traslado') {
            $destinoTipo = $data['traslado_destino_tipo'];
            $destinoId = (int) $data['traslado_destino_id'];

            $result = $destinoTipo === 'caja'
                ? CajaService::trasladarACaja(
                    (int) $data['caja_id'],
                    $destinoId,
                    (float) $data['monto'],
                    $data['concepto'] ?? null,
                    $data['referencia'] ?? null,
                )
                : CajaService::trasladarABanco(
                    (int) $data['caja_id'],
                    $destinoId,
                    (float) $data['monto'],
                    $data['concepto'] ?? null,
                    $data['referencia'] ?? null,
                );

            return $result['origen'] ?? $result['movimiento_caja'];
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
