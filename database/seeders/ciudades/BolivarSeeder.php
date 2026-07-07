<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BolivarSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'BOLÍVAR')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "BOLÍVAR" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ACHI', 'ALTOS DEL ROSARIO', 'AMAYA', 'ARJONA', 'ARROYOHONDO',
            'BARRACAS', 'BRAZUELO DE LA VICTORIA', 'CALAMA', 'CANTAGALLO',
            'CICUCO', 'CLEMENCIA', 'EL CARMEN DE BOLIVAR', 'EL GUAMO',
            'EL PEÑON', 'HATILLO DE LOBA', 'MAGANGUÉ', 'MAHATES', 'MARGARITA',
            'MARÍA LA BAJA', 'MONTECRISTO', 'MORALES', 'NOROSI', 'PINILLOS',
            'REGIDOR', 'RIO VIEJO', 'SAN CRISTÓBAL', 'SAN ESTANISLAO',
            'SAN FERNANDO', 'SAN JACINTO', 'SAN JACINTO DEL CAUCA',
            'SAN JUAN NEPOMUCENO', 'SAN MARTÍN DE LOBA', 'SAN PABLO',
            'SANTA CATALINA', 'SANTA ROSA', 'SANTA ROSA DEL SUR',
            'SIMITÍ', 'SOPLAVIENTO', 'TALAURIAL', 'TIQUISIO',
            'TURBACO', 'TURBANA', 'VILLANUEVA', 'ZAMBRANO',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Bolívar.');
    }
}
