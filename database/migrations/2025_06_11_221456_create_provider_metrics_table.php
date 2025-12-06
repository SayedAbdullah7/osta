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
        Schema::create('provider_metrics', function (Blueprint $table) {
//            $table->id();
//            $table->timestamps();
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->date('month');
            $table->integer('completed_orders')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('repeat_customers')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('cancellation_rate', 5, 2)->default(0);
            $table->decimal('response_time_avg')->default(0);
            $table->timestamps();

            $table->unique(['provider_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_metrics');
    }
};
