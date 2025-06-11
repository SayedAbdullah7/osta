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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            // Polymorphic fields: works for both User and Provider
            $table->unsignedBigInteger('notifiable_id');
            $table->string('notifiable_type');

            $table->string('title');
            $table->text('message');
            $table->string('type'); // e.g., chat, order, system
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // Index to speed up queries by notifiable
            $table->index(['notifiable_id', 'notifiable_type', 'is_read']);
        });
//        Schema::create('notifications', function (Blueprint $table) {
//            $table->id();
//            $table->string('title');
//            $table->string('message');
//            $table->enum('type', ['chat', 'order', 'system'])->default('system'); // Restrict values and provide a default
//
//            $table->boolean('is_read')->default(false);
//            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Make foreign table explicit
//            $table->foreignId('provider_id')->nullable()->constrained('providers')->onDelete('cascade'); // Make foreign table explicit
//
//            $table->timestamps();
//
//            // Add indexes for foreign keys for faster lookups
//            $table->index('user_id');
//            $table->index('provider_id');
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
