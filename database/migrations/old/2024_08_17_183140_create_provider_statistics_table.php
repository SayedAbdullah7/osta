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
        Schema::create('provider_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->date('month');
            $table->integer('orders_done_count')->default(0);
            $table->integer('level')->default(1);
            $table->integer('orders_remaining_for_next_level')->default(0);
            $table->foreignId('level_id');
                //->constrained('levels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_statistics');
    }
};
