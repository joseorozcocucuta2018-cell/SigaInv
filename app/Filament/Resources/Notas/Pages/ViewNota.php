<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notas\Pages;

use App\Filament\Resources\Notas\NotaResource;
use Filament\Resources\Pages\ViewRecord;

class ViewNota extends ViewRecord
{
    protected static string $resource = NotaResource::class;
}
