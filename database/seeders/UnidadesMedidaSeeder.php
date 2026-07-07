<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * UnidadesMedidaSeeder
 *
 * Unidades de medida de uso frecuente en sistemas de inventario colombianos.
 * Incluye unidades de cantidad, peso, volumen, longitud, área y tiempo.
 * Todas activas por defecto.
 *
 * Protección contra duplicados: verifica por nombre antes de insertar.
 * Seguro para ejecutar múltiples veces (db:seed repetido).
 */
class UnidadesMedidaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $unidades = [
            // Cantidad
            ['nombre' => 'Unidad',        'simbolo' => 'UND',  'descripcion' => 'Unidad genérica'],
            ['nombre' => 'Par',           'simbolo' => 'PAR',  'descripcion' => 'Conjunto de dos unidades'],
            ['nombre' => 'Docena',        'simbolo' => 'DOC',  'descripcion' => '12 unidades'],
            ['nombre' => 'Ciento',        'simbolo' => 'CTO',  'descripcion' => '100 unidades'],
            ['nombre' => 'Caja',          'simbolo' => 'CJA',  'descripcion' => 'Caja (contenido variable)'],
            ['nombre' => 'Paquete',       'simbolo' => 'PQT',  'descripcion' => 'Paquete (contenido variable)'],
            ['nombre' => 'Rollo',         'simbolo' => 'RLL',  'descripcion' => 'Rollo'],
            ['nombre' => 'Juego',         'simbolo' => 'JGO',  'descripcion' => 'Juego o set de piezas'],

            // Peso
            ['nombre' => 'Gramo',         'simbolo' => 'g',    'descripcion' => 'Gramo'],
            ['nombre' => 'Kilogramo',     'simbolo' => 'kg',   'descripcion' => 'Kilogramo (1000 g)'],
            ['nombre' => 'Tonelada',      'simbolo' => 'ton',  'descripcion' => 'Tonelada métrica (1000 kg)'],
            ['nombre' => 'Libra',         'simbolo' => 'lb',   'descripcion' => 'Libra (0.453592 kg)'],
            ['nombre' => 'Onza',          'simbolo' => 'oz',   'descripcion' => 'Onza (28.3495 g)'],

            // Volumen
            ['nombre' => 'Mililitro',     'simbolo' => 'ml',   'descripcion' => 'Mililitro'],
            ['nombre' => 'Litro',         'simbolo' => 'L',    'descripcion' => 'Litro (1000 ml)'],
            ['nombre' => 'Galón',         'simbolo' => 'gal',  'descripcion' => 'Galón (3.785 L)'],
            ['nombre' => 'Metro cúbico',  'simbolo' => 'm³',   'descripcion' => 'Metro cúbico'],

            // Longitud
            ['nombre' => 'Milímetro',     'simbolo' => 'mm',   'descripcion' => 'Milímetro'],
            ['nombre' => 'Centímetro',    'simbolo' => 'cm',   'descripcion' => 'Centímetro'],
            ['nombre' => 'Metro',         'simbolo' => 'm',    'descripcion' => 'Metro'],
            ['nombre' => 'Kilómetro',     'simbolo' => 'km',   'descripcion' => 'Kilómetro'],
            ['nombre' => 'Pulgada',       'simbolo' => 'in',   'descripcion' => 'Pulgada (2.54 cm)'],
            ['nombre' => 'Pie',           'simbolo' => 'ft',   'descripcion' => 'Pie (30.48 cm)'],
            ['nombre' => 'Yarda',         'simbolo' => 'yd',   'descripcion' => 'Yarda (91.44 cm)'],

            // Área
            ['nombre' => 'Metro cuadrado', 'simbolo' => 'm²',   'descripcion' => 'Metro cuadrado'],

            // Tiempo
            ['nombre' => 'Hora',          'simbolo' => 'h',    'descripcion' => 'Hora de servicio'],
            ['nombre' => 'Día',           'simbolo' => 'día',  'descripcion' => 'Día de servicio'],
        ];

        foreach ($unidades as $unidad) {
            $existe = DB::table('unidades_medida')
                ->where('nombre', $unidad['nombre'])
                ->exists();

            if (! $existe) {
                DB::table('unidades_medida')->insert([
                    'nombre' => $unidad['nombre'],
                    'simbolo' => $unidad['simbolo'],
                    'descripcion' => $unidad['descripcion'],
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
