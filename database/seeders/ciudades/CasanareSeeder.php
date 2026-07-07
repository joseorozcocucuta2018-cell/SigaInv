<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CasanareSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'CASANARE')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "CASANARE" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'AGUAZUL',
            'CHÁMEZA',
            'HATO COROZAL',
            'LA SALINA',
            'MANÍ',
            'MONTERREY',
            'NUNCHÍA',
            'OROCUÉ',
            'PAZ DE ARIPORO',
            'PUERTO DE CÚCUTA',
            'PUERTO NARE',
            'PUERTO SALGAR',
            'REMOLINO',
            'SABANAS DE SAN ÁNGEL',
            'SACAMA',
            'SAN LUIS DE GACENO',
            'SAN VICENTE DEL CAGUÁN',
            'SANTA ROSA',
            'TAMARA',
            'TAURAMENA',
            'TRINIDAD',
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

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Casanare.');
    }
}
