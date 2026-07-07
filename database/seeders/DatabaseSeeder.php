<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DatabaseSeeder
 *
 * Orquesta la ejecución de todos los seeders en el orden correcto.
 * El orden respeta las dependencias de FK:
 *
 *  1.  RoleSeeder           — roles Spatie (generado por crear.ps1 Paso 9)
 *  2.  UserSeeder           — usuario admin + usuarios testing (depende: roles)
 *  3.  DepartamentosSeeder  — sin dependencias         (coredata)
 *  4.  CiudadesSeeder       — depende: departamentos   (coredata)
 *  5.  EmpresaSeeder        — depende: departamentos, ciudades
 *  6.  UnidadesMedidaSeeder — sin dependencias
 *  7.  CategoriasSeeder     — sin dependencias (FK self-referencial nullable)
 *  8.  ImpuestosSeeder      — sin dependencias
 *  9.  FormasPagoSeeder     — sin dependencias
 * 10. NumeracionesSeeder   — sin dependencias
 * 11. BodegasSeeder        — depende: departamentos, ciudades, users
 * 12. ClientesSeeder       — depende: departamentos, ciudades, users  (coredata)
 * 13. ProveedoresSeeder    — depende: departamentos, ciudades, users  (coredata)
 *
 * COMPORTAMIENTO POR ESCENARIO:
 *
 *  Escenario A — migrate:fresh + db:seed (instalación limpia):
 *    Todas las tablas vacías → count() = 0 → ejecuta TODO completo.
 *
 *  Escenario B — migrate + db:seed (tablas nuevas sobre proyecto existente):
 *    Tablas del script con datos → count() > 0 → se omiten con mensaje.
 *    Tablas nuevas vacías → sus seeders internos insertan normalmente.
 *
 *  Escenario C — db:seed repetido (sin migraciones nuevas):
 *    Todo ya tiene datos → seeders del script se omiten.
 *    Seeders nuevos verifican internamente → no duplican.
 *
 * CREDENCIALES ADMIN (.env):
 *   ADMIN_NAME=Administrador
 *   ADMIN_EMAIL=admin@ejemplo.com
 *   ADMIN_PASSWORD=su_clave_segura
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ─────────────────────────────────────────────────────────────
        if (DB::table('roles')->count() === 0) {
            $this->call(RoleSeeder::class);
        } else {
            $this->command->info('RoleSeeder omitido — roles ya existen.');
        }

        // ── Usuario admin + usuarios testing ────────────────────────────────
        // UserSeeder es idempotente — seguro en cualquier escenario
        $this->call(UserSeeder::class);

        // ── Coredata ──────────────────────────────────────────────────────────
        if (DB::table('departamentos')->count() === 0) {
            $this->call(DepartamentosSeeder::class);
        } else {
            $this->command->info('DepartamentosSeeder omitido — ya existen registros.');
        }

        if (DB::table('ciudades')->count() === 0) {
            $this->call(CiudadesSeeder::class);
        } else {
            $this->command->info('CiudadesSeeder omitido — ya existen registros.');
        }

        if (DB::table('clientes')->count() === 0) {
            $this->call(ClientesSeeder::class);
        } else {
            $this->command->info('ClientesSeeder omitido — ya existen registros.');
        }

        if (DB::table('proveedores')->count() === 0) {
            $this->call(ProveedoresSeeder::class);
        } else {
            $this->command->info('ProveedoresSeeder omitido — ya existen registros.');
        }

        // ── Seeders de catálogos ─────────────────────────────────────────────
        // Cada uno verifica existencia antes de insertar — idempotentes.
        $this->call([
            // EmpresaSeeder::class,
            NumeracionesSeeder::class,
            UnidadesMedidaSeeder::class,
            // CategoriasSeeder::class,
            ImpuestosSeeder::class,
            FormasPagoSeeder::class,
            BodegasSeeder::class,
            CajasSeeder::class,
        ]);

        $this->command->info('✓ Seeders ejecutados correctamente.');
    }
}
