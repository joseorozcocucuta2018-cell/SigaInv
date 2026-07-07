<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoBancos\Pages;

use App\Filament\Resources\MovimientoBancos\MovimientoBancoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewMovimientoBanco extends ViewRecord
{
    protected static string $resource = MovimientoBancoResource::class;

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
