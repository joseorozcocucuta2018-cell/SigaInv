<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions\Pages;

use App\Filament\Resources\Remisions\RemisionResource;
use App\Filament\Resources\Remisions\Schemas\RemisionForm;
use App\Traits\SendsDocumentEmail;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewRemision extends ViewRecord
{
    use SendsDocumentEmail;

    protected static string $resource = RemisionResource::class;

    public function form(Schema $schema): Schema
    {
        return RemisionForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            $this->makeReenviarAction('remision', 'la remisión', [
                'cliente.ciudad', 'bodega', 'detalles.producto', 'detalles.impuesto',
                'usuario', 'cotizacion',
            ]),
        ];
    }
}
