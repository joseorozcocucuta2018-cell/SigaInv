<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ClienteEstado;
use App\Enums\PortalAccesoEnum;
use App\Models\Cliente;
use App\Traits\RegistraAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Servicio para operaciones de Cliente usadas desde el POS.
 *
 * Complementa el flujo de Filament (ClienteResource) con un endpoint
 * simplificado para el Punto de Venta que solo requiere 5 campos.
 */
class ClienteService
{
    use RegistraAuditoria;

    /**
     * Crea un cliente rápido para uso en el POS.
     *
     * 5 campos requeridos: nombre, tipo_documento, documento, telefono, correo.
     * Defaults: estado=activo, portal_acceso=sin_acceso, ciudad=889 (Cúcuta),
     * departamento=54 (Norte de Santander).
     *
     * @param  array{
     *     nombre:string,
     *     tipo_documento:string,
     *     documento:string,
     *     telefono:string,
     *     correo:string,
     *     direccion1?:string|null,
     *     porcentaje_descuento?:float|int|string|null,
     *     dias_credito?:int|null,
     *     limite_credito?:float|int|string|null,
     * }  $data
     *
     * @throws ValidationException Si el documento ya existe
     */
    public static function crearRapido(array $data): Cliente
    {
        $data['nombre'] = mb_convert_case(trim((string) ($data['nombre'] ?? '')), MB_CASE_TITLE, 'UTF-8');
        $data['documento'] = trim((string) ($data['documento'] ?? ''));

        Validator::make($data, [
            'nombre' => 'required|string|max:200',
            'tipo_documento' => 'required|string|max:10',
            'documento' => 'required|string|max:50',
            'telefono' => 'required|string|max:30',
            'correo' => 'required|email|max:120',
        ])->validate();

        $existente = Cliente::where('documento', $data['documento'])->first();
        if ($existente) {
            throw ValidationException::withMessages([
                'documento' => "Ya existe un cliente con el documento '{$data['documento']}'.",
            ]);
        }

        $cliente = DB::transaction(function () use ($data) {
            return Cliente::create([
                'nombre' => $data['nombre'],
                'tipo_documento' => $data['tipo_documento'],
                'documento' => $data['documento'],
                'telefono' => $data['telefono'],
                'email' => $data['correo'],
                'direccion1' => $data['direccion1'] ?? '-',
                'departamento_id' => 54,
                'ciudad_id' => 889,
                'estado' => ClienteEstado::ACTIVO,
                'portal_acceso' => PortalAccesoEnum::SIN_ACCESO,
                'porcentaje_descuento' => $data['porcentaje_descuento'] ?? 0,
                'dias_credito' => $data['dias_credito'] ?? 0,
                'limite_credito' => $data['limite_credito'] ?? 0,
            ]);
        });

        static::registrarAuditoria(
            documentoTipo: 'cliente',
            documentoId: $cliente->id,
            accion: 'pos.cliente.crear',
            campo: 'estado',
            valorAnterior: null,
            valorNuevo: ClienteEstado::ACTIVO->value,
            estadoDocumento: ClienteEstado::ACTIVO->value,
            observacion: "Cliente '{$cliente->nombre}' ({$cliente->documento}) creado desde POS por usuario #".(Auth::id() ?? 0),
        );

        return $cliente;
    }
}
