<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Previously disabled catalog rows are retired from the UI but remain
        // in the database so existing order-item foreign keys are preserved.
        DB::table('services')->where('is_active', false)->update(['deleted_at' => now()]);
    }

    public function down(): void
    {
        DB::table('services')->whereNotNull('deleted_at')->update(['is_active' => false]);

        Schema::table('services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
