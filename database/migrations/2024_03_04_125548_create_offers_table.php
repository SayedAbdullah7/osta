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
            $table->dateTime('arrival_from');
            $table->dateTime('arrival_to');
            $table->string('arrival_time')->nullable();
            $table->mediumInteger('price')->unsigned()->nullable();
            $table->enum('status', \App\Enums\OfferStatusEnum::values())->default(\App\Enums\OfferStatusEnum::DefaultValue());
            $table->boolean('is_second')->default(false);
            $table->string('latitude',15)->nullable();
            $table->string('longitude',15)->nullable();
            $table->float('distance',6,2)->nullable();
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
