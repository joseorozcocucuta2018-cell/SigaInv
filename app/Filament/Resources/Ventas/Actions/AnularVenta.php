<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ventas\Actions;

use App\Filament\Actions\Concerns\AbstractAnularAction;
use App\Services\VentaService;

class AnularVenta extends AbstractAnularAction
{
    protected static string $documentName = 'Venta';

    protected static ?string $serviceClass = VentaService::class;

    protected static string $serviceMethod = 'anular';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [
        'borrador',
        'confirmada',
    ];
}
