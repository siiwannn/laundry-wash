<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assignment_id')->constrained('courier_assignments')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['assignment_id', 'recorded_at']);
            $table->index(['courier_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_locations');
    }
};
