<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;
    use SanitizesAttributes;

    protected $table = 'categorias';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function hijas(): HasMany
    {
        return $this->hasMany(Categoria::class, 'categoria_id');
    }
}
