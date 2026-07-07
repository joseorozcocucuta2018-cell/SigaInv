<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;
    use SanitizesAttributes;

    protected $table = 'unidades_medida';

    protected $fillable = [
        'nombre',
        'simbolo',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
