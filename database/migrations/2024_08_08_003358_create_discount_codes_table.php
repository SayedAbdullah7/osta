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
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Unique discount code
            $table->decimal('discount_amount', 8, 2)->default(0.00); // Fixed amount discount
            $table->decimal('discount_percentage', 5, 2)->nullable(); // Percentage-based discount
            $table->boolean('is_active')->default(true); // Indicates if the code is active
            $table->timestamp('expires_at')->nullable(); // Expiration date of the code
            $table->timestamp('used_at')->nullable(); // When the code was used
            $table->unsignedBigInteger('used_by')->nullable(); // User who used the discount code
            $table->foreign('used_by')->references('id')->on('users')->onDelete('set null'); // Foreign key to users table
            $table->timestamps();
//            $table->id();
//            $table->string('code')->unique();
//            $table->enum('type', ['fixed', 'percentage']); // Discount type: fixed amount or percentage
//            $table->decimal('discount_value', 10, 2); // The discount value (either a fixed amount or percentage)
//            $table->timestamp('valid_from')->nullable(); // When the discount code starts being valid
//            $table->timestamp('valid_to')->nullable(); // When the discount code stops being valid
//            $table->boolean('is_active')->default(true); // Whether the discount code is currently active
//            $table->timestamp('used_at')->nullable(); // When the discount code was used
//            $table->unsignedBigInteger('used_by')->nullable(); // The ID of the user who used the discount code
//            $table->foreign('used_by')->references('id')->on('users')->onDelete('set null'); // Foreign key constraint
//            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
