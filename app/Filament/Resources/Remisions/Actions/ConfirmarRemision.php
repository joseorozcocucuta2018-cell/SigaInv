<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions\Actions;

use App\Filament\Actions\Concerns\AbstractConfirmAction;
use App\Services\RemisionService;

class ConfirmarRemision extends AbstractConfirmAction
{
    protected static string $documentName = 'Remisión';

    protected static ?string $serviceClass = RemisionService::class;

    protected static string $serviceMethod = 'confirmar';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [
        'borrador',
    ];

    protected static ?string $customNotificationBody = 'La remisión se ha confirmado y los movimientos de inventario han sido generados.';
}
