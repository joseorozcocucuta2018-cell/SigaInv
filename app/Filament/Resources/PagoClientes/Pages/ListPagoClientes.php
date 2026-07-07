<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoClientes\Pages;

use App\Filament\Resources\PagoClientes\PagoClienteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPagoClientes extends ListRecords
{
    protected static string $resource = PagoClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo'),
        ];
    }
}
