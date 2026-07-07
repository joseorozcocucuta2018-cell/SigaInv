<?php

declare(strict_types=1);

namespace App\Enums;

enum NotaTipo: string
{
    case NOTA_CREDITO = 'nota_credito';
    case NOTA_DEBITO = 'nota_debito';

    public function label(): string
    {
        return match ($this) {
            self::NOTA_CREDITO => 'Nota Crédito',
            self::NOTA_DEBITO => 'Nota Débito',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NOTA_CREDITO => 'success',
            self::NOTA_DEBITO => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NOTA_CREDITO => 'heroicon-o-arrow-up-circle',
            self::NOTA_DEBITO => 'heroicon-o-arrow-down-circle',
        };
    }
}
