<?php

declare(strict_types=1);

namespace App\Filament\Resources\Devoluciones\Pages;

use App\Filament\Resources\Devoluciones\DevolucionResource;
use App\Filament\Resources\Devoluciones\Schemas\DevolucionForm;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewDevolucion extends ViewRecord
{
    protected static string $resource = DevolucionResource::class;

    public function form(Schema $schema): Schema
    {
        return DevolucionForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
