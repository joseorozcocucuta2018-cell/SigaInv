<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Actions;

use App\Filament\Actions\Concerns\AbstractConfirmAction;
use App\Services\CompraService;

class RegistrarCompra extends AbstractConfirmAction
{
    protected static string $documentName = 'Compra';

    protected static ?string $serviceClass = CompraService::class;

    protected static string $serviceMethod = 'registrar';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [
        'borrador',
    ];

    protected static ?string $customNotificationBody = 'La compra se ha registrado y los movimientos de inventario han sido generados.';
}
