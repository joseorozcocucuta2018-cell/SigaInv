<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ventas\Actions;

use App\Filament\Actions\Concerns\AbstractConfirmAction;
use App\Services\VentaService;

class ConfirmarVenta extends AbstractConfirmAction
{
    protected static string $documentName = 'Venta';

    protected static ?string $serviceClass = VentaService::class;

    protected static string $serviceMethod = 'confirmar';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [
        'borrador',
    ];

    protected static ?string $customNotificationBody = 'La venta se ha confirmado y los movimientos de inventario han sido generados.';
}
