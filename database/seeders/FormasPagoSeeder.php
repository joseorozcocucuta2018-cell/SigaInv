<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FormasPagoSeeder
 *
 * Métodos de pago de uso frecuente en Colombia.
 *
 * requiere_banco:
 *   true  → al registrar el pago se debe seleccionar una cuenta bancaria
 *   false → pago en efectivo o sin cuenta bancaria asociada
 */
class FormasPagoSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $formas = [
            ['nombre' => 'Efectivo',           'requiere_banco' => false, 'descripcion' => 'Pago en efectivo',      'activo' => true],
            ['nombre' => 'Transferencia',      'requiere_banco' => true,  'descripcion' => 'Transferencia bancaria o ACH', 'activo' => true],
            ['nombre' => 'Cheque',             'requiere_banco' => true,  'descripcion' => 'Cheque bancario',           'activo' => true],
            ['nombre' => 'Tarjeta Débito',     'requiere_banco' => true,  'descripcion' => 'Pago con tarjeta débito',    'activo' => true],
            ['nombre' => 'Tarjeta Crédito',    'requiere_banco' => false, 'descripcion' => 'Pago con tarjeta crédito',   'activo' => false],
            ['nombre' => 'Nequi',              'requiere_banco' => true,  'descripcion' => 'Pago por Nequi',              'activo' => true],
            ['nombre' => 'Daviplata',          'requiere_banco' => true,  'descripcion' => 'Pago por Daviplata',          'activo' => true],
            ['nombre' => 'PSE',                'requiere_banco' => true,  'descripcion' => 'Pago en línea PSE',           'activo' => true],
            ['nombre' => 'Crédito Directo',    'requiere_banco' => false, 'descripcion' => 'Crédito otorgado directamente por la empresa', 'activo' => false],
            ['nombre' => 'Compensación',       'requiere_banco' => false, 'descripcion' => 'Cruce de cuentas o compensación de cartera', 'activo' => false],
        ];

        foreach ($formas as $forma) {
            $existe = DB::table('formas_pago')->where('nombre', $forma['nombre'])->exists();

            if ($existe) {
                DB::table('formas_pago')->where('nombre', $forma['nombre'])->update([
                    'requiere_banco' => $forma['requiere_banco'],
                    'descripcion' => $forma['descripcion'],
                    'activo' => $forma['activo'],
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('formas_pago')->insert([
                    'nombre' => $forma['nombre'],
                    'requiere_banco' => $forma['requiere_banco'],
                    'descripcion' => $forma['descripcion'],
                    'activo' => $forma['activo'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
