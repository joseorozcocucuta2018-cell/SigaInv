<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\FacturaPortals\Pages;

use App\Enums\VentaEstado;
use App\Filament\Resources\PortalClientes\FacturaPortals\FacturaPortalResource;
use App\Models\Venta;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListFacturaPortals extends ListRecords
{
    protected static string $resource = FacturaPortalResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableQuery(): Builder
    {
        $clienteId = Auth::guard('cliente')->id();

        if (! $clienteId) {
            return Venta::whereRaw('1 = 0');
        }

        return Venta::where('cliente_id', $clienteId)
            ->whereIn('estado', [VentaEstado::CONFIRMADA->value, VentaEstado::PAGADA->value, VentaEstado::ANULADA->value]);
    }
}
