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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
//            $table->foreignId('user_id')->constrained()->onDelete('cascade');
//            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
//            $table->foreignId('provider_id')->nullable()->constrained()->onDelete('cascade');
//            $table->text('comment')->nullable();
//            $table->unsignedTinyInteger('rating');
//            $table->timestamps();
//            $table->id();

            // Polymorphic relation for the reviewer (user or provider)
            $table->morphs('reviewable'); // This will create reviewable_type and reviewable_id columns

            // The target of the review (the other party in the review)
            $table->morphs('reviewed'); // This will create reviewed_type and reviewed_id columns

            // Common review fields
            $table->text('comment')->nullable();
            $table->unsignedTinyInteger('rating'); // from 1 to 5
            $table->boolean('is_approved')->default(false);
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
