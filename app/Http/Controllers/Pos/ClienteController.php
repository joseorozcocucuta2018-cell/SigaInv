<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\PosCrearClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use App\Services\ClienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = (int) $request->query('limit', 30);
        $limit = max(1, min($limit, 100));

        $query = Cliente::query();
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                    ->orWhere('documento', 'like', "%{$q}%");
            });
        }

        $clientes = $query->orderBy('nombre')->limit($limit)->get();

        return response()->json([
            'data' => ClienteResource::collection($clientes)->resolve(),
        ]);
    }

    public function show(Cliente $cliente): JsonResponse
    {
        return response()->json([
            'data' => (new ClienteResource($cliente))->resolve(),
        ]);
    }

    public function store(PosCrearClienteRequest $request): JsonResponse
    {
        $cliente = ClienteService::crearRapido([
            'nombre' => (string) $request->string('nombre'),
            'tipo_documento' => (string) ($request->input('tipo_documento') ?? 'CC'),
            'documento' => (string) $request->string('documento'),
            'telefono' => (string) ($request->input('telefono') ?? '0000000'),
            'correo' => (string) ($request->input('email') ?? $request->input('correo') ?? 'sin-correo@local.test'),
        ]);

        return response()->json([
            'data' => (new ClienteResource($cliente))->resolve(),
        ], 201);
    }
}
