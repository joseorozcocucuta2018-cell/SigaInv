<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions\Actions;

use App\Filament\Actions\Concerns\AbstractAnularAction;
use App\Services\RemisionService;

class AnularRemision extends AbstractAnularAction
{
    protected static string $documentName = 'Remisión';

    protected static ?string $serviceClass = RemisionService::class;

    protected static string $serviceMethod = 'anular';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [
        'borrador',
        'confirmada',
    ];
}
