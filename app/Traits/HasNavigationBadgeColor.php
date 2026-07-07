<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait para badges de navegación en recursos Filament.
 * Proporciona getNavigationBadge() y getNavigationBadgeColor().
 */
trait HasNavigationBadgeColor
{
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::exists() ? 'success' : 'danger';
    }
}
