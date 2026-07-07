<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\RemisionPortals\Pages;

use App\Enums\RemisionEstado;
use App\Filament\Resources\PortalClientes\RemisionPortals\RemisionPortalResource;
use App\Models\Remision;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListRemisionPortals extends ListRecords
{
    protected static string $resource = RemisionPortalResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableQuery(): Builder
    {
        $clienteId = Auth::guard('cliente')->id();

        if (! $clienteId) {
            return Remision::whereRaw('1 = 0');
        }

        return Remision::where('cliente_id', $clienteId)
            ->whereIn('estado', [RemisionEstado::CONFIRMADA->value, RemisionEstado::FACTURADA->value, RemisionEstado::ANULADA->value]);
    }
}
