<?php

declare(strict_types=1);

namespace App\Filament\Resources\Devoluciones\Actions;

use App\Filament\Actions\Concerns\AbstractConfirmAction;
use App\Services\DevolucionService;

class ConfirmarDevolucion extends AbstractConfirmAction
{
    protected static string $documentName = 'Devolución';

    protected static ?string $serviceClass = DevolucionService::class;

    protected static string $serviceMethod = 'confirmar';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [
        'borrador',
    ];

    protected static ?string $customNotificationBody = 'El stock ha sido restaurado y el saldo del cliente actualizado.';
}
