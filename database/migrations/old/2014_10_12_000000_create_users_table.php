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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
//            $table->string('phone',15)->unique();
            $table->string('phone',15);
            $table->boolean('is_phone_verified')->default(0);

//            $table->string('email')->unique()->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
//            $table->enum('account',['evidence','specialization','bank account']);
            $table->boolean('gender');
            $table->date('date_of_birth')->nullable();
//            $table->string('password');
            $table->rememberToken();
            $table->foreignIdFor(\App\Models\Country::class)->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
