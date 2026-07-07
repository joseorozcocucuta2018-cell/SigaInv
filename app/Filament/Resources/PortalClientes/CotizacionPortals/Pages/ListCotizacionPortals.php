<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\CotizacionPortals\Pages;

use App\Enums\CotizacionEstado;
use App\Filament\Resources\PortalClientes\CotizacionPortals\CotizacionPortalResource;
use App\Models\Cotizacion;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListCotizacionPortals extends ListRecords
{
    protected static string $resource = CotizacionPortalResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableQuery(): Builder
    {
        $clienteId = Auth::guard('cliente')->id();

        if (! $clienteId) {
            return Cotizacion::whereRaw('1 = 0');
        }

        return Cotizacion::where('cliente_id', $clienteId)
            ->whereIn('estado', [
                CotizacionEstado::PENDIENTE->value,
                CotizacionEstado::ACEPTADA->value,
                CotizacionEstado::RECHAZADA->value,
                CotizacionEstado::VENCIDA->value,
            ]);
    }
}
