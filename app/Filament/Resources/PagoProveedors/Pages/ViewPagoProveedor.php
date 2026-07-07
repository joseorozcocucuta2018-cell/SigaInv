<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoProveedors\Pages;

use App\Filament\Resources\PagoProveedors\PagoProveedorResource;
use App\Filament\Resources\PagoProveedors\Schemas\PagoProveedorForm;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewPagoProveedor extends ViewRecord
{
    protected static string $resource = PagoProveedorResource::class;

    public function form(Schema $schema): Schema
    {
        return PagoProveedorForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
