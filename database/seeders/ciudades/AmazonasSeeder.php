<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmazonasSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'AMAZONAS')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "AMAZONAS" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'LETICIA', 'EL ENCANTO', 'LA CHORRERA', 'PUERTO ALEGRE',
            'PUERTO NARIÑO', 'PUERTO SANTANDER', 'TURBACO', 'CARURÚ',
            'MIRITÍ-PARANÁ', 'SAN JOSÉ',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento del Amazonas.');
    }
}
