<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NorteDeSantanderSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'NORTE DE SANTANDER')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "NORTE DE SANTANDER" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ABREGO',
            'ARBOLEDAS',
            'BOCHALEMA',
            'BUCARASICA',
            'CÁCOTA',
            'CÓRDOBA',
            'CUCUTA',
            'CUMARAL',
            'DURANIA',
            'EL CARMELO',
            'EL TARRA',
            'FACATATIVA',
            'GAMARRA',
            'GONZÁLEZ',
            'HACARÍ',
            'HERRAN',
            'LABATECA',
            'LA ESPERANZA',
            'LA PAZ',
            'LA PLAYA',
            'LOS PATIOS',
            'LOURDES',
            'MUTISCUA',
            'OCAÑA',
            'PAMPLONA',
            'PUERTO SANTANDER',
            'RAGONVALIA',
            'SALAZAR',
            'SAN CALIXTO',
            'SAN CAYETANO',
            'SANTIAGO',
            'SANTO DOMINGO',
            'SARDINATA',
            'TEORAMA',
            'TIBÚ',
            'TOLEDO',
            'VILLA CARO',
            'VILLA DEL ROSARIO',
        ];

        foreach ($ciudades as $ciudad) {
            $existe = DB::table('ciudades')
                ->where('nombre', $ciudad)
                ->where('departamento_id', $departamentoId)
                ->exists();
            if (! $existe) {
                DB::table('ciudades')->insert([
                    'nombre' => $ciudad,
                    'departamento_id' => $departamentoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Norte de Santander.');
    }
}
