<?php

declare(strict_types=1);

namespace App\Filament\Resources\Traslados\Pages;

use App\Filament\Resources\Traslados\TrasladoResource;
use App\Traits\FiltersEmptyRepeaterRows;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTraslado extends CreateRecord
{
    use FiltersEmptyRepeaterRows;

    protected static string $resource = TrasladoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = Auth::id();
        $this->filterEmptyRepeaterRows($data);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
