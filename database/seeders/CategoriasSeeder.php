<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * CategoriasSeeder
 *
 * Categorías genéricas de primer nivel (categoria_id = null).
 * Sirven como punto de partida — el usuario las ajusta según su negocio.
 *
 * Se usan firstOrCreate vía insert con verificación previa para evitar
 * duplicados en re-ejecuciones del seeder.
 */
class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $categorias = [
            ['nombre' => 'General',                  'descripcion' => 'Categoría por defecto para productos sin clasificar'],
            ['nombre' => 'Materias Primas',          'descripcion' => 'Insumos y materiales para producción'],
            ['nombre' => 'Productos Terminados',     'descripcion' => 'Productos listos para la venta'],
            ['nombre' => 'Repuestos y Partes',       'descripcion' => 'Repuestos, piezas y componentes'],
            ['nombre' => 'Herramientas',              'descripcion' => 'Herramientas manuales y eléctricas'],
            ['nombre' => 'Equipos y Maquinaria',     'descripcion' => 'Equipos, máquinas y activos productivos'],
            ['nombre' => 'Papelería y Oficina',      'descripcion' => 'Artículos de papelería y consumibles de oficina'],
            ['nombre' => 'Aseo y Limpieza',          'descripcion' => 'Productos de aseo, limpieza e higiene'],
            ['nombre' => 'Tecnología',               'descripcion' => 'Equipos de cómputo, periféricos y accesorios'],
            ['nombre' => 'Servicios',                'descripcion' => 'Servicios prestados o contratados'],
        ];

        foreach ($categorias as $categoria) {
            // Evitar duplicados en re-ejecuciones
            $existe = DB::table('categorias')->where('nombre', $categoria['nombre'])->exists();
            if (! $existe) {
                DB::table('categorias')->insert([
                    'categoria_id' => null,
                    'nombre' => $categoria['nombre'],
                    'descripcion' => $categoria['descripcion'],
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
