<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courier_profiles', function (Blueprint $table) {
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
        });
        Schema::table('courier_locations', function (Blueprint $table) {
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('heading', 8, 2)->nullable();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->string('old_status')->nullable()->after('order_id');
            $table->string('new_status')->nullable()->after('old_status');
        });
    }

    public function down(): void
    {
        Schema::table('courier_profiles', fn (Blueprint $table) => $table->dropColumn(['current_latitude', 'current_longitude']));
        Schema::table('courier_locations', fn (Blueprint $table) => $table->dropColumn(['speed', 'heading']));
        Schema::table('payments', fn (Blueprint $table) => $table->dropConstrainedForeignId('verified_by'));
        Schema::table('order_status_histories', fn (Blueprint $table) => $table->dropColumn(['old_status', 'new_status']));
    }
};
