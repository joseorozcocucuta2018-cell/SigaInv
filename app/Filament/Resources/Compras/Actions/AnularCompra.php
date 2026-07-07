<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Actions;

use App\Filament\Actions\Concerns\AbstractAnularAction;
use App\Services\CompraService;

class AnularCompra extends AbstractAnularAction
{
    protected static string $documentName = 'Compra';

    protected static ?string $serviceClass = CompraService::class;

    protected static string $serviceMethod = 'anular';

    protected static string $verbo = 'Cancelar';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [
        'borrador',
        'registrada',
        'pendiente',
    ];
}
