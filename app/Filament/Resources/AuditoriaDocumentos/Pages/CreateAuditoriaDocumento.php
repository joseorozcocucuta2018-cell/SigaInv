<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditoriaDocumentos\Pages;

use App\Filament\Resources\AuditoriaDocumentos\AuditoriaDocumentoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAuditoriaDocumento extends CreateRecord
{
    protected static string $resource = AuditoriaDocumentoResource::class;
}
