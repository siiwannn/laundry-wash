<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('laundry_price_per_kg', 12, 2)->default(8000);
            $table->decimal('pickup_fee', 12, 2)->default(5000);
            $table->decimal('delivery_fee', 12, 2)->default(5000);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('pickup_method')->default('pickup')->after('delivery_address_id');
            $table->string('delivery_method')->default('delivery')->after('pickup_method');
            $table->date('pickup_date')->nullable()->after('delivery_method');
            $table->time('pickup_time')->nullable()->after('pickup_date');
            $table->decimal('price_per_kg', 12, 2)->default(8000)->after('actual_weight');
            $table->decimal('pickup_fee', 12, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pickup_method', 'delivery_method', 'pickup_date', 'pickup_time', 'price_per_kg', 'pickup_fee']);
        });
        Schema::dropIfExists('settings');
    }
};
