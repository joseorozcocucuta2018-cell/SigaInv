<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RisaraldaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'RISARALDA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "RISARALDA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'AGUADAS',
            'ANSERMA',
            'ARAUCA',
            'BELÉN DE UMBRÍA',
            'CHINCHINÁ',
            'FILANDIA',
            'GENEVA',
            'GUATICÁ',
            'MANIZALES',
            'MARQUETALIA',
            'MARULANDA',
            'MISTRATÓ',
            'PACORA',
            'PALESTINA',
            'PENSILVANIA',
            'RIOSUCIO',
            'RISARALDA',
            'SALAMINA',
            'SAMANÁ',
            'SAN JOSÉ',
            'SUPIA',
            'VICTORIA',
            'VILLAMARÍA',
            'YOLOMBÓ',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Risaralda.');
    }
}
