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
        Schema::create('levels', function (Blueprint $table) {
//            $table->id();
//            $table->string('name');
//            $table->integer('level');
//            $table->unsignedInteger('percentage');
//            $table->integer('orders_required');
//            $table->integer('is_paid')->default(0);
//            $table->foreignId('next_level_id')->nullable()->constrained('levels')->nullOnDelete();
//            $table->timestamps();
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('level')->unique();
            $table->string('badge_image')->nullable();
            $table->json('requirements')->nullable();
            $table->json('benefits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('grace_period_months')->default(1);
            $table->boolean('grace_period_applies_to_orders_only')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
