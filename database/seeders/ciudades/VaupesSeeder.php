<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VaupesSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'VAUPÉS')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "VAUPÉS" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'MITÚ',
            'CARURÚ',
            'PACOA',
            'TARAIRA',
            'JOSE IGNACIO RONDON',
            'CUMARIBO',
            'MAPIRIPANA',
            'PUERTO CONCHAVIA',
            'SAN JUAN DE MAPIRIPANA',
            'YAHUA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Vaupés.');
    }
}
