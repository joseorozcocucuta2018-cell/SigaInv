<?php

declare(strict_types=1);

namespace App\Filament\Resources\AjusteInventarios\Pages;

use App\Enums\AjusteEstado;
use App\Filament\Resources\AjusteInventarios\AjusteInventarioResource;
use App\Filament\Resources\AjusteInventarios\Schemas\AjusteInventarioForm;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAjusteInventario extends ViewRecord
{
    protected static string $resource = AjusteInventarioResource::class;

    public function form(Schema $schema): Schema
    {
        return AjusteInventarioForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->record->estado?->value === AjusteEstado::BORRADOR->value),
        ];
    }
}
