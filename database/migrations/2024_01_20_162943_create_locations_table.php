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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('street');
            $table->string('apartment_number');
            $table->string('floor_number');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('desc');
            $table->boolean('is_default')->default(0);
//            $table->foreignIdFor(\App\Models\Country::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(\App\Models\City::class)->constrained()->restrictOnDelete();
//            $table->foreignIdFor(\App\Models\Area::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(\App\Models\User::class)->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
