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
        Schema::table('contracts', function (Blueprint $table) {
            $table->dateTime('suspended_at')->nullable()->after('status');
            $table->dateTime('resumed_at')->nullable()->after('suspended_at');
            $table->unsignedInteger('suspended_days')->default(0)->after('resumed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'suspended_at',
                'resumed_at',
                'suspended_days',
            ]);
        });
    }
};
