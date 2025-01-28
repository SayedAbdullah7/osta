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
        Schema::create('order_sub_service', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('sub_service_id');
            $table->unsignedBigInteger('space_id')->nullable();
            $table->integer('quantity')->unsigned()->default(1);
            $table->string('space_name')->nullable();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('sub_service_id')->references('id')->on('sub_services')->onDelete('cascade');
            $table->foreign('space_id')->references('id')->on('spaces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_sub_service');
    }
};
