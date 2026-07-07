<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait para validar que un documento sea editable antes de guardar.
 *
 * Por defecto usa el método isEditable() del enum estado.
 * Se puede sobrescribir con validateEditableState() en la página.
 */
trait ValidatesEditableState
{
    protected function beforeSave(): void
    {
        $estado = $this->record->estado;

        if (is_object($estado) && method_exists($estado, 'isEditable')) {
            if (! $estado->isEditable()) {
                $entity = $this->getEntityLabel();
                throw new \InvalidArgumentException(
                    "No se puede editar {$entity} en estado {$estado->label()}. "
                    .'Solo se pueden editar en estado Borrador.'
                );
            }
        }
    }

    protected function getEntityLabel(): string
    {
        return method_exists($this, 'getResource')
            ? strtolower($this->getResource()::getModelLabel())
            : 'este registro';
    }
}
