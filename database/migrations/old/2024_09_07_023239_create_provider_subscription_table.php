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
        Schema::create('provider_subscription', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('subscription_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            // Foreign keys
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_subscription');
    }
};
