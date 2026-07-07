<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaDocumento extends Model
{
    protected $table = 'auditoria_documentos';

    protected $fillable = [
        'documento_tipo',
        'documento_id',
        'usuario_id',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'estado_documento',
        'accion',
        'observacion',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que realizó el cambio
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Scope para filtrar por tipo de documento
     */
    public function scopeForDocument($query, string $tipo, int $documentoId)
    {
        return $query
            ->where('documento_tipo', $tipo)
            ->where('documento_id', $documentoId)
            ->orderByDesc('created_at');
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeForAccion($query, string $accion)
    {
        return $query->where('accion', $accion);
    }

    /**
     * Obtener el nombre legible del tipo de documento
     */
    public function getNombreDocumento(): string
    {
        return match ($this->documento_tipo) {
            'compra' => 'Compra',
            'venta' => 'Venta',
            'remision' => 'Remisión',
            default => ucfirst($this->documento_tipo),
        };
    }

    /**
     * Obtener el nombre legible de la acción
     */
    public function getNombreAccion(): string
    {
        return match ($this->accion) {
            'create' => 'Creación',
            'update' => 'Modificación',
            'delete' => 'Eliminación',
            'confirm' => 'Confirmación',
            'mark_paid' => 'Marcar como Pagado',
            'cancel' => 'Anulación',
            default => ucfirst($this->accion),
        };
    }
}
