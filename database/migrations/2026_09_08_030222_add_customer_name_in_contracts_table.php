
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contracts', 'account_number')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->string('account_number')->before('start_date');
            });
        }
    }

    public function down(): void
    {
        // Keep the existing column and its data.
    }
};
