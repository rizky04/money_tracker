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
        Schema::create('fuel_entries', function (Blueprint $table) {
            $table->id();
    $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->date('date');
    $table->string('fuel_type'); // Pertamax, Pertalite, dll
    $table->decimal('liters', 10, 2);
    $table->decimal('price_per_liter', 15, 2);
    $table->decimal('total_price', 15, 2);
    $table->integer('odometer');
    $table->string('location_name')->nullable(); // Nama SPBU
    $table->string('receipt_image')->nullable(); // Path foto struk
    $table->boolean('is_ai_generated')->default(false);
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_entries');
    }
};
