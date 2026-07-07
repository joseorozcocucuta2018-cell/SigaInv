<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SanAndresSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'ARCHIPIÉLAGO DE SAN ANDRÉS, PROVIDENCIA Y SANTA CATALINA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "ARCHIPIÉLAGO DE SAN ANDRÉS, PROVIDENCIA Y SANTA CATALINA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'SAN ANDRÉS',
            'PROVIDENCIA',
            'CACAOTAL',
            'CENTRO',
            'GRACIAS A DIOS',
            'LA LAGUNA',
            'LA PAZ',
            'LOS ROBLES',
            'MARAVILLA',
            'MORGANS',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de San Andrés y Providencia.');
    }
}
