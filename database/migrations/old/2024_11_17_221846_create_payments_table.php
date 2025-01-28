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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
//            $table->foreignId('invoice_id')->constrained()->onDelete('cascade'); // Foreign key to invoices table
            $table->decimal('amount', 10, 2); // Store amount with two decimal precision
            $table->string('payment_method'); // Payment method (e.g., wallet, credit card, etc.)
            $table->json('meta')->nullable(); // Meta data to store additional information
            $table->boolean('is_reviewed')->default(true);
            $table->foreignId('creator_id')->constrained()->onDelete('cascade'); // Foreign key to
            $table->foreignId('reviewer_id')->constrained()->onDelete('cascade'); // Foreign key to

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
