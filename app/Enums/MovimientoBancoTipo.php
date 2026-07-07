<?php

declare(strict_types=1);

namespace App\Enums;

enum MovimientoBancoTipo: string
{
    case DEPOSITO = 'deposito';
    case RETIRO = 'retiro';
    case TRANSFERENCIA = 'transferencia';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSITO => 'Depósito / Entrada',
            self::RETIRO => 'Retiro / Salida',
            self::TRANSFERENCIA => 'Transferencia',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DEPOSITO => 'success',
            self::RETIRO => 'danger',
            self::TRANSFERENCIA => 'info',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DEPOSITO => 'heroicon-o-arrow-trending-up',
            self::RETIRO => 'heroicon-o-arrow-trending-down',
            self::TRANSFERENCIA => 'heroicon-o-arrows-right-left',
        };
    }
}
