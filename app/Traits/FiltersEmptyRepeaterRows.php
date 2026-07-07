<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait para filtrar filas vacías de repeaters antes de guardar.
 *
 * Se usa en páginas Create/Edit que tienen repeaters con campos opcionales.
 * Las filas que tengan todos sus campos vacíos o nulos se eliminan antes
 * de persistir para evitar registros huérfanos o incompletos.
 */
trait FiltersEmptyRepeaterRows
{
    /**
     * Campos que se consideran "obligatorios" para que una fila sea válida.
     * Override en la clase hija si es necesario.
     */
    protected function getRequiredRepeaterFields(): array
    {
        return ['producto_id'];
    }

    /**
     * Nombre del campo repeater a filtrar. Default: 'detalles'.
     */
    protected function getRepeaterFieldName(): string
    {
        return 'detalles';
    }

    /**
     * Filtra las filas vacías del repeater antes de guardar.
     */
    protected function filterEmptyRepeaterRows(array &$data): void
    {
        $fieldName = $this->getRepeaterFieldName();
        $requiredFields = $this->getRequiredRepeaterFields();

        if (! isset($data[$fieldName]) || ! is_array($data[$fieldName])) {
            return;
        }

        $data[$fieldName] = array_values(array_filter(
            $data[$fieldName],
            function (array $row) use ($requiredFields): bool {
                foreach ($requiredFields as $field) {
                    if (empty($row[$field])) {
                        return false;
                    }
                }

                return true;
            }
        ));
    }
}
