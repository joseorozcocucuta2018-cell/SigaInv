<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucreSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'SUCRE')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "SUCRE" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'BUENAVISTA',
            'CAIMITO',
            'CHINÚ',
            'CIÉNAGA DE ORO',
            'COLOSO',
            'COROZAL',
            'COVEÑAS',
            'EL ROBLE',
            'GALERAS',
            'GUARANDA',
            'LA UNIÓN',
            'LOS CÓRDOBAS',
            'MÁLAGA',
            'MORROA',
            'OVEJAS',
            'PALMITO',
            'SAMPUÉS',
            'SAN BENITO ABAD',
            'SAN JOSÉ DE TOLU',
            'SAN JUAN DE BETULIA',
            'SAN MARCOS',
            'SAN ONOFRE',
            'SAN PEDRO',
            'SAN VICENTE DE CHUCURÍ',
            'SINCE',
            'SINCELEJO',
            'SUCRE',
            'TOLÚ VIEJO',
            'TOLÚ',
            'VALLEDUPAR',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Sucre.');
    }
}
