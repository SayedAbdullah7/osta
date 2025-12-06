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
        Schema::create('provider_levels', function (Blueprint $table) {
//            $table->id();
//            $table->timestamps();
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->date('achieved_at');
            $table->date('valid_until')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->unique(['provider_id', 'level_id', 'achieved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_levels');
    }
};
