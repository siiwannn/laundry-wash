<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway_order_id', 100)->nullable()->unique()->after('order_id');
            $table->string('gateway_transaction_id', 100)->nullable()->index()->after('gateway_order_id');
            $table->text('snap_token')->nullable()->after('gateway_transaction_id');
            $table->string('gateway_status', 50)->nullable()->index()->after('snap_token');
            $table->timestamp('expires_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['gateway_order_id']);
            $table->dropIndex(['gateway_transaction_id']);
            $table->dropIndex(['gateway_status']);
            $table->dropColumn([
                'gateway_order_id',
                'gateway_transaction_id',
                'snap_token',
                'gateway_status',
                'expires_at',
            ]);
        });
    }
};
