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
        Schema::create('account_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->morphs('deletable');  // This will create both `deletable_id` and `deletable_type` column
            $table->foreignId('user_id')->constrained()->onDelete('cascade');  // link to user or provider
            $table->enum('user_type', ['user', 'provider']);  // Type of user requesting deletion
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();  // Will be populated once deleted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_deletion_requests');
    }
};
