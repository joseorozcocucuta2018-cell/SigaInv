<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            if (Schema::hasColumn('empresa', 'logo_email_url')) {
                $table->dropColumn('logo_email_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            if (! Schema::hasColumn('empresa', 'logo_email_url')) {
                $table->string('logo_email_url', 500)->nullable();
            }
        });
    }
};
