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
        Schema::create('conversation_members', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Conversation::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->morphs('user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_members');
    }
};
