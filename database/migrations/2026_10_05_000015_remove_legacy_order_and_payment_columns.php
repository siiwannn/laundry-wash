<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('method', ['cash', 'transfer', 'qris', 'virtual_account'])
                ->nullable()
                ->default(null)
                ->change();
        });

        DB::table('payments')
            ->whereIn('method', ['cash', 'transfer'])
            ->update(['method' => 'virtual_account']);

        DB::table('payments')
            ->where('status', 'refunded')
            ->update(['status' => 'failed']);

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('method', ['qris', 'virtual_account'])->nullable()->default(null)->change();
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending')->change();
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['proof_file', 'reference']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'pickup_method', 'delivery_method']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('service_type', ['pickup_and_delivery', 'self_drop_off'])
                ->default('pickup_and_delivery');
            $table->string('pickup_method')->default('pickup');
            $table->string('delivery_method')->default('delivery');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('method', ['cash', 'transfer', 'qris'])->nullable()->default(null)->change();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->change();
            $table->string('proof_file')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
