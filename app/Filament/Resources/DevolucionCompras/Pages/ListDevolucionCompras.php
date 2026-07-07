<?php

declare(strict_types=1);

namespace App\Filament\Resources\DevolucionCompras\Pages;

use App\Filament\Resources\DevolucionCompras\DevolucionCompraResource;
use Filament\Resources\Pages\ListRecords;

class ListDevolucionCompras extends ListRecords
{
    protected static string $resource = DevolucionCompraResource::class;
}
