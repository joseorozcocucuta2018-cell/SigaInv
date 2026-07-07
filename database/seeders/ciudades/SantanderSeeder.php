<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SantanderSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'SANTANDER')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "SANTANDER" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ABREGO',
            'ALBANIA',
            'ARATOCA',
            'BARBOSA',
            'BARICHARA',
            'BARRANCABERMEJA',
            'BETULIA',
            'BOLÍVAR',
            'CABEZAS',
            'CALIFORNIA',
            'CAPITANEJO',
            'CARCASI',
            'CEPITÁ',
            'CERRITO',
            'CHARALÁ',
            'CHARTA',
            'CHIMICHINA',
            'CHIPATÁ',
            'CIMITARRA',
            'CONFINES',
            'CONTRATACIÓN',
            'COROMORO',
            'CURITÍ',
            'EL CARMEN DE CHUCURÚ',
            'EL GUACAMAYO',
            'EL PEÑÓN',
            'ENCINO',
            'FLORIDABLANCA',
            'GALÁN',
            'GÁMBITA',
            'GIRÓN',
            'GUACA',
            'GUADALUPE',
            'GUAPOTA',
            'GUAVATA',
            'GÜEPSA',
            'HATO',
            'JORDÁN',
            'LA BELLEZA',
            'LANDÁZURI',
            'LA FLORIDA',
            'LA PAZ',
            'LEBRIJA',
            'LOS SANTOS',
            'MACARAVITA',
            'MÁLAGA',
            'MATANZA',
            'MOGOTES',
            'MOLAGAVITA',
            'MORALES',
            'NOREÑA',
            'OIBA',
            'ONZAGA',
            'PALMAR',
            'PALMAS DEL SOCORRO',
            'PÁRAMO',
            'PIEDECUESTA',
            'PINCHOTE',
            'PUENTE NACIONAL',
            'PUERTO BERRÍO',
            'PUERTO FLOREZ',
            'PUERTO SANTANDER',
            'RAFAEL URIBE URIBE',
            'RIONEGRO',
            'SABANA DE TORRES',
            'SAN ANDRÉS',
            'SAN BENITO',
            'SAN GIL',
            'SAN JOAQUÍN',
            'SAN JOSÉ DE MIRANDA',
            'SAN MARCIAL',
            'SAN MIGUEL',
            'SAN VICENTE DE CHUCURÍ',
            'SANTA BÁRBARA',
            'SANTA HELENA DEL OPÓN',
            'SIMACOTA',
            'SOCORRO',
            'SUAITA',
            'SUCRE',
            'SURATA',
            'TONA',
            'VALDEZUELA',
            'VÉLEZ',
            'VILLANUEVA',
            'YARUMAL',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Santander.');
    }
}
