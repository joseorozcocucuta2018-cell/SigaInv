<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CajaEstado;
use App\Enums\CajaTipo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CajasSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('cajas')->count() > 0) {
            $this->command->info('CajasSeeder omitido — ya existen registros.');

            return;
        }

        DB::table('cajas')->insert([
            'nombre' => 'Caja Principal',
            'tipo' => CajaTipo::GENERAL->value,
            'saldo_inicial' => 0,
            'estado' => CajaEstado::ACTIVA->value,
            'activo' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
