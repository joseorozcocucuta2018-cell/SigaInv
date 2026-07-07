<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormaPagos\Pages;

use App\Filament\Resources\FormaPagos\FormaPagoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFormaPagos extends ListRecords
{
    protected static string $resource = FormaPagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo'),
        ];
    }
}
