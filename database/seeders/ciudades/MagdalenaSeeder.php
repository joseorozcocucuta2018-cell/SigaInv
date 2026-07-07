<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MagdalenaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'MAGDALENA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "MAGDALENA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ALGARROBO',
            'ALGARROBO',
            'ALMERÍA',
            'BARRANCAS',
            'BOCACHICO',
            'BUENA VISTA',
            'CAMELIA',
            'CERRO DE SAN ANTONIO',
            'CHIVOLO',
            'CIÉNAGA',
            'CONCORDIA',
            'EL BANCO',
            'EL PIÑÓN',
            'EL RETEN',
            'FUNDACIÓN',
            'GUAMAL',
            'NUEVA GRANADA',
            'PEDRAZA',
            'PIJIÑO DEL CARMEN',
            'PILAR',
            'PLATO',
            'PUEBLO VIEJO',
            'REMOLINO',
            'SABANAS DE SAN ÁNGEL',
            'SALAMINA',
            'SAN SEBASTIÁN DE BUENAVISTA',
            'SAN ZENÓN',
            'SANTA ANA',
            'SANTA BÁRBARA DE PINTO',
            'SITIONUEVO',
            'TENERIFE',
            'VALLEDUPAR',
            'VEGANOS',
            'ZAPAYÁN',
            'ZONA BANANERA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento del Magdalena.');
    }
}
