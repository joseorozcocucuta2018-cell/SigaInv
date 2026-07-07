<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ventas\Pages;

use App\Filament\Resources\Ventas\Schemas\VentaForm;
use App\Filament\Resources\Ventas\VentaResource;
use App\Traits\SendsDocumentEmail;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewVenta extends ViewRecord
{
    use SendsDocumentEmail;

    protected static string $resource = VentaResource::class;

    public function form(Schema $schema): Schema
    {
        return VentaForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            $this->makeReenviarAction('venta', 'la venta', [
                'cliente.ciudad', 'bodega', 'detalles.producto', 'detalles.impuesto',
                'usuario', 'cotizacion', 'remision',
            ]),
        ];
    }
}
