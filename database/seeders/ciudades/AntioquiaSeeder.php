<?php

namespace Database\Seeders\ciudades;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AntioquiaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos el ID del departamento "ANTIOQUIA"
        $departamentoId = DB::table('departamentos')->where('nombre', 'ANTIOQUIA')->value('id');

        if (! $departamentoId) {
            $this->command->error('No se encontró el departamento "ANTIOQUIA" en la tabla "departamentos".');

            return;
        }

        $ciudades = [
            'ABEJORRAL', 'AMAGÁ', 'AMALFI', 'ANDÉS', 'ANGELOPOLIS', 'ANGOSTURA', 'ANORÍ',
            'APARTADÓ', 'ARBOLEDA', 'ARGELIA', 'ARMENIA', 'BARCELONA', 'BELMIRA', 'BETANIA',
            'BETULIA', 'BRICEÑO', 'BURITICÁ', 'CABEZAS', 'CAICEDO', 'CARAMANTA', 'CAREPA',
            'CAROLINA DEL PRÍNCIPE', 'CARMEN DE VIBORAL', 'CAROLINA', 'CATAS DE BUSTOS',
            'CAUJERÍA', 'CERRITO', 'CERRO AZUL', 'CHIGORODÓ', 'CISNEROS', 'CIUDAD BOLÍVAR',
            'COCORNÁ', 'CONCEPCIÓN', 'CONCORDIA', 'COPACABANA', 'DAVEIBA', 'DON MATÍAS',
            'EBÉJICO', 'EL BAGRE', 'EL CARMEN DE VIBORAL', 'EL PEÑOL', 'EL RETIRO',
            'EL SANTUARIO', 'ENTRERRIOS', 'ENVIGADO', 'FREDONIA', 'FRONTINO', 'GIRALDO',
            'GIRARDOTA', 'GÓMEZ PLATA', 'GRANADA', 'GUADALUPE', 'GUARNE', 'GUATAPÉ',
            'HELICONIA', 'HISPANIA', 'ITAGÜÍ', 'ITUANGO', 'JARDÍN', 'JERICÓ',
            'LA CEJA DEL TAMBO', 'LA ESTRELLA', 'LA PINTADA', 'LA UNIÓN', 'LIBORINA',
            'MACEO', 'MARINILLA', 'MEDELLÍN', 'MONTEBELLO', 'MURINDÓ', 'MUTATÁ',
            'NARIÑO', 'NECOCLÍ', 'NECHÍ', 'OLAYA', 'PEQUE', 'PUEBLORRICO', 'PUERTO BERRÍO',
            'PUERTO NARE', 'REMEDIOS', 'RIONEGRO', 'SABANETA', 'SALGAR', 'SAN ANDRÉS DE CUERQUIA',
            'SAN CARLOS', 'SAN FRANCISCO', 'SAN JERÓNIMO', 'SAN JOSÉ DE LA MONTAÑA',
            'SAN JUAN DE URABÁ', 'SAN LUIS', 'SAN PEDRO', 'SAN RAFAEL', 'SAN ROQUE',
            'SAN VICENTE', 'SANTA BÁRBARA', 'SANTA FE DE ANTIOQUIA', 'SANTA ROSA DE OSOS',
            'SANTO DOMINGO', 'EL SOCORRO', 'SOPETRÁN', 'TÁMESIS', 'TARAZÁ', 'TASCO',
            'TITIRIBÍ', 'TOLEDO', 'TURBO', 'URAMITA', 'VALDIVIA', 'VALLE DE ABURRÁ',
            'VALLE DE SAN NICOLÁS', 'VÉLEZ', 'VILLAMARÍA', 'YALÍ', 'YARUMAL', 'YOLOMBÓ',
            'YONDO', 'ZARAGOZA',
        ];

        foreach ($ciudades as $ciudad) {
            DB::table('ciudades')->insert([
                'nombre' => $ciudad,
                'departamento_id' => $departamentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info(count($ciudades).' ciudades insertadas en el departamento de Antioquia.');
    }
}
