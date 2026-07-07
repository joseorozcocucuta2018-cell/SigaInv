<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormulaTransformacions\Pages;

use App\Filament\Resources\FormulaTransformacions\FormulaTransformacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormulaTransformacions extends ListRecords
{
    protected static string $resource = FormulaTransformacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo'),
        ];
    }
}
