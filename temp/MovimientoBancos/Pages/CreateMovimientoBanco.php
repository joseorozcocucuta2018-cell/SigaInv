<?php

namespace App\Filament\Resources\MovimientoBancos\Pages;

use App\Filament\Resources\MovimientoBancos\MovimientoBancoResource;
use App\Models\MovimientoBanco;
use App\Services\BancosService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMovimientoBanco extends CreateRecord
{
    protected static string $resource = MovimientoBancoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = Auth::id();

        return $data;
    }

    protected function handleRecordCreation(array $data): MovimientoBanco
    {
        if ($data['tipo'] === 'transferencia') {
            $destinoTipo = $data['traslado_destino_tipo'];
            $destinoId = (int) $data['traslado_destino_id'];

            $result = $destinoTipo === 'caja'
                ? BancosService::trasladarACaja(
                    (int) $data['banco_id'],
                    $destinoId,
                    (float) $data['monto'],
                    $data['concepto'] ?? null,
                    $data['referencia'] ?? null,
                )
                : BancosService::registrarTransferenciaSaliente(
                    (int) $data['banco_id'],
                    $destinoId,
                    (float) $data['monto'],
                    $data['concepto'] ?? null,
                    $data['referencia'] ?? null,
                );

            return $result['movimiento_banco'] ?? $result['origen'];
        }

        if ($data['tipo'] === 'deposito') {
            return BancosService::registrarDeposito(
                (int) $data['banco_id'],
                (float) $data['monto'],
                $data['concepto'] ?? 'Depósito manual',
                $data['categoria'] ?? 'consignacion',
                $data['referencia'] ?? null,
                $data['forma_pago_id'] ?? null,
            );
        }

        return BancosService::registrarRetiro(
            (int) $data['banco_id'],
            (float) $data['monto'],
            $data['concepto'] ?? 'Retiro manual',
            $data['categoria'] ?? 'retiro',
            $data['referencia'] ?? null,
            $data['forma_pago_id'] ?? null,
        );
    }
}
