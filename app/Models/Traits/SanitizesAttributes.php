<?php

declare(strict_types=1);

namespace App\Models\Traits;

trait SanitizesAttributes
{
    public static function bootSanitizesAttributes(): void
    {
        static::saving(function (self $model): void {
            foreach ($model->getAttributes() as $key => $value) {
                if (! is_string($value)) {
                    continue;
                }

                $clean = trim($value);

                if ($key === 'nombre' || $key === 'nombre_comun') {
                    $clean = mb_strtoupper($clean);
                }

                if ($clean !== $value) {
                    $model->$key = $clean;
                }
            }
        });
    }
}
