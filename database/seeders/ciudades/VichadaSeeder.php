<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VichadaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'VICHADA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "VICHADA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'CUMARAL',
            'LA PRIMAVERA',
            'PUERTO GAITÁN',
            'PUERTO López',
            'PUERTO NARE',
            'PUERTO SANTANDER',
            'RESTREPOS',
            'SAN JOSÉ DE MIRANDA',
            'TRINIDAD',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Vichada.');
    }
}
