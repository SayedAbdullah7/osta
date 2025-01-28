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
        Schema::create('user_actions', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('action')->nullable();  // The action type (e.g., 'rating', 'skip', 'purchase')
            $table->unsignedBigInteger('model_id')->nullable();  // ID of the related model (e.g., order, product, etc.)
            $table->string('value')->nullable();  // Action-specific value (e.g., rating value, reason for skip)
            $table->unsignedBigInteger('user_id')->index()->nullable();  // The user who performed the action
            $table->unsignedBigInteger('provider_id')->index()->nullable();  // The provider associated with the action (can be null if not needed)
            $table->timestamps();

            // Foreign key constraints (if applicable)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');  // Adjust table name if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_actions');
    }
};
