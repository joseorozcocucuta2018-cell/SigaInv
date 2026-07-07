<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoClientes\Pages;

use App\Filament\Resources\PagoClientes\PagoClienteResource;
use App\Filament\Resources\PagoClientes\Schemas\PagoClienteForm;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewPagoCliente extends ViewRecord
{
    protected static string $resource = PagoClienteResource::class;

    public function form(Schema $schema): Schema
    {
        return PagoClienteForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
