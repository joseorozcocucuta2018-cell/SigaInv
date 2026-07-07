<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Actions\Action;

/**
 * Factory para botones "Imprimir" en tablas y páginas de Filament.
 * Centraliza la creación del Action que abre el PDF en nueva pestaña.
 */
final class PrintAction
{
    public static function make(string $routeName): Action
    {
        return Action::make('imprimir')
            ->label('Imprimir')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(fn ($record) => route($routeName, $record))
            ->openUrlInNewTab();
    }
}
