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
        Schema::create('provider_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->unique();  // Ensure one location per provider
            $table->decimal('latitude', 10, 7);  // 10 digits total, 7 after decimal
            $table->decimal('longitude', 10, 7); // 10 digits total, 7 after decimal
            $table->timestamp('tracked_at')->useCurrent();  // Use current timestamp by default
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade'); // Foreign key constraint
            $table->timestamps(); // Automatically handles created_at and updated_atx`

            $table->index(['latitude', 'longitude']); // For spatial queries or searching based on location
            $table->index('tracked_at'); // If querying by timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_locations');
    }
};
