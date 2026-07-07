<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages;

use App\Filament\Cliente\Widgets\ClienteStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dashboard del portal de clientes.
 * Las estadísticas se renderizan vía el widget ClienteStatsWidget
 * (migrado en Tarea 7.05 — getStats() deprecado en Filament v5).
 */
class ClienteDashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            ClienteStatsWidget::class,
        ];
    }
}
