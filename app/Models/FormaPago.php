<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesAttributes;
use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    use SanitizesAttributes;

    protected $table = 'formas_pago';

    protected $fillable = [
        'nombre',
        'requiere_banco',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'requiere_banco' => 'boolean',
    ];
}
