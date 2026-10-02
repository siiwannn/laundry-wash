<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pickup_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
            $table->foreignId('delivery_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
            $table->enum('service_type', ['pickup_and_delivery', 'self_drop_off'])->default('pickup_and_delivery');
            $table->string('status')->default('pending')->index();
            $table->string('payment_status')->default('pending')->index();
            $table->decimal('estimated_weight', 8, 2)->nullable();
            $table->decimal('actual_weight', 8, 2)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('additional_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
