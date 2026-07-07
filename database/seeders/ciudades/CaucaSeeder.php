<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaucaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'CAUCA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "CAUCA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'AGUACHICA',
            'ALMAGUER',
            'ARGELIA',
            'BALBOA',
            'BOLÍVAR',
            'BUENOS AIRES',
            'CAJIBÍO',
            'CALDONÓ',
            'CAPUL',
            'CARLOSPA',
            'CHAPARRAL',
            'COLOSO',
            'CORINTO',
            'CUBARÁ',
            'CURILLO',
            'FLORENCIA',
            'GUAPI',
            'INZERCIÓN',
            'JAMBALÓ',
            'LA SIERRA',
            'LA VEGA',
            'LOPEZ DE MICAY',
            'MERCADERES',
            'MIRANDA',
            'MORALES',
            'PADILLA',
            'PÁEZ',
            'PATIA',
            'PIAMONTE',
            'PIENDAMO',
            'PUERTO TEJADA',
            'PUPIAL',
            'ROSAS',
            'SAN SEBASTIÁN',
            'SANTANDER DE QUILICHAO',
            'SANTA ROSA',
            'SILVIA',
            'SUCRE',
            'SUÁREZ',
            'TIMBIQUÍ',
            'TORIBIO',
            'TOTORÓ',
            'VILLA RICA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento del Cauca.');
    }
}
