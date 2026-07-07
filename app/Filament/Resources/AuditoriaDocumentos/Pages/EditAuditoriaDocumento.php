<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditoriaDocumentos\Pages;

use App\Filament\Resources\AuditoriaDocumentos\AuditoriaDocumentoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAuditoriaDocumento extends EditRecord
{
    protected static string $resource = AuditoriaDocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
