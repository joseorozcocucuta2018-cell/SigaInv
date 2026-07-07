<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoyacaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento
        $departamentoId = DB::table('departamentos')->where('nombre', 'BOYACÁ')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "BOYACÁ" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ALMEIDA',
            'AQUITANIA',
            'ARCABUCO',
            'BELÉN',
            'BERBEO',
            'BETÉITIVA',
            'BOYACÁ',
            'BRICEÑO',
            'BUENAVISTA',
            'BUSBANZÁ',
            'CALDAS',
            'CAMPOHERMOSO',
            'CERINZA',
            'CHINAVITA',
            'CHIQUINQUIRÁ',
            'CHISCAS',
            'CHITARAQUE',
            'CHIVATA',
            'CIÉNEGA',
            'CÓMBITA',
            'COPER',
            'CORRALES',
            'COVARACHÍA',
            'CUBARÁ',
            'CUCAITA',
            'CUITIVA',
            'CHÍCHARO',
            'EL COPEY',
            'FIRAVITOBA',
            'FLORESTA',
            'GACHANTIVÁ',
            'GAMEZA',
            'GARAGOA',
            'GUACAMAYAS',
            'GUATEQUE',
            'GUAYATÁ',
            'GÜICÁN DE LA SIERRA',
            'IZA',
            'JENESANO',
            'JERICÓ',
            'LABRANZAGRANDE',
            'LA ULABA',
            'LEIVA',
            'MACANAL',
            'MARIPÍ',
            'MIRAFLORES',
            'MONGUA',
            'MONSERRAT',
            'MOTAVITA',
            'MUZO',
            'NOBSA',
            'NUEVO COLÓN',
            'OICATÁ',
            'OTANCHE',
            'PACHAVITA',
            'PÁEZ',
            'PAIPA',
            'PAJARITO',
            'PALMAR DE VARELA',
            'PANTANO',
            'PARAMO',
            'PÁRAMO',
            'PAZ DE RÍO',
            'PESCA',
            'PISBA',
            'PUERTO BOYACÁ',
            'QUÍPAMA',
            'RAMIRIQUÍ',
            'RÁQUIRA',
            'RONDÓN',
            'SABOYÁ',
            'SÁCHICA',
            'SAMACÁ',
            'SAN EDUARDO',
            'SAN JOSÉ DE PARE',
            'SAN LUIS DE GACENO',
            'SAN MATEO',
            'SANTA SOFÍA',
            'SANTUARIO',
            'SATIVANORTE',
            'SATIVASUR',
            'SIACHOQUE',
            'SOATÁ',
            'SOCOTÁ',
            'SOCHA',
            'SOGAMOSO',
            'SOMONDOCO',
            'SORA',
            'SOTAQUIRÁ',
            'SUSACÓN',
            'SUTAMARCHÁN',
            'SUTATENZA',
            'TASCO',
            'TENERIFE',
            'TIBACUY',
            'TINJACÁ',
            'TIPACOQUE',
            'TOGÜÍ',
            'TÓPAGA',
            'TOTA',
            'TUNUNGUI',
            'TURMEQUÉ',
            'TUTA',
            'TUTAZÁ',
            'UMBITÁ',
            'VENTAQUEMADA',
            'VILLA DE LEYVA',
            'YACOPÍ',
            'ZETAQUIRA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Boyacá.');
    }
}
