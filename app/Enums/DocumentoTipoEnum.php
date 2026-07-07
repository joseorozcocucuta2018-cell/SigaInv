<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentoTipoEnum: string
{
    case CC = 'CC';
    case NIT = 'NIT';
    case CE = 'CE';
    case PP = 'PP';

    public function label(): string
    {
        return match ($this) {
            self::CC => 'Cédula de Ciudadanía',
            self::NIT => 'NIT',
            self::CE => 'Cédula de Extranjería',
            self::PP => 'Pasaporte',
        };
    }

    public function shortLabel(): string
    {
        return $this->value;
    }
}
