<?php

declare(strict_types=1);

namespace App\Filament\Resources\AjusteInventarios\Pages;

use App\Filament\Resources\AjusteInventarios\AjusteInventarioResource;
use App\Traits\FiltersEmptyRepeaterRows;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAjusteInventario extends CreateRecord
{
    use FiltersEmptyRepeaterRows;

    protected static string $resource = AjusteInventarioResource::class;

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
