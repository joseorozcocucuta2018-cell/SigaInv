<?php

declare(strict_types=1);

namespace App\Enums;

enum EmpresaTipoPersona: string
{
    case NATURAL = 'natural';
    case JURIDICA = 'juridica';

    public function label(): string
    {
        return match ($this) {
            self::NATURAL => 'Natural',
            self::JURIDICA => 'Jurídica',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NATURAL => 'info',
            self::JURIDICA => 'primary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NATURAL => 'heroicon-m-user',
            self::JURIDICA => 'heroicon-m-building-office',
        };
    }
}
