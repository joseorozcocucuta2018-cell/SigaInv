<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProveedorEstado;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedoresSeeder extends Seeder
{
    public function run(): void
    {
        $existe = DB::table('proveedores')->where('id', 1)->orWhere('documento', '9999999999')->exists();
        if ($existe) {
            $this->command->info('ProveedoresSeeder omitido — ya existe proveedor de ejemplo.');

            return;
        }

        $now = Carbon::now();
        $usuarioId = DB::table('users')->value('id');

        DB::table('proveedores')->insert([
            [
                'id' => 1,
                'nombre' => 'PROVEEDORES VARIOS',
                'documento' => '9999999999',
                'tipo_documento' => 'NIT',
                'telefono' => '999999999',
                'email' => 'no_tiene_correo@correo.com',
                'direccion1' => 'SIN INFORMACION',
                'direccion2' => null,
                'departamento_id' => 54,     // Norte de Santander (DANE)
                'ciudad_id' => 889,    // Cúcuta
                'saldo' => 0.00,
                'pais' => 'Colombia',
                'estado' => ProveedorEstado::ACTIVO->value,
                'limite_credito' => 0.00,
                'dias_credito' => 0,
                'dias_pago' => 0,
                'contacto_principal' => null,
                'sitio_web' => null,
                'usuario_id' => $usuarioId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
