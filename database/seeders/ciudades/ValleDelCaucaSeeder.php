<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ValleDelCaucaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'VALLE DEL CAUCA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "VALLE DEL CAUCA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ALCALÁ',
            'ANDALUCÍA',
            'ANSERMANUEVO',
            'ARGELIA',
            'BOLÍVAR',
            'BUENAVENTURA',
            'BUGA',
            'BUGALAGRANDE',
            'CAICEDONIA',
            'CALI',
            'CANDELARIA',
            'CARTAGO',
            'DAGUA',
            'EL ÁGUILA',
            'EL CAIRO',
            'EL CERRITO',
            'EL DOVIO',
            'FLORIDA',
            'GINEBRA',
            'GUACARÍ',
            'JAMUNDÍ',
            'LA CUMBRE',
            'LA UNIÓN',
            'LA VICTORIA',
            'OBANDO',
            'PALMIRA',
            'PRADERA',
            'RESTREPOS',
            'RIOFRÍO',
            'ROLDANILLO',
            'SAN PEDRO',
            'SEVILLA',
            'TORO',
            'TRUJILLO',
            'TULUÁ',
            'YOTOCO',
            'YUMBO',
            'ZARZAL',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento del Valle del Cauca.');
    }
}
