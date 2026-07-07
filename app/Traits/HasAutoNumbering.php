<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait para auto-numeración secuencial de documentos.
 *
 * Configurar en el model que lo use:
 *   protected string $autoNumberPrefix = 'AJU-';
 *   protected int $autoNumberOffset = 4;       // Para substr(PREFIX-NNNNN, offset)
 *   // O usar explode con el último segmento:
 *   protected bool $autoNumberUseLastSegment = true;
 */
trait HasAutoNumbering
{
    protected static function bootHasAutoNumbering(): void
    {
        static::creating(function ($model): void {
            $prefix = $model->autoNumberPrefix ?? '';
            $padLength = $model->autoNumberPadLength ?? 5;

            if (! empty($model->numero)) {
                return;
            }

            $last = static::orderBy('id', 'desc')->lockForUpdate()->first();
            $nextNumber = 1;

            if ($last && $last->numero) {
                if (! empty($model->autoNumberUseLastSegment)) {
                    $parts = explode('-', $last->numero);
                    if (count($parts) > 1) {
                        $nextNumber = (int) end($parts) + 1;
                    }
                } else {
                    $offset = $model->autoNumberOffset ?? strlen($prefix);
                    $nextNumber = ((int) substr($last->numero, $offset)) + 1;
                }
            }

            $model->numero = $prefix.str_pad((string) $nextNumber, $padLength, '0', STR_PAD_LEFT);
        });
    }
}
