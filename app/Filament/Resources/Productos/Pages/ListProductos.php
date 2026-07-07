<?php

declare(strict_types=1);

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Resources\Productos\ProductoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\Response;

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Nuevo Producto')
                ->icon('heroicon-o-plus')
                ->url(ProductoResource::getUrl('create')),
        ];
    }

    public function descargarPlantilla(): Response
    {
        // Eliminado: movido a Conteo Físico para evitar repeticiones y centralizar la lógica de inventario
        abort(404);
    }

    public function importarExcel(array $data): void
    {
        // Eliminado: movido a Conteo Físico
    }
}
