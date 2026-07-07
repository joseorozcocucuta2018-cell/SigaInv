<?php

declare(strict_types=1);

namespace App\Filament\Resources\Devoluciones\Actions;

use App\Filament\Actions\Concerns\AbstractAnularAction;
use App\Services\DevolucionService;

class AnularDevolucion extends AbstractAnularAction
{
    protected static string $documentName = 'Devolución';

    protected static ?string $serviceClass = DevolucionService::class;

    protected static string $serviceMethod = 'anular';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [
        'confirmada',
    ];

    protected static bool $razonRequired = true;

    protected static ?string $customNotificationBody = 'La devolución ha sido anulada y los movimientos revertidos.';
}
