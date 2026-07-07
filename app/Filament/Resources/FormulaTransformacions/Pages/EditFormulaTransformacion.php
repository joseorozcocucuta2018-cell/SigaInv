<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormulaTransformacions\Pages;

use App\Filament\Resources\FormulaTransformacions\FormulaTransformacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFormulaTransformacion extends EditRecord
{
    protected static string $resource = FormulaTransformacionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => ! $this->record->tiene_transformaciones && ! $this->record->bloqueada),
        ];
    }
}
