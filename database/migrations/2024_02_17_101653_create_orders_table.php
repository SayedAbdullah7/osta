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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
            $table->enum('warranty',['lev1','lev2','lev3'])->nullable();
            $table->enum('status',['pending','accepted','coming','almost done','done'])->default('pending');
            $table->text('desc');
            $table->mediumInteger('price')->unsigned()->nullable();
            $table->foreignIdFor(\App\Models\User::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(\App\Models\Service::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(\App\Models\Provider::class)->nullable()->constrained()->restrictOnDelete();
            $table->foreignIdFor(\App\Models\Location::class)->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
