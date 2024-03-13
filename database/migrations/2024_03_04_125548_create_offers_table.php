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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->mediumInteger('price')->unsigned();
            $table->enum('status', array_column(\App\Enums\OfferStatusEnum::cases(), 'value'))->default(\App\Enums\OfferStatusEnum::PENDING);
            $table->boolean('is_second')->default(false);
            $table->foreignIdFor(\App\Models\Provider::class)->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(\App\Models\Order::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
