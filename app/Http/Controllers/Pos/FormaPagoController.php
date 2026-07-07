<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\FormaPago;
use Illuminate\Http\JsonResponse;

class FormaPagoController extends Controller
{
    public function index(): JsonResponse
    {
        $formas = FormaPago::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'requiere_banco', 'requiere_referencia'])
            ->map(fn ($f) => [
                'id' => (int) $f->id,
                'nombre' => (string) $f->nombre,
                'requiere_banco' => (bool) $f->requiere_banco,
                'requiere_referencia' => (bool) $f->requiere_referencia,
            ]);

        return response()->json(['data' => $formas]);
    }
}
