<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PutumayoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'PUTUMAYO')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "PUTUMAYO" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'PUERTO ASIS',
            'VILLAGARZÓN',
            'MIRITÍ-PARANÁ',
            'SAN FRANCISCO',
            'SAN MIGUEL',
            'SANTIAGO',
            'Leticia',
            'EL ENCANTO',
            'LA CHORRERA',
            'PUERTO ALEGRE',
            'PUERTO NARIÑO',
            'PUERTO SANTANDER',
            'TURBACO',
            'EL BANCO',
            'LA PRIMAVERA',
            'CARURÚ',
            'PASTO',
            'MOCOA',
            'ORITO',
            'PUERTO LEGUÍZAMO',
            'SUCRE',
            'VALLE DEL GUAMUEZ',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Putumayo.');
    }
}
