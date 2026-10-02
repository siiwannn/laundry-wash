<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('vehicle_type')->nullable(); // Motor, Mobil
            $table->string('vehicle_plate')->nullable(); // B 1234 XYZ
            $table->enum('status', ['available', 'busy', 'offline'])->default('offline');
            $table->timestamps();

            $table->unique('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_profiles');
    }
};
