<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoProveedors\Pages;

use App\Filament\Resources\PagoProveedors\PagoProveedorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPagoProveedors extends ListRecords
{
    protected static string $resource = PagoProveedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo'),
        ];
    }
}
