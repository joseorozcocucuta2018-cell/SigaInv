<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaGuajiraSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'LA GUAJIRA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "LA GUAJIRA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ALBANIA',
            'BARRANCAS',
            'DIBULLA',
            'DISTRACCIÓN',
            'EL MOLINO',
            'FONSECA',
            'GARZON',
            'GONZÁLEZ',
            'HATONUEVO',
            'LABATECA',
            'LA JAGUA DEL PILAR',
            'MAICAO',
            'MANAURE',
            'MORROA',
            'NOVITA',
            'PIJIÑO DEL CARMEN',
            'PLATO',
            'RIOHACHA',
            'SAN JUAN DE LA COSTA',
            'SAN JUAN DEL CESAR',
            'TAMARA',
            'TARIFA',
            'URIBIA',
            'URUMITA',
            'VILLANUEVA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de La Guajira.');
    }
}
