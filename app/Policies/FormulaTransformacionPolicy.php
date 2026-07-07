<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FormulaTransformacion;
use App\Models\User;

class FormulaTransformacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('formula_transformacion.ver');
    }

    public function view(User $user, FormulaTransformacion $formulaTransformacion): bool
    {
        return $user->can('formula_transformacion.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('formula_transformacion.crear');
    }

    public function update(User $user, FormulaTransformacion $formulaTransformacion): bool
    {
        if ($formulaTransformacion->tiene_transformaciones || $formulaTransformacion->bloqueada) {
            return false;
        }

        return $user->can('formula_transformacion.editar');
    }

    public function delete(User $user, FormulaTransformacion $formulaTransformacion): bool
    {
        if ($formulaTransformacion->tiene_transformaciones || $formulaTransformacion->bloqueada) {
            return false;
        }

        return $user->can('formula_transformacion.eliminar');
    }

    public function restore(User $user, FormulaTransformacion $formulaTransformacion): bool
    {
        return $user->can('formula_transformacion.eliminar');
    }

    public function forceDelete(User $user, FormulaTransformacion $formulaTransformacion): bool
    {
        return false;
    }
}
