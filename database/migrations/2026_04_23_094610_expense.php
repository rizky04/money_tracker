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
        // Migration for Expenses
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->date('date');
    $table->string('merchant');
    $table->integer('total_amount');
    $table->timestamps();
});

// Migration for Expense Items
Schema::create('expense_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('expense_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->integer('qty');
    $table->integer('price');
    $table->integer('subtotal');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
