<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
//            $table->string('first_name',15);
//            $table->string('last_name',15);
            $table->string('phone',15)->unique();
            $table->boolean('is_phone_verified')->default(0);
            $table->boolean('is_approved')->default(0);
//            $table->string('email')->unique()->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('gender');
            $table->boolean('is_new')->default(true);
//            $table->string('password');
            $table->foreignIdFor(\App\Models\Country::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(\App\Models\City::class)->constrained()->restrictOnDelete();
            //            $table->enum('account',['evidence','specialization','bank account']);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
