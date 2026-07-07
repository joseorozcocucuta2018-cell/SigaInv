<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormulaTransformacions\Pages;

use App\Filament\Resources\FormulaTransformacions\FormulaTransformacionResource;
use App\Filament\Resources\FormulaTransformacions\Schemas\FormulaTransformacionForm;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewFormulaTransformacion extends ViewRecord
{
    protected static string $resource = FormulaTransformacionResource::class;

    public function form(Schema $schema): Schema
    {
        return FormulaTransformacionForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => ! $this->record->tiene_transformaciones && ! $this->record->bloqueada),
        ];
    }
}
