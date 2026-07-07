<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AraucaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'ARAUCA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "ARAUCA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ARAUCA', 'ARAUQUITA', 'CRAVO NORTE', 'FORTUL', 'PUERTO RONDÓN',
            'SARAVENA', 'TAME',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Arauca.');
    }
}
