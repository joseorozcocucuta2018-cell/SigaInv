<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChocoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'CHOCÓ')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "CHOCÓ" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ACANDÍ',
            'ALTO BAUDÓ',
            'ATRATO',
            'BAGADÓ',
            'BAHÍA SOLANO',
            'BAJO BAUDÓ',
            'BELÉN DE LOS ANDAQUÍES',
            'BETÉITIVA',
            'BOJAYA',
            'CANTÓN DE SAN PABLO',
            'CERRO AZUL',
            'CONDOTO',
            'EL CARMEN DE ATRATO',
            'EL LITORAL DEL SAN JUAN',
            'ISTMINA',
            'JURADÓ',
            'LLORÓ',
            'MEDIO ATRATO',
            'MEDIO BAUDÓ',
            'MEDIO SAN JUAN',
            'NOVITA',
            'NUÍ',
            'PUEBLO RICO',
            'QUIBDÓ',
            'RÍO IRO',
            'RÍO QUITO',
            'RIOSUCIO',
            'SAN JOSÉ DEL PALMAR',
            'SANTA GENOVEVA DE DOCO',
            'SANTA ROSA',
            'SANTO DOMINGO',
            'SIPI',
            'TADÓ',
            'UNGUÍA',
            'UNIÓN PANAMERICANA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento del Chocó.');
    }
}
