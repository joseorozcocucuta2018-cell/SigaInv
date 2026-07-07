<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: clientes
 *
 * Correcciones aplicadas vs coredata original:
 *   - ciudadesId      → ciudad_id       (snake_case)
 *   - departamentosId → departamento_id (snake_case)
 *   - usuarioId       → usuario_id      (snake_case)
 *   - usuario_id: onDelete cambiado de 'restrict' → 'set null' + nullable()
 *     Razón: si se elimina un usuario del sistema, los clientes que registró
 *     no deben bloquearlo ni perderse. El campo queda en null trazablemente.
 *   - Orden de columnas corregido: FKs agrupadas lógicamente, usuario_id al final
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('documento', 20)->unique();
            $table->enum('tipo_documento', ['CC', 'NIT', 'CE', 'PP'])->default('CC');
            $table->string('telefono', 30);
            $table->string('email', 100)->unique();
            $table->string('direccion1', 100);
            $table->string('direccion2', 100)->nullable();
            $table->foreignId('departamento_id')
                ->constrained('departamentos')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreignId('ciudad_id')
                ->constrained('ciudades')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->decimal('saldo', 15, 2)->default(0.00);           // Solo lectura — calculado por movimientos
            $table->string('pais', 100)->default('Colombia');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->decimal('limite_credito', 15, 2)->default(0.00);
            $table->integer('dias_credito')->default(0);
            $table->integer('dias_pago')->default(0);
            $table->string('contacto_principal', 100)->nullable();
            $table->string('sitio_web', 255)->nullable();
            $table->decimal('porcentaje_descuento', 5, 2)->default(0)
                ->comment('Descuento comercial del cliente (integradores/subdistribuidores)');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->enum('portal_acceso', ['sin_acceso', 'pendiente', 'activo'])
                ->default('sin_acceso')
                ->comment('Nivel de acceso al portal de clientes');
            $table->foreignId('user_id_portal')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Usuario asociado al portal de este cliente');
            $table->timestamps();

            // --- Auth (portal /clientes) ---
            $table->string('password', 255)->nullable()
                ->comment('Hash bcrypt/argon2 — null = sin acceso al portal');
            $table->string('remember_token', 100)->nullable()
                ->comment('Token de sesión persistente (Auth estándar)');
            $table->timestamp('email_verified_at')->nullable()
                ->comment('Verificación de email (Auth estándar)');
            $table->timestamp('password_changed_at')->nullable()
                ->comment('null = contraseña temporal; forzar cambio en primer login');
            $table->timestamp('portal_last_login_at')->nullable()
                ->comment('Auditoría — último acceso al portal /clientes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
