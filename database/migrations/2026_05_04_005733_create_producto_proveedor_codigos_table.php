<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('producto_proveedor_codigos', function (Blueprint $schema) {
            $schema->id();
            $schema->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            $schema->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $schema->string('codigo_proveedor', 100);
            $schema->string('descripcion_proveedor', 255)->nullable();
            $schema->timestamps();

            $schema->unique(['proveedor_id', 'codigo_proveedor'], 'prod_prov_cod_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_proveedor_codigos');
    }
};
