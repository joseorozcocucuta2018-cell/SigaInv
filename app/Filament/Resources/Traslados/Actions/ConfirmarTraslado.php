<?php

declare(strict_types=1);

namespace App\Filament\Resources\Traslados\Actions;

use App\Filament\Actions\Concerns\AbstractConfirmAction;
use App\Services\TrasladoService;

class ConfirmarTraslado extends AbstractConfirmAction
{
    protected static string $documentName = 'Traslado';

    protected static ?string $serviceClass = TrasladoService::class;

    protected static string $serviceMethod = 'confirmar';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [
        'borrador',
    ];

    protected static ?string $customNotificationBody = 'El traslado se ha confirmado y los movimientos de inventario han sido generados.';
}
