<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasIndex('compras', 'compras_usuario_id_receta_id_unique', 'unique')) {
            return;
        }

        Schema::table('compras', function (Blueprint $table) {
            if (Schema::hasForeignKey('compras', ['usuario_id'])) {
                $table->dropForeign(['usuario_id']);
            }

            if (Schema::hasForeignKey('compras', ['receta_id'])) {
                $table->dropForeign(['receta_id']);
            }
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropUnique('compras_usuario_id_receta_id_unique');
        });

        Schema::table('compras', function (Blueprint $table) {
            if (! Schema::hasIndex('compras', ['usuario_id'])) {
                $table->index('usuario_id');
            }

            if (! Schema::hasIndex('compras', ['receta_id'])) {
                $table->index('receta_id');
            }

            if (! Schema::hasForeignKey('compras', ['usuario_id'])) {
                $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            }

            if (! Schema::hasForeignKey('compras', ['receta_id'])) {
                $table->foreign('receta_id')->references('id')->on('recetas')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('compras', 'compras_usuario_id_receta_id_unique', 'unique')) {
            return;
        }

        Schema::table('compras', function (Blueprint $table) {
            if (Schema::hasForeignKey('compras', ['usuario_id'])) {
                $table->dropForeign(['usuario_id']);
            }

            if (Schema::hasForeignKey('compras', ['receta_id'])) {
                $table->dropForeign(['receta_id']);
            }
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->unique(['usuario_id', 'receta_id']);
        });

        Schema::table('compras', function (Blueprint $table) {
            if (! Schema::hasForeignKey('compras', ['usuario_id'])) {
                $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            }

            if (! Schema::hasForeignKey('compras', ['receta_id'])) {
                $table->foreign('receta_id')->references('id')->on('recetas')->cascadeOnDelete();
            }
        });
    }
};
