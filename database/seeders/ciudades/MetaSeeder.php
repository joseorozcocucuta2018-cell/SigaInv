<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'META')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "META" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ACACÍAS',
            'BARRANCA DE UPIA',
            'CABUYARO',
            'CASTILLA LA NUEVA',
            'CUBARRAL',
            'CUMARAL',
            'EL CALVARIO',
            'EL CASTILLO',
            'EL DORADO',
            'FUENTE DE ORO',
            'GRANADA',
            'GUAMAL',
            'LA MACARENA',
            'LA URIBE',
            'LEJÍAS',
            'MAPIRIPÁ',
            'MESETAS',
            'LA VICTORIA',
            'OBANDO',
            'PUERTO CONCEPCIÓN',
            'PUERTO LLERAS',
            'PUERTO SANTANDER',
            'REGIDOR',
            'REMONTE',
            'RESTREPOS',
            'RÍO FRÍO',
            'SALDAÑA',
            'SAN CARLOS DE GUAROA',
            'SAN FRANCISCO',
            'SAN JUAN DE ARAMA',
            'SAN JOSÉ DEL PACÍFICO',
            'SAN LUIS DE GACENO',
            'SAN PEDRO DE LOS MILAGROS',
            'SAN VICENTE DEL CAGUÁN',
            'SANTA ROSA DE YOPAL',
            'SATIFICA',
            'SEOY',
            'SERREZUELA',
            'SOLANO',
            'SOLARTA',
            'TÁMARA',
            'TAURAMENA',
            'TESALIA',
            'TIMAYA',
            'VISTAHERMOSA',
            'VILLAVICENCIO',
            'YOPAL',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento del Meta.');
    }
}
