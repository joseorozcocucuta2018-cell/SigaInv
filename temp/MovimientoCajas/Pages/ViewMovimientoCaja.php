<?php

namespace App\Filament\Resources\MovimientoCajas\Pages;

use App\Filament\Resources\MovimientoCajas\MovimientoCajaResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewMovimientoCaja extends ViewRecord
{
    protected static string $resource = MovimientoCajaResource::class;

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
