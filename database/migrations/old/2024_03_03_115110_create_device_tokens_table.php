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
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_set_notification');
            $table->string('token');
            $table->boolean('is_ios')->default(0);
//            $table->morphs('user');
            // Two separate columns for user_id and provider_id
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // For user
            $table->foreignId('provider_id')->nullable()->constrained()->onDelete('cascade'); // For provider

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
