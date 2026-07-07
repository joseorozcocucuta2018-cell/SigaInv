<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoBancos\Pages;

use App\Enums\MovimientoBancoTipo;
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

        if ($data['tipo'] instanceof MovimientoBancoTipo) {
            $data['tipo'] = $data['tipo']->value;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): MovimientoBanco
    {
        if ($data['tipo'] === 'transferencia') {
            $result = BancosService::registrarTransferenciaSaliente(
                (int) $data['banco_id'],
                (int) $data['destino_id'],
                (float) $data['monto'],
                $data['concepto'] ?? null,
                $data['referencia'] ?? null,
            );

            return $result['origen'];
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

        if ($data['tipo'] === 'retiro' && ! empty($data['destino_id'])) {
            $result = BancosService::trasladarACaja(
                (int) $data['banco_id'],
                (int) $data['destino_id'],
                (float) $data['monto'],
                $data['concepto'] ?? null,
                $data['referencia'] ?? null,
            );

            return $result['movimiento_banco'];
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
