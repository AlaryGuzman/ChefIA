<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->string('metodo_pago')->default('Tarjeta simulada')->after('precio_pagado');
            $table->string('tarjeta_ultimos4', 4)->nullable()->after('metodo_pago');
            $table->string('referencia_pago')->nullable()->after('tarjeta_ultimos4');
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'tarjeta_ultimos4', 'referencia_pago']);
        });
    }
};
