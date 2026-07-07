<?php

declare(strict_types=1);

namespace App\Enums;

enum MotivoAjuste: string
{
    case CONTEO_FISICO = 'conteo_fisico';
    case MERMA = 'merma';
    case DANO = 'daño';
    case ROBO = 'robo';
    case AJUSTE_INICIAL = 'ajuste_inicial';
    case OTRO = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::CONTEO_FISICO => 'Conteo Físico',
            self::MERMA => 'Merma',
            self::DANO => 'Daño',
            self::ROBO => 'Robo',
            self::AJUSTE_INICIAL => 'Ajuste Inicial',
            self::OTRO => 'Otro',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CONTEO_FISICO => 'info',
            self::MERMA => 'warning',
            self::DANO => 'danger',
            self::ROBO => 'danger',
            self::AJUSTE_INICIAL => 'primary',
            self::OTRO => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CONTEO_FISICO => 'heroicon-o-clipboard-document-check',
            self::MERMA => 'heroicon-o-arrow-trending-down',
            self::DANO => 'heroicon-o-exclamation-triangle',
            self::ROBO => 'heroicon-o-shield-exclamation',
            self::AJUSTE_INICIAL => 'heroicon-o-document-plus',
            self::OTRO => 'heroicon-o-ellipsis-horizontal-circle',
        };
    }

    public function esSuma(): bool
    {
        return in_array($this, [self::CONTEO_FISICO, self::AJUSTE_INICIAL]);
    }
}
