<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NarinoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'NARIÑO')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "NARIÑO" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ABREGO',
            'ALDANA',
            'ANCUYA',
            'ARBOLEDA',
            'BARBACOAS',
            'BELÉN',
            'BUESACO',
            'CÓRDOBA',
            'CHACHAGÜÍ',
            'COLÓN',
            'CONSACA',
            'CONTADERO',
            'CÓRDOBA',
            'CUASPÚD',
            'CUMBAL',
            'CUMBITARA',
            'DEPARTAMENTO DE NARIÑO',
            'EL CHARCO',
            'EL PEÑOL',
            'EL ROSARIO',
            'EL TABLÓN DE GÓMEZ',
            'EL TAMBO',
            'FUNES',
            'GUACHUCAL',
            'GUAITARILLA',
            'GUALMATÁN',
            'ILES',
            'IMUÉS',
            'IPIALES',
            'LA CRUZ',
            'LA FLORIDA',
            'LA LLANADA',
            'LA TOLA',
            'LA UNIÓN',
            'LEIVA',
            'LINARES',
            'LOS ANDES',
            'MAGÜÍ',
            'MALLAMA',
            'MOSQUERA',
            'NARIÑO',
            'OLAYA HERRERA',
            'OSPINA',
            'PÁEZ',
            'PATIA',
            'PIEDRANCHA',
            'PUERRES',
            'PUERTO CARREÑO',
            'PUPiales',
            'RICAURTE',
            'ROBERTO PAYAN',
            'SAMANIEGO',
            'SAN ANDRÉS DE TUMACO',
            'SAN JOSÉ',
            'SAN JUAN DE PASTO',
            'SAN LORENZO',
            'SAN PABLO',
            'SAN PEDRO DE CARTAGO',
            'SANTA BÁRBARA',
            'SANTACRUZ',
            'SAPUYES',
            'TAMINANGO',
            'TANGUA',
            'TUMACO',
            'TUQUERRES',
            'YACUANQUER',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Nariño.');
    }
}
