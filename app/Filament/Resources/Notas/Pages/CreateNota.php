<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notas\Pages;

use App\Filament\Resources\Notas\NotaResource;
use App\Traits\FiltersEmptyRepeaterRows;
use Filament\Resources\Pages\CreateRecord;

class CreateNota extends CreateRecord
{
    use FiltersEmptyRepeaterRows;

    protected static string $resource = NotaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = auth()->id();
        $this->filterEmptyRepeaterRows($data);

        return $data;
    }
}
