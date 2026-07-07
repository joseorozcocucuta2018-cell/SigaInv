<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AtlanticoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'ATLÁNTICO')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "ATLÁNTICO" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'BARANOA', 'CAMPO DE LA CRUZ', 'CANDELARIA', 'COLOSO', 'CURUMANÍ',
            'FONSECA', 'GALAPA', 'GRAN ESTACIÓN', 'GUACAMAYAS', 'GUARANDA',
            'LA AVANZADA', 'LA CEJA', 'LA ESPERANZA', 'LA PAZ', 'MANATÍ',
            'PALMAR DE VARELA', 'PIJIÑO DEL CARMEN', 'PILONES', 'PISIÓN',
            'PLATO', 'PUERTO COLOMBIA', 'RIOVEHÍCULO', 'SABANAGRANDE',
            'SABANALARGA', 'SANTA BÁRBARA', 'SANTA CATALINA', 'SANTA ROSA',
            'SANTO DOMINGO', 'SAN JUAN NEPOMUCENO', 'SAN MARCOS', 'SUAN',
            'TALAMANCA', 'USIACURÍ', 'VALLEDUPAR', 'VÉLEZ', 'VENECIA',
            'VILLACOLOR', 'ZAPAYÁN', 'ZONA BANANERA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Atlántico.');
    }
}
