<?php

declare(strict_types=1);

namespace App\Enums;

enum ConteoFisicoEstado: string
{
    case ABIERTO = 'abierto';
    case CERRADO = 'cerrado';
    case AJUSTADO = 'ajustado';

    public function label(): string
    {
        return match ($this) {
            self::ABIERTO => 'Abierto',
            self::CERRADO => 'Cerrado',
            self::AJUSTADO => 'Ajustado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ABIERTO => 'warning',
            self::CERRADO => 'info',
            self::AJUSTADO => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ABIERTO => 'heroicon-m-clipboard-document-list',
            self::CERRADO => 'heroicon-m-lock-closed',
            self::AJUSTADO => 'heroicon-m-check-badge',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::ABIERTO;
    }
}
