<?php

declare(strict_types=1);

namespace App\Enums;

enum AuditoriaOperacion: string
{
    case INSERT = 'INSERT';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';

    public function label(): string
    {
        return match ($this) {
            self::INSERT => 'Inserción',
            self::UPDATE => 'Actualización',
            self::DELETE => 'Eliminación',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INSERT => 'success',
            self::UPDATE => 'warning',
            self::DELETE => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::INSERT => 'heroicon-m-plus-circle',
            self::UPDATE => 'heroicon-m-pencil-square',
            self::DELETE => 'heroicon-m-trash',
        };
    }
}
