<?php

declare(strict_types=1);

namespace App\Filament\Resources\Traslados\Actions;

use App\Filament\Actions\Concerns\AbstractAnularAction;
use App\Services\TrasladoService;

class AnularTraslado extends AbstractAnularAction
{
    protected static string $documentName = 'Traslado';

    protected static ?string $serviceClass = TrasladoService::class;

    protected static string $serviceMethod = 'anular';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [
        'borrador',
    ];
}
