<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BogotaSeeder extends Seeder
{
    public function run(): void
    {
        $departamentoId = DB::table('departamentos')->where('nombre', 'BOGOTÁ, D.C.')->value('id');
        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "BOGOTÁ, D.C." en la tabla departamentos.');

            return;
        }

        $existe = DB::table('ciudades')->where('nombre', 'BOGOTÁ')->where('departamento_id', $departamentoId)->exists();
        if ($existe) {
            return;
        }

        DB::table('ciudades')->insert([
            [
                'nombre' => 'BOGOTÁ',
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('Se insertó la ciudad BOGOTÁ.');
    }
}
