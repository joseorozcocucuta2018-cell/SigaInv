<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaldasSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'CALDAS')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "CALDAS" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'AGUADAS',
            'ANSERMA',
            'ARUETO',
            'BAHÍA SOLANO',
            'BALBOA',
            'BOLÍVAR',
            'BRICEÑO',
            'BUENOS AIRES',
            'CALDAS',
            'CHINCHINÁ',
            'CÓRDOBA',
            'DOS QUEBRADAS',
            'GUACAMAYAS',
            'GUATICA',
            'GAVETE',
            'HIRACACHA',
            'JARDÍN',
            'LA DORADA',
            'LA MERCED',
            'MANIZALES',
            'MARQUETALIA',
            'MARULANDA',
            'MISTRATÓ',
            'NEIRA',
            'NORCASIA',
            'PÁCORA',
            'PALESTINA',
            'PENSILVANIA',
            'PEZ VÁZQUEZ',
            'PUEBLO RICO',
            'PUERTO ROMERO',
            'QUIBDÓ',
            'RÍO SUÁREZ',
            'SAMANÁ',
            'SAN JOSÉ',
            'SUSACÓN',
            'SUTATENZA',
            'TEMBLORES',
            'TOTA',
            'URABA',
            'VALPARAÍSO',
            'VÉLEZ',
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

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Caldas.');
    }
}
