<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_until')->nullable()->after('role');
            $table->boolean('suspended_indefinitely')->default(false)->after('suspended_until');
            $table->string('suspension_reason')->nullable()->after('suspended_indefinitely');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'suspended_until',
                'suspended_indefinitely',
                'suspension_reason',
            ]);
        });
    }
};
