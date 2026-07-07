<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cotizacions\Pages;

use App\Filament\Resources\Cotizacions\CotizacionResource;
use App\Filament\Resources\Cotizacions\Schemas\CotizacionForm;
use App\Traits\SendsDocumentEmail;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewCotizacion extends ViewRecord
{
    use SendsDocumentEmail;

    protected static string $resource = CotizacionResource::class;

    public function form(Schema $schema): Schema
    {
        return CotizacionForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            $this->makeReenviarAction('cotizacion', 'la cotización', [
                'cliente.ciudad', 'bodega', 'detalles.producto', 'detalles.impuesto', 'usuario',
            ]),
        ];
    }
}
