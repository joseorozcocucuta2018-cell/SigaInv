<?php

namespace App\Filament\Resources\MovimientoBancos\Pages;

use App\Filament\Resources\MovimientoBancos\MovimientoBancoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMovimientoBancos extends ListRecords
{
    protected static string $resource = MovimientoBancoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo'),
        ];
    }
}
