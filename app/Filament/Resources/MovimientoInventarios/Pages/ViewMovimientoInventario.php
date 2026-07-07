<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoInventarios\Pages;

use App\Filament\Resources\MovimientoInventarios\MovimientoInventarioResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewMovimientoInventario extends ViewRecord
{
    protected static string $resource = MovimientoInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => $this->getResource()::getUrl('index')),
        ];
    }
}
