<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla: empresa
 *
 * Almacena los datos legales y fiscales del negocio.
 * Contiene UN SOLO REGISTRO (id = 1) — nunca se crean más.
 * Se gestiona desde el panel como formulario de configuración,
 * no como un CRUD convencional.
 *
 * Contexto Colombia:
 *   - NIT con dígito de verificación (ej: 900123456-7)
 *   - Régimen tributario: simplificado / común / gran_contribuyente
 *   - Responsable de IVA: determina si se cobra IVA en facturas
 *   - Resolución DIAN: autoriza el rango de numeración de facturas electrónicas
 *   - Actividad CIIU: código de actividad económica
 *
 * Campos de documento:
 *   - logo: ruta al archivo de imagen (se imprime en facturas)
 *   - pie_pagina: texto libre que aparece al pie de cada documento
 *   - email_documentos: correo desde el que se envían facturas/remisiones
 *
 * timestamp: usa timestamp convencional created_at/updated_at.
 * No tiene softDeletes — este registro nunca se elimina.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id();

            // --- Identificación legal ---
            $table->string('razon_social', 150);
            $table->string('nombre_comercial', 150)->nullable();
            $table->string('nit', 20);                          // Sin dígito verificación
            $table->unsignedTinyInteger('digito_verificacion'); // 0-9
            $table->enum('tipo_persona', ['natural', 'juridica'])->default('juridica');

            // --- Tributario ---
            $table->enum('regimen_tributario', [
                'simplificado',
                'comun',
                'gran_contribuyente',
            ])->default('comun');
            $table->boolean('responsable_iva')->default(true);
            $table->boolean('usa_seriales')->default(false);
            $table->boolean('una_sola_bodega')->default(false);
            $table->string('actividad_ciiu', 10)->nullable();   // Ej: 4711

            // --- Ubicación fiscal ---
            $table->string('direccion', 150);
            $table->foreignId('departamento_id')
                ->nullable()
                ->constrained('departamentos')
                ->onDelete('set null');
            $table->foreignId('ciudad_id')
                ->nullable()
                ->constrained('ciudades')
                ->onDelete('set null');
            $table->string('pais', 60)->default('Colombia');

            // --- Contacto ---
            $table->string('telefono', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('email_documentos', 100)->nullable(); // Para envío de facturas
            $table->string('sitio_web', 150)->nullable();

            // --- Resolución DIAN ---
            $table->string('resolucion_dian', 30)->nullable();       // Número de resolución
            $table->date('resolucion_fecha_expedicion')->nullable();
            $table->date('resolucion_fecha_vencimiento')->nullable();
            $table->unsignedInteger('resolucion_desde')->nullable(); // Rango autorizado inicio
            $table->unsignedInteger('resolucion_hasta')->nullable(); // Rango autorizado fin

            // --- Documentos ---
            $table->string('logo', 255)->nullable();            // Logo panel Filament
            $table->string('logo_pos', 255)->nullable();        // Logo tickets POS (80mm)
            $table->string('logo_impresion', 255)->nullable();  // Logo PDFs (carta/media carta)
            $table->text('pie_pagina')->nullable();             // Pie de página en documentos
            $table->text('notas_factura')->nullable();          // Notas legales en factura

            // --- Márgenes para transformaciones ---
            $table->decimal('margen_ganancia_default', 5, 2)
                ->default(30.00)
                ->comment('Margen de ganancia por defecto para precios de venta');
            $table->decimal('margen_ganancia_minimo', 5, 2)
                ->default(10.00)
                ->comment('Margen de ganancia mínimo permitido');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
