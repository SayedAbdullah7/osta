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
        Schema::create('space_sub_service', function (Blueprint $table) {
            $table->unsignedBigInteger('space_id');
            $table->unsignedBigInteger('sub_service_id');
            $table->decimal('max_price', 10, 2);

            $table->foreign('space_id')->references('id')->on('spaces')->onDelete('cascade');
            $table->foreign('sub_service_id')->references('id')->on('sub_services')->onDelete('cascade');

            $table->primary(['space_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_service');
    }
};
