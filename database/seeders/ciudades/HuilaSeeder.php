<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HuilaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'HUILA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "HUILA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ACEVEDO',
            'AGRADO',
            'AIPE',
            'ALGECIRAS',
            'ALTAMIRA',
            'BARAYA',
            'CAMPOALEGRE',
            'COLOMBIA',
            'ELÍAS',
            'FENTON',
            'GARZÓN',
            'GIGANTE',
            'GUADALAS',
            'HOBO',
            'IQUIRA',
            'ISNOS',
            'JAMBALÓ',
            'LAS VEGAS',
            'LA ARGENTA',
            'LA PLATA',
            'MACOA',
            'MESETAS',
            'MONTAGUA',
            'MUÑICO',
            'NEIVA',
            'NOVIEMBRE DE 1821',
            'ORITO',
            'PAICOL',
            'PALERMO',
            'PALESTINA',
            'PITAL',
            'PITALITO',
            'RIVERA',
            'ROSELLÓN',
            'SALADOBLANCO',
            'SAN AGUSTÍN',
            'SAN ANTONIO',
            'SAN LUIS',
            'SANTA MARÍA',
            'SUAZA',
            'TARQUI',
            'TESALIA',
            'TIMANÁ',
            'VILLAVIEJA',
            'YAGUARA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento del Huila.');
    }
}
