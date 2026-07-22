<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->string('estado')->default('pagado')->after('referencia_pago');
            $table->string('referencia_efectivo')->nullable()->after('estado');
            $table->string('referencia_reembolso')->nullable()->after('referencia_efectivo');
            $table->string('motivo_cancelacion')->nullable()->after('referencia_reembolso');
            $table->timestamp('pagado_at')->nullable()->after('motivo_cancelacion');
            $table->timestamp('enviado_at')->nullable()->after('pagado_at');
            $table->timestamp('entregado_at')->nullable()->after('enviado_at');
            $table->timestamp('cancelado_at')->nullable()->after('entregado_at');
            $table->timestamp('eliminado_at')->nullable()->after('cancelado_at');
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn([
                'estado',
                'referencia_efectivo',
                'referencia_reembolso',
                'motivo_cancelacion',
                'pagado_at',
                'enviado_at',
                'entregado_at',
                'cancelado_at',
                'eliminado_at',
            ]);
        });
    }
};
