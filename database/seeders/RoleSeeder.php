<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // ROLES BASE
        // ============================================================
        $roles = [
            'administrador',
            'auxiliar',
            'contador',
            'vendedor',
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // ============================================================
        // PERMISOS POR MÓDULO
        // ============================================================

        // Configuración
        $permisosConfig = [
            'config.ver',
            'config.editar',
            'empresa.ver',
            'empresa.editar',
            'bodega.ver',
            'bodega.crear',
            'bodega.editar',
            'bodega.eliminar',
            'categoria.ver',
            'categoria.crear',
            'categoria.editar',
            'categoria.eliminar',
            'marca.ver',
            'marca.crear',
            'marca.editar',
            'marca.eliminar',
            'impuesto.ver',
            'impuesto.crear',
            'impuesto.editar',
            'impuesto.eliminar',
            'forma_pago.ver',
            'forma_pago.crear',
            'forma_pago.editar',
            'forma_pago.eliminar',
            'numeracion.ver',
            'numeracion.crear',
            'numeracion.editar',
            'numeracion.eliminar',
            'unidad_medida.ver',
            'unidad_medida.crear',
            'unidad_medida.editar',
            'unidad_medida.eliminar',
            'banco.ver',
            'banco.crear',
            'banco.editar',
            'banco.eliminar',
        ];

        // Administración
        $permisosAdmin = [
            'admin.ver',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',
            'auditoria.ver',
        ];

        // Inventario
        $permisosInventario = [
            'producto.ver',
            'producto.crear',
            'producto.editar',
            'producto.eliminar',
            'stock.ver',
            'movimiento_inventario.ver',
            'historico_precios.ver',
            'traslado.ver',
            'traslado.crear',
            'traslado.editar',
            'traslado.eliminar',
            'traslado.confirmar',
            'traslado.anular',
            'proveedor.ver',
            'proveedor.crear',
            'proveedor.editar',
            'proveedor.eliminar',
            'cliente_catalogo.ver',
            'cliente_catalogo.crear',
            'cliente_catalogo.editar',
            'cliente_catalogo.eliminar',
        ];

        // Compras
        $permisosCompras = [
            'compra.ver',
            'compra.crear',
            'compra.editar',
            'compra.eliminar',
            'compra.confirmar',
            'pago_proveedor.ver',
            'pago_proveedor.crear',
            'pago_proveedor.editar',
            'pago_proveedor.eliminar',
        ];

        // Ventas
        $permisosVentas = [
            'cotizacion.ver',
            'cotizacion.crear',
            'cotizacion.editar',
            'cotizacion.eliminar',
            'remision.ver',
            'remision.crear',
            'remision.editar',
            'remision.eliminar',
            'remision.confirmar',
            'venta.ver',
            'venta.crear',
            'venta.editar',
            'venta.eliminar',
            'venta.confirmar',
            'pago_cliente.ver',
            'pago_cliente.crear',
            'pago_cliente.editar',
            'pago_cliente.eliminar',
        ];

        // Transformaciones
        $permisosTransformaciones = [
            'transformacion.ver',
            'transformacion.crear',
            'transformacion.editar',
            'transformacion.eliminar',
            'transformacion.confirmar',
        ];

        // Fórmulas de Transformación
        $permisosFormulaTransformacion = [
            'formula_transformacion.ver',
            'formula_transformacion.crear',
            'formula_transformacion.editar',
            'formula_transformacion.eliminar',
        ];

        // Reportes
        $permisosReportes = [
            'reporte.ver',
            'reporte.exportar',
            'reporte.imprimir',
        ];

        // Dashboard
        $permisosDashboard = [
            'dashboard.ver',
        ];

        // Portal (cliente)
        $permisosPortal = [
            'portal.ver',
            'portal.mis_cotizaciones',
            'portal.mis_remisiones',
            'portal.mis_ventas',
            'portal.mi_estado_cuenta',
        ];

        // Ajustes de Inventario
        $permisosAjusteInventario = [
            'ajuste_inventario.ver',
            'ajuste_inventario.crear',
            'ajuste_inventario.editar',
            'ajuste_inventario.eliminar',
            'ajuste_inventario.confirmar',
        ];

        // Conteo Físico
        $permisosConteoFisico = [
            'conteo_fisico.ver',
            'conteo_fisico.crear',
            'conteo_fisico.editar',
            'conteo_fisico.eliminar',
            'conteo_fisico.cerrar',
            'conteo_fisico.generar_ajuste',
        ];

        // Caja
        $permisosCaja = [
            'caja.ver',
            'caja.crear',
            'caja.editar',
            'caja.eliminar',
            'movimiento_caja.ver',
            'movimiento_caja.crear',
            'movimiento_caja.editar',
            'movimiento_caja.eliminar',
            'turno.ver',
        ];

        // Consolidar todos los permisos
        $todosPermisos = array_merge(
            $permisosConfig,
            $permisosAdmin,
            $permisosInventario,
            $permisosCompras,
            $permisosVentas,
            $permisosTransformaciones,
            $permisosFormulaTransformacion,
            $permisosAjusteInventario,
            $permisosConteoFisico,
            $permisosReportes,
            $permisosDashboard,
            $permisosPortal,
            $permisosCaja
        );

        // Crear permisos
        foreach ($todosPermisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // ============================================================
        // ASIGNAR PERMISOS POR ROL
        // ============================================================

        // ADMINISTRADOR: acceso total
        $adminRole = Role::findByName('administrador');
        $adminRole->givePermissionTo($todosPermisos);

        // AUXILIAR: crear, editar, ver (NO eliminar, NO configuración, NO admin)
        $auxiliarPermisos = array_merge(
            $permisosDashboard,
            $permisosInventario,
            $permisosCompras,
            $permisosVentas,
            $permisosTransformaciones,
            $permisosFormulaTransformacion,
            $permisosAjusteInventario,
            $permisosConteoFisico,
            $permisosReportes
        );
        // Agregar permisos de ver configuración (sin editar/eliminar)
        $auxiliarPermisos[] = 'config.ver';
        $auxiliarPermisos[] = 'empresa.ver';
        $auxiliarPermisos[] = 'bodega.ver';
        $auxiliarPermisos[] = 'categoria.ver';
        $auxiliarPermisos[] = 'marca.ver';
        $auxiliarPermisos[] = 'impuesto.ver';
        $auxiliarPermisos[] = 'forma_pago.ver';
        $auxiliarPermisos[] = 'numeracion.ver';
        $auxiliarPermisos[] = 'unidad_medida.ver';
        $auxiliarPermisos[] = 'banco.ver';

        $auxiliarRole = Role::findByName('auxiliar');
        $auxiliarRole->givePermissionTo(array_unique($auxiliarPermisos));

        // CONTADOR: solo ver, exportar, imprimir (NO crear, NO editar, NO eliminar)
        $contadorPermisos = array_merge(
            $permisosDashboard,
            $permisosInventario,
            $permisosCompras,
            $permisosVentas,
            $permisosTransformaciones,
            $permisosFormulaTransformacion,
            $permisosReportes
        );
        // Solo ver en config y admin
        $contadorPermisos[] = 'config.ver';
        $contadorPermisos[] = 'auditoria.ver';
        $contadorPermisos[] = 'ajuste_inventario.ver';
        $contadorPermisos[] = 'conteo_fisico.ver';

        $contadorRole = Role::findByName('contador');
        $contadorRole->givePermissionTo(array_unique($contadorPermisos));

        // VENDEDOR: acceso limitado a ventas
        $vendedorPermisos = [
            'dashboard.ver',
            'producto.ver',
            'cotizacion.ver',
            'cotizacion.crear',
            'cotizacion.editar',
            'remision.ver',
            'remision.crear',
            'remision.editar',
            'venta.ver',
            'venta.crear',
            'venta.editar',
        ];

        $vendedorRole = Role::findByName('vendedor');
        $vendedorRole->givePermissionTo($vendedorPermisos);

        // Solo mostrar mensaje si el seeder se ejecuta desde Artisan (no en tests)
        if ($this->command) {
            $this->command->info('Roles y permisos granulares creados correctamente.');
        }
    }
}
