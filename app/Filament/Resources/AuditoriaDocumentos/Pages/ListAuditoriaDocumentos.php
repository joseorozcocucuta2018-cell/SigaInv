<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditoriaDocumentos\Pages;

use App\Filament\Resources\AuditoriaDocumentos\AuditoriaDocumentoResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditoriaDocumentos extends ListRecords
{
    protected static string $resource = AuditoriaDocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
