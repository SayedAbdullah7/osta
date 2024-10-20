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
        Schema::create('provider_review_statistics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('provider_id')->constrained()->onDelete('cascade');
                $table->integer('total_reviews')->default(0);
                $table->float('average_rating', 3, 2)->default(0.00);
                $table->integer('completed_orders')->unsigned()->default(0);
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_review_statistics');
    }
};
