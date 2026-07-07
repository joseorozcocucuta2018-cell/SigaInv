<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * EmpresaSeeder
 *
 * Crea el registro único de la empresa (id = 1).
 * Este registro NUNCA se duplica — el sistema siempre trabaja
 * con id = 1. Se edita desde el panel en Configuración > Empresa.
 *
 * Los valores marcados con "COMPLETAR" deben actualizarse
 * desde el panel antes de emitir el primer documento.
 *
 * departamento_id = 54  → Norte de Santander (DANE)
 * ciudad_id       = 889 → Cúcuta
 * (ajustar si la empresa está en otra ciudad)
 */
class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $existe = DB::table('empresa')->where('id', 1)->exists();

        if (! $existe) {
            DB::table('empresa')->insert([
                'id' => 1,

                // --- Identificación legal ---
                'razon_social' => 'NOMBRE DE LA EMPRESA S.A.S',   // COMPLETAR
                'nombre_comercial' => null,
                'nit' => '900000000',                     // COMPLETAR
                'digito_verificacion' => 0,                               // COMPLETAR
                'tipo_persona' => 'juridica',

                // --- Tributario ---
                'regimen_tributario' => 'comun',
                'responsable_iva' => true,
                'actividad_ciiu' => null,                            // COMPLETAR

                // --- Ubicación fiscal ---
                'direccion' => 'DIRECCIÓN FISCAL',              // COMPLETAR
                'departamento_id' => 54,    // Norte de Santander
                'ciudad_id' => 889,   // Cúcuta
                'pais' => 'Colombia',

                // --- Contacto ---
                'telefono' => null,                            // COMPLETAR
                'celular' => null,                            // COMPLETAR
                'email' => null,                            // COMPLETAR
                'email_documentos' => null,                            // COMPLETAR
                'sitio_web' => null,

                // --- Resolución DIAN ---
                'resolucion_dian' => null,
                'resolucion_fecha_expedicion' => null,
                'resolucion_fecha_vencimiento' => null,
                'resolucion_desde' => null,
                'resolucion_hasta' => null,

                // --- Documentos ---
                'logo' => null,
                'logo_pos' => null,
                'logo_impresion' => null,
                'pie_pagina' => 'Gracias por su preferencia.',
                'notas_factura' => null,

                // --- Márgenes de ganancia ---
                'margen_ganancia_default' => 30.00,
                'margen_ganancia_minimo' => 10.00,

                // --- Configuración ---
                'usa_seriales' => false,
                'una_sola_bodega' => false,

                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
