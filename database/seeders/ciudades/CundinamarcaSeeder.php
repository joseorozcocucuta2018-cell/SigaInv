<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CundinamarcaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'CUNDINAMARCA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "CUNDINAMARCA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'AGUA DE DIOS',
            'ALBAN',
            'ANOLAIMA',
            'ARBELAEZ',
            'BELTRAN',
            'BITUIMA',
            'BOJACA',
            'CABRERA',
            'CACHIPAY',
            'CAJICA',
            'CAPARRAPI',
            'CAQUEZA',
            'CARMEN DE CARUPA',
            'CHAGUANI',
            'CHIA',
            'CHIPAQUE',
            'CHOACHI',
            'CHOCONTÁ',
            'CIENAGA',
            'COGUA',
            'COTACACHI',
            'CUCUNUBA',
            'EL COLEGIAL',
            'EL ROSAL',
            'FACATATIVA',
            'FOMEQUE',
            'FONSECA',
            'FUNZA',
            'FÚQUENE',
            'FUSAGASUGÁ',
            'GACHALA',
            'GACHANCIPA',
            'GACHETA',
            'GAMA',
            'GIRARDOT',
            'GRANADA',
            'GUACAMAYAS',
            'GUADUAS',
            'GUASCA',
            'GUATAQUI',
            'GUAYABAL DE SIQUIMA',
            'GUAYABETAL',
            'HONDA',
            'MADRID',
            'MANTA',
            'MEDINA',
            'MOSQUERA',
            'NEMOCÓN',
            'NILO',
            'NIMAIMA',
            'NOCAIMA',
            'PACHO',
            'PAIME',
            'PANDI',
            'PARATEBUENO',
            'PASCA',
            'PUERTO SALGAR',
            'PULÍ',
            'QUEBRADANEGRA',
            'QUETAME',
            'QUIPILE',
            'RICAURTE',
            'SAN ANTONIO DEL TEQUENDAMA',
            'SAN FRANCISCO',
            'SAN JUAN DE RIOSEO',
            'SAN LUIS DE GACENO',
            'SAN MARCOS',
            'SASAIMA',
            'SESQUILÉ',
            'SIBATÉ',
            'SILVANIA',
            'SIMIJACA',
            'SOACHA',
            'SOPO',
            'SUBACHOQUE',
            'SUESCA',
            'SUPATÁ',
            'SUSA',
            'SUTATAUSA',
            'TABIO',
            'TENA',
            'TENJO',
            'TIBACUY',
            'TIERRA DENTRO',
            'TIMIZA',
            'TOCAIMA',
            'TOCANCIPÁ',
            'TOPAIPI',
            'UBALDO',
            'UBAQUE',
            'UBATE',
            'UNE',
            'ÚTICA',
            'VALLE DE SAN JOSE',
            'VIANI',
            'VIOTA',
            'YACOPÍ',
            'ZAPALLAL',
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

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Cundinamarca.');
    }
}
