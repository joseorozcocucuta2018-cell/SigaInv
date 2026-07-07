<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CordobaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'CÓRDOBA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "CÓRDOBA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'AYAPEL',
            'BUENAVISTA',
            'CANALETE',
            'CERETÉ',
            'CHIMÁ',
            'CHINÚ',
            'CIÉNAGA DE ORO',
            'COTORRA',
            'LA APARTADA',
            'LOS CÓRDOBAS',
            'MOMIL',
            'MONTELÍBANO',
            'MONTERÍA',
            'MOÑITOS',
            'PLANES DE BOLÍVAR',
            'PUEBLO NUEVO',
            'PUERTO ESCONDIDO',
            'PUERTO LIBERTADOR',
            'PURÍSIMA',
            'SAHAGÚN',
            'SAN ANDRÉS DE SOTAVENTO',
            'SAN ANTERO',
            'SAN BERNARDO DEL VIENTO',
            'SAN CARLOS',
            'SAN PELAYO',
            'TIERRALTA',
            'TUCHÍN',
            'VALLEDUPAR',
            'VALENCIA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Córdoba.');
    }
}
