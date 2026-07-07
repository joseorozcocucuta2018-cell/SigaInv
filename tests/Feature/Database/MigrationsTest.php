<?php

/*
|--------------------------------------------------------------------------
| MigrationsTest.php — Tests de estructura de la base de datos
| v2 — columnas corregidas: departamento_id, ciudad_id, usuario_id
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Schema;

describe('Migraciones — tablas Coredata', function () {

    it('tabla users existe con campos personalizados', function () {
        expect(Schema::hasTable('users'))->toBeTrue();
        expect(Schema::hasColumns('users', [
            'id', 'name', 'email', 'password',
            'celular', 'fecha_nacimiento',
            'cargo', 'avatar', 'password_changed_at',
        ]))->toBeTrue();
    });

    it('tabla departamentos existe', function () {
        expect(Schema::hasTable('departamentos'))->toBeTrue();
        expect(Schema::hasColumns('departamentos', ['id', 'nombre']))->toBeTrue();
    });

    it('tabla ciudades existe con FK a departamentos', function () {
        expect(Schema::hasTable('ciudades'))->toBeTrue();
        expect(Schema::hasColumns('ciudades', ['id', 'nombre', 'departamento_id']))->toBeTrue();
    });

    it('tabla clientes existe con todos los campos', function () {
        expect(Schema::hasTable('clientes'))->toBeTrue();
        expect(Schema::hasColumns('clientes', [
            'id', 'nombre', 'documento', 'tipo_documento',
            'telefono', 'email', 'direccion1',
            'ciudad_id', 'departamento_id',
            'saldo', 'estado', 'usuario_id',
            'portal_acceso', 'user_id_portal',
            'password', 'remember_token', 'email_verified_at',
            'password_changed_at', 'portal_last_login_at',
        ]))->toBeTrue();
    });

    it('tabla proveedores existe con todos los campos', function () {
        expect(Schema::hasTable('proveedores'))->toBeTrue();
        expect(Schema::hasColumns('proveedores', [
            'id', 'nombre', 'documento', 'tipo_documento',
            'telefono', 'email', 'direccion1',
            'ciudad_id', 'departamento_id',
            'saldo', 'estado', 'usuario_id',
        ]))->toBeTrue();
    });
});

describe('Migraciones — tablas Configuración', function () {

    it('tabla empresa existe', function () {
        expect(Schema::hasTable('empresa'))->toBeTrue();
    });

    it('tabla unidades_medida existe', function () {
        expect(Schema::hasTable('unidades_medida'))->toBeTrue();
    });

    it('tabla categorias existe', function () {
        expect(Schema::hasTable('categorias'))->toBeTrue();
    });

    it('tabla marcas existe', function () {
        expect(Schema::hasTable('marcas'))->toBeTrue();
    });

    it('tabla impuestos existe', function () {
        expect(Schema::hasTable('impuestos'))->toBeTrue();
    });

    it('tabla formas_pago existe', function () {
        expect(Schema::hasTable('formas_pago'))->toBeTrue();
    });

    it('tabla numeraciones existe', function () {
        expect(Schema::hasTable('numeraciones'))->toBeTrue();
    });

    it('tabla bodegas existe', function () {
        expect(Schema::hasTable('bodegas'))->toBeTrue();
    });
});

describe('Migraciones — tablas Inventario', function () {

    it('tabla productos existe', function () {
        expect(Schema::hasTable('productos'))->toBeTrue();
    });

    it('tabla stock_bodegas existe', function () {
        expect(Schema::hasTable('stock_bodegas'))->toBeTrue();
    });

    it('tabla movimientos_inventario existe', function () {
        expect(Schema::hasTable('movimientos_inventario'))->toBeTrue();
    });
});

describe('Migraciones — tablas Ventas y Compras', function () {

    it('tabla cotizaciones existe', function () {
        expect(Schema::hasTable('cotizaciones'))->toBeTrue();
    });

    it('tabla ventas existe', function () {
        expect(Schema::hasTable('ventas'))->toBeTrue();
    });

    it('tabla detalle_ventas existe', function () {
        expect(Schema::hasTable('detalle_ventas'))->toBeTrue();
    });

    it('tabla compras existe', function () {
        expect(Schema::hasTable('compras'))->toBeTrue();
    });

    it('tabla detalle_compras existe', function () {
        expect(Schema::hasTable('detalle_compras'))->toBeTrue();
    });

    it('tabla pago_clientes existe con columnas principales', function () {
        expect(Schema::hasTable('pago_clientes'))->toBeTrue();
        expect(Schema::hasColumn('pago_clientes', 'cliente_id'))->toBeTrue();
        expect(Schema::hasColumn('pago_clientes', 'monto'))->toBeTrue();
    });

    it('tabla pago_proveedores existe con columnas principales', function () {
        expect(Schema::hasTable('pago_proveedores'))->toBeTrue();
        expect(Schema::hasColumn('pago_proveedores', 'proveedor_id'))->toBeTrue();
        expect(Schema::hasColumn('pago_proveedores', 'monto'))->toBeTrue();
    });

    it('tablas detalle_pago_clientes y detalle_pago_proveedores existen (S23)', function () {
        expect(Schema::hasTable('detalle_pago_clientes'))->toBeTrue();
        expect(Schema::hasTable('detalle_pago_proveedores'))->toBeTrue();
    });
});

describe('Migraciones — tablas Spatie Permissions', function () {

    it('tabla roles existe', function () {
        expect(Schema::hasTable('roles'))->toBeTrue();
    });

    it('tabla permissions existe', function () {
        expect(Schema::hasTable('permissions'))->toBeTrue();
    });

    it('tabla model_has_roles existe', function () {
        expect(Schema::hasTable('model_has_roles'))->toBeTrue();
    });
});

describe('Migraciones — tablas Devoluciones', function () {

    it('tabla devoluciones existe con todos los campos', function () {
        expect(Schema::hasTable('devoluciones'))->toBeTrue();
        expect(Schema::hasColumns('devoluciones', [
            'id', 'numero', 'tipo_documento', 'documento_id',
            'cliente_id', 'estado', 'confirmada_en',
            'motivo', 'observaciones',
            'subtotal', 'descuento', 'impuestos', 'total',
            'usuario_id', 'deleted_at',
        ]))->toBeTrue();
    });

    it('tabla detalles_devoluciones existe con todos los campos', function () {
        expect(Schema::hasTable('detalles_devoluciones'))->toBeTrue();
        expect(Schema::hasColumns('detalles_devoluciones', [
            'id', 'devolucion_id', 'producto_id',
            'cantidad', 'precio_unitario', 'subtotal', 'defectuoso',
        ]))->toBeTrue();
    });

    it('tabla movimientos_saldo_cliente existe con todos los campos', function () {
        expect(Schema::hasTable('movimientos_saldo_cliente'))->toBeTrue();
        expect(Schema::hasColumns('movimientos_saldo_cliente', [
            'id', 'cliente_id', 'tipo', 'referencia',
            'monto', 'saldo_anterior', 'saldo_nuevo',
            'descripcion', 'usuario_id',
        ]))->toBeTrue();
    });

    it('tabla devoluciones soporta softDeletes (columna deleted_at)', function () {
        expect(Schema::hasColumn('devoluciones', 'deleted_at'))->toBeTrue();
    });

    it('tabla detalles_devoluciones tiene FK a devoluciones', function () {
        expect(Schema::hasColumn('detalles_devoluciones', 'devolucion_id'))->toBeTrue();
    });
});
