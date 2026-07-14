<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->boolean('es_premium')->default(false)->after('imagen');
            $table->decimal('precio', 8, 2)->nullable()->after('es_premium');
        });
    }

    public function down(): void
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->dropColumn(['es_premium', 'precio']);
        });
    }
};
